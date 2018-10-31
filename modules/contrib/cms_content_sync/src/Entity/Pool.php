<?php

namespace Drupal\cms_content_sync\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Site\Settings;
use Drupal\cms_content_sync\SyncCorePoolExport;
use Drupal\cms_content_sync\ExportIntent;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Defines the "CMS Content Sync - Pool" entity.
 *
 * @ConfigEntityType(
 *   id = "cms_content_sync_pool",
 *   label = @Translation("CMS Content Sync - Pool"),
 *   handlers = {
 *     "list_builder" = "Drupal\cms_content_sync\Controller\PoolListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cms_content_sync\Form\PoolForm",
 *       "edit" = "Drupal\cms_content_sync\Form\PoolForm",
 *       "delete" = "Drupal\cms_content_sync\Form\PoolDeleteForm",
 *     }
 *   },
 *   config_prefix = "pool",
 *   admin_permission = "administer cms content sync:",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/services/cms_content_sync/pool/{cms_content_sync_pool}/edit",
 *     "delete-form" = "/admin/config/services/cms_content_sync/synchronizations/{cms_content_sync_pool}/delete",
 *   }
 * )
 */
class Pool extends ConfigEntityBase implements PoolInterface {

  /**
   * @var string POOL_USAGE_FORBID Forbid usage of this pool for this flow.
   */
  const POOL_USAGE_FORBID = 'forbid';
  /**
   * @var string POOL_USAGE_ALLOW Allow usage of this pool for this flow.
   */
  const POOL_USAGE_ALLOW = 'allow';
  /**
   * @var string POOL_USAGE_FORCE Force usage of this pool for this flow.
   */
  const POOL_USAGE_FORCE = 'force';

  /**
   * The Pool ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Pool label.
   *
   * @var string
   */
  public $label;

  /**
   * The Pool Sync Core backend URL.
   *
   * @var string
   */
  public $backend_url;

  /**
   * The unique site identifier.
   *
   * @var string
   */
  public $site_id;

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    try {
      foreach ($entities as $entity) {
        $exporter = new SyncCorePoolExport($entity);
        $exporter->remove(FALSE);
      }
    }
    catch (RequestException $e) {
      $messenger = \Drupal::messenger();
      $messenger->addError(t('The Sync Core server could not be accessed. Please check the connection.'));
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Returns the CMS Content Sync Backend URL for this pool.
   *
   * @return string
   */
  public function getBackendUrl() {
    // Check if the BackendUrl got overwritten.
    $cms_content_sync_settings = Settings::get('cms_content_sync');
    if (isset($cms_content_sync_settings) && isset($cms_content_sync_settings['pools'][$this->id]['backend_url'])) {
      return $cms_content_sync_settings['pools'][$this->id]['backend_url'];
    }
    else {
      return $this->backend_url;
    }
  }

  /**
   * Returns the site id this pool.
   *
   * @return string
   */
  public function getSiteId() {
    // Check if the siteID got overwritten.
    $cms_content_sync_settings = Settings::get('cms_content_sync');
    if (isset($cms_content_sync_settings) && isset($cms_content_sync_settings['pools'][$this->id]['site_id'])) {
      return $cms_content_sync_settings['pools'][$this->id]['site_id'];
    }
    else {
      return $this->site_id;
    }
  }

  /**
   * Get the newest import/export timestamp for this pool from all status
   * entities that exist for the given entity.
   *
   * @param $entity_type
   * @param $entity_uuid
   * @param bool $import
   *
   * @return int|null
   */
  public function getNewestTimestamp($entity_type, $entity_uuid, $import = TRUE) {
    $entity_status = EntityStatus::getInfoForPool($entity_type, $entity_uuid, $this);
    $timestamp = NULL;
    foreach ($entity_status as $info) {
      $item_timestamp = $import ? $info->getLastImport() : $info->getLastExport();
      if ($item_timestamp) {
        if (!$timestamp || $timestamp < $item_timestamp) {
          $timestamp = $item_timestamp;
        }
      }
    }
    return $timestamp;
  }

  /**
   * Get the newest import/export timestamp for this pool from all status
   * entities that exist for the given entity.
   *
   * @param $entity_type
   * @param $entity_uuid
   * @param int $timestamp
   * @param bool $import
   */
  public function setTimestamp($entity_type, $entity_uuid, $timestamp, $import = TRUE) {
    $entity_status = EntityStatus::getInfoForPool($entity_type, $entity_uuid, $this);
    foreach ($entity_status as $info) {
      if ($import) {
        $info->setLastImport($timestamp);
      }
      else {
        $info->setLastExport($timestamp);
      }
      $info->save();
    }
  }

  /**
   * Mark the entity as deleted in this pool (reflected on all entity status
   * entities related to this pool).
   *
   * @param $entity_type
   * @param $entity_uuid
   */
  public function markDeleted($entity_type, $entity_uuid) {
    $entity_status = EntityStatus::getInfoForPool($entity_type, $entity_uuid, $this);
    foreach ($entity_status as $info) {
      $info->isDeleted(TRUE);
      $info->save();
    }
  }

  /**
   * Check whether this entity has been deleted intentionally already. In this
   * case we ignore export and import intents for it.
   *
   * @param $entity_type
   * @param $entity_uuid
   *
   * @return bool
   */
  public function isEntityDeleted($entity_type, $entity_uuid) {
    $entity_status = EntityStatus::getInfoForPool($entity_type, $entity_uuid, $this);
    foreach ($entity_status as $info) {
      if ($info->isDeleted()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Load all cms_content_sync_pool entities.
   *
   * @return \Drupal\cms_content_sync\Entity\Pool[]
   */
  public static function getAll() {

    /**
     * @var \Drupal\cms_content_sync\Entity\Pool[] $configurations
     */
    $configurations = \Drupal::entityTypeManager()
      ->getStorage('cms_content_sync_pool')
      ->loadMultiple();

    return $configurations;
  }

  /**
   * Returns an list of pools that can be selected for an entity type.
   *
   * @oaram string $entity_type
   *  The entity type the pools should be returned for.
   * @param string $bundle
   *   The bundle the pools should be returned for.
   * @param \Drupal\Core\Entity\FieldableEntityInterface $parent_entity
   *   The
   *   parent entity, if any. Only required if $field_name is given-.
   * @param string $field_name
   *   The name of the parent entity field that
   *   references this entity. In this case if the field handler is set to
   *   "automatically export referenced entities", the user doesn't have to
   *   make a choice as it is set automatically anyway.
   *
   * @return array $selectable_pools
   */
  public static function getSelectablePools($entity_type, $bundle, $parent_entity = NULL, $field_name = NULL) {
    // Get all available flows.
    $flows = Flow::getAll();
    $configs = [];
    $selectable_pools = [];
    $selectable_flows = [];

    // When editing the entity directly, the "export as reference" flows won't be available and vice versa.
    $root_entity = !$parent_entity && !$field_name;
    if ($root_entity) {
      $allowed_export_options = [ExportIntent::EXPORT_FORCED, ExportIntent::EXPORT_MANUALLY, ExportIntent::EXPORT_AUTOMATICALLY];
    }
    else {
      $allowed_export_options = [ExportIntent::EXPORT_FORCED, ExportIntent::EXPORT_AS_DEPENDENCY];
    }

    foreach ($flows as $flow_id => $flow) {
      $flow_entity_config = $flow->getEntityTypeConfig($entity_type, $bundle);
      if (empty($flow_entity_config)) {
        continue;
      }
      if ($flow_entity_config['handler'] == 'ignore') {
        continue;
      }
      if (!in_array($flow_entity_config['export'], $allowed_export_options)) {
        continue;
      }
      if ($parent_entity && $field_name) {
        $parent_flow_config = $flow->sync_entities[$parent_entity->getEntityTypeId() . '-' . $parent_entity->bundle() . '-' . $field_name];
        if (!empty($parent_flow_config['handler_settings']['export_referenced_entities'])) {
          continue;
        }
      }

      $selectable_flows[$flow_id] = $flow;

      $configs[$flow_id] = [
        'flow_label' => $flow->label(),
        'flow' => $flow->getEntityTypeConfig($entity_type, $bundle),
      ];
    }

    foreach ($configs as $config_id => $config) {
      if (in_array('allow', $config['flow']['export_pools'])) {
        $selectable_pools[$config_id]['flow_label'] = $config['flow_label'];
        $selectable_pools[$config_id]['widget_type'] = $config['flow']['pool_export_widget_type'];
        foreach ($config['flow']['export_pools'] as $pool_id => $export_pool) {

          // Filter out all pools with configuration "allow".
          if ($export_pool == self::POOL_USAGE_ALLOW) {
            $pool_entity = \Drupal::entityTypeManager()->getStorage('cms_content_sync_pool')
              ->loadByProperties(['id' => $pool_id]);
            $pool_entity = reset($pool_entity);
            $selectable_pools[$config_id]['pools'][$pool_id] = $pool_entity->label();
          }
        }
      }
    }
    return $selectable_pools;
  }

}
