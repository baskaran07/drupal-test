<?php

namespace Drupal\cms_content_sync\Plugin\cms_content_sync\field_handler;

use Drupal\cms_content_sync\Plugin\FieldHandlerBase;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Providing a minimalistic implementation for any field type.
 *
 * @FieldHandler(
 *   id = "cms_content_sync_default_field_handler",
 *   label = @Translation("Default"),
 *   weight = 100
 * )
 *
 * @package Drupal\cms_content_sync\Plugin\cms_content_sync\field_handler
 */
class DefaultFieldHandler extends FieldHandlerBase {

  /**
   * {@inheritdoc}
   */
  public static function supports($entity_type, $bundle, $field_name, FieldDefinitionInterface $field) {
    $allowed = [
      "string",
      "integer",
      "uuid",
      "language",
      "created",
      "string_long",
      "boolean",
      "changed",
      "datetime",
      "map",
      "text_with_summary",
      "uri",
      "email",
      "timestamp",
      "text_long",
      "text",
      "metatag",
      "list_string",
      "list_float",
      "list_integer",
      "viewfield",
      "video_embed_field",
      "telephone",
      "soundcloud",
      "color_field_type",
      "comment",
    ];
    return in_array($field->getType(), $allowed) !== FALSE;
  }

}
