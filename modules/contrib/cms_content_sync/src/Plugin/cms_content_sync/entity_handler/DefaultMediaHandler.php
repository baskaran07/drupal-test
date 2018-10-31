<?php

namespace Drupal\cms_content_sync\Plugin\cms_content_sync\entity_handler;

use Drupal\cms_content_sync\ImportIntent;
use Drupal\cms_content_sync\Plugin\EntityHandlerBase;
use Drupal\cms_content_sync\Exception\SyncException;
use Drupal\cms_content_sync\SyncIntent;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TranslatableInterface;

/**
 * Class DefaultMediaHandler, providing a minimalistic implementation for the
 * media entity type.
 *
 * @EntityHandler(
 *   id = "cms_content_sync_media_entity_handler",
 *   label = @Translation("Default Media"),
 *   weight = 90
 * )
 *
 * @package Drupal\cms_content_sync\Plugin\cms_content_sync\entity_handler
 */
class DefaultMediaHandler extends EntityHandlerBase {

  /**
   * @inheritdoc
   */
  public static function supports($entity_type, $bundle) {
    return $entity_type == 'media';
  }

  /**
   * @inheritdoc
   */
  public function getForbiddenFields() {
    return array_merge(
      parent::getForbiddenFields(),
      [
        // Must be recreated automatically on remote site.
        'thumbnail',
      ]
    );
  }

  /**
   * @inheritdoc
   */
  public function getAllowedPreviewOptions() {
    return [
      'table' => 'Table',
      'preview_mode' => 'Preview mode',
    ];
  }


  protected function setEntityValues(ImportIntent $intent, FieldableEntityInterface $entity = NULL) {
    if (!$entity) {
      $entity = $intent->getEntity();
    }

    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $field_definitions = $entityFieldManager->getFieldDefinitions($type, $bundle);

    $entity_type = \Drupal::entityTypeManager()->getDefinition($intent->getEntityType());
    $label       = $entity_type->getKey('label');
    if ($label && !$intent->shouldMergeChanges()) {
      $entity->set($label, $intent->getField('title'));
    }

    $static_fields = $this->getStaticFields();

    $is_translation = boolval($intent->getActiveLanguage());

    foreach ($field_definitions as $key => $field) {
      $handler = $this->flow->getFieldHandler($type, $bundle, $key);

      if (!$handler) {
        continue;
      }

      // This field cannot be updated.
      if (in_array($key, $static_fields) && $intent->getAction() != SyncIntent::ACTION_CREATE) {
        continue;
      }

      if ($is_translation && !$field->isTranslatable()) {
        continue;
      }

      // In the first run we can only set properties, not fields
      // Otherwise Drupal will throw Exceptions when using field references
      // if the translated entity has not been saved before..
      // Error message is: InvalidArgumentException: Invalid translation language (und) specified. in Drupal\Core\Entity\ContentEntityBase->getTranslation() (line 866 of /var/www/html/docroot/core/lib/Drupal/Core/Entity/ContentEntityBase.php).
      // Occurs when using translatable media entities referencing files.
      if (substr($key, 0, 6) == "field_") {
        continue;
      }

      $handler->import($intent);
    }

    try {
      $entity->save();
    }
    catch (\Exception $e) {
      throw new SyncException(SyncException::CODE_ENTITY_API_FAILURE, $e);
    }

    $changed = FALSE;
    foreach ($field_definitions as $key => $field) {
      $handler = $this->flow->getFieldHandler($type, $bundle, $key);

      if (!$handler) {
        continue;
      }

      // This field cannot be updated.
      if (in_array($key, $static_fields) && $intent->getAction() != SyncIntent::ACTION_CREATE) {
        continue;
      }

      // Now we can save all the fields instead of the properties.
      if (substr($key, 0, 6) != "field_") {
        continue;
      }

      $handler->import($intent);
      $changed = TRUE;
    }

    try {
      if ($changed) {
        $entity->save();
      }
    }
    catch (\Exception $e) {
      throw new SyncException(SyncException::CODE_ENTITY_API_FAILURE, $e);
    }


    if ($entity instanceof TranslatableInterface && !$intent->getActiveLanguage()) {
      $languages = $intent->getTranslationLanguages();
      foreach ($languages as $language) {
        /**
         * If the provided entity is fieldable, translations are as well.
         *
         * @var \Drupal\Core\Entity\FieldableEntityInterface $translation
         */
        if ($entity->hasTranslation($language)) {
          $translation = $entity->getTranslation($language);
        }
        else {
          $translation = $entity->addTranslation($language);
        }

        $intent->changeTranslationLanguage($language);
        if (!$this->ignoreImport($intent)) {
          $this->setEntityValues($intent, $translation);
        }
      }

      // Delete translations that were deleted on master site.
      if (boolval($this->settings['import_deletion_settings']['import_deletion'])) {
        $existing = $entity->getTranslationLanguages(FALSE);
        foreach ($existing as &$language) {
          $language = $language->getId();
        }
        $languages = array_diff($existing, $languages);
        foreach ($languages as $language) {
          $entity->removeTranslation($language);
        }
      }

      $intent->changeTranslationLanguage();
    }

    return TRUE;
  }



}
