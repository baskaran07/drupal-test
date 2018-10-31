<?php

namespace Drupal\cms_content_sync\Plugin\cms_content_sync\field_handler;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\cms_content_sync\ExportIntent;
use Drupal\cms_content_sync\ImportIntent;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Reference menu references and make sure they're published as the content
 * comes available.
 *
 * @FieldHandler(
 *   id = "cms_content_sync_default_menu_link_content_reference_handler",
 *   label = @Translation("Default Menu Link Content Reference"),
 *   weight = 80
 * )
 *
 * @package Drupal\cms_content_sync\Plugin\cms_content_sync\field_handler
 */
class DefaultMenuLinkContentReferenceHandler extends DefaultEntityReferenceHandler {

  /**
   * {@inheritdoc}
   */
  public static function supports($entity_type, $bundle, $field_name, FieldDefinitionInterface $field) {
    if (!in_array($field->getType(), ["entity_reference"])) {
      return FALSE;
    }

    $type = $field->getSetting('target_type');
    return $type == 'menu_link_content';
  }

  /**
   *
   */
  protected function serializeReference(ExportIntent $intent, FieldableEntityInterface $reference, $value) {
    foreach ($this->getInvalidExportSubfields() as $field) {
      unset($value[$field]);
    }

    $value['enabled'] = $reference->get('enabled')->value;

    if ($this->shouldExportReferencedEntities()) {
      return $intent->embedEntity($reference, TRUE, $value);
    }
    else {
      return $intent->embedEntityDefinition(
        $reference->getEntityTypeId(),
        $reference->bundle(),
        $reference->uuid(),
        FALSE,
        $value
      );
    }
  }

  /**
   *
   */
  protected function setValues(ImportIntent $intent) {
    /**
     * @var \Drupal\Core\Entity\FieldableEntityInterface $entity
     */
    $entity = $intent->getEntity();

    $data = $intent->getField($this->fieldName);

    $values = [];
    foreach ($data as $value) {
      $reference = $this->loadReferencedEntity($intent, $value);

      if ($reference) {
        $info = $intent->getEmbeddedEntityData($value);
        if (isset($info['enabled'])) {
          $reference->set('enabled', $info['enabled']);
          $reference->save();
        }

        $attributes = [
          'target_id' => $reference->id(),
        ];

        $values[] = array_merge($info, $attributes);
      }
    }

    $entity->set($this->fieldName, $values);

    return TRUE;
  }

}
