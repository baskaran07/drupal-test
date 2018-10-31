<?php

namespace Drupal\bluehornet\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines a campaign entity type
 *
 * @ContentEntityType(
 *   id = "bluehornet__campaign",
 *   label = @Translation("Bluehornet campaign"),
 *   handlers = {
 *     "form" = {
 *       "default" = "\Drupal\bluehornet\Form\CampaignForm",
 *       "edit" = "\Drupal\bluehornet\Form\CampaignForm",
 *       "add" = "\Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "\Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "list_builder" = "\Drupal\bluehornet\CampaignListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "views_data" = "\Drupal\views\EntityViewsData"
 *   },
 *   base_table = "bluehornet__campaign",
 *   data_table = "bluehornet__campaign_data",
 *   admin_permission = "administer bluehornet",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "subject",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/bluehornet__campaign/{bluehornet__campaign}",
 *     "add-form" = "/admin/bluehornet__campaign/add",
 *     "edit-form" = "/admin/bluehornet__campaign/{bluehornet__campaign}/edit",
 *     "delete-form" = "/admin/bluehornet__campaign/{bluehornet__campaign}/delete",
 *   },
 * )
 */
class Campaign extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['reply_email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Reply email'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'basic_string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'email_default',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['from_email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('From email'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'basic_string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'email_default',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['from_description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('From description'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['text_mail_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Text mail body'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 10,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['rich_mail_body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Rich mail body'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 10,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['schedule_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel('Scheduled publishing date')
      ->setRequired(FALSE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -5,
        'settings' => [
          'rows' => 10,
        ],
      ]);

    return $fields;
  }

  /**
   * @return string
   */
  public function getSubject() {
    return $this->get('subject')->value;
  }

  /**
   * @return string
   */
  public function getReplyEmail() {
    return $this->get('reply_email')->value;
  }

  /**
   * @return string
   */
  public function getFromEmail() {
    return $this->get('from_email')->value;
  }

  /**
   * @return string
   */
  public function getFromDescription() {
    return $this->get('from_description')->value;
  }

  /**
   * @return string
   */
  public function getFromTextMailBody() {
    return $this->get('text_mail_body')->value;
  }

  /**
   * @return string
   */
  public function getFromRichMailBody() {
    return $this->get('rich_mail_body')->value;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getScheduleDate() {
    return $this->get('schedule_date')->date;
  }

  /**
   * @return bool
   */
  public function hasScheduleDate() {
    return !$this->get('schedule_date')->isEmpty();
  }

}
