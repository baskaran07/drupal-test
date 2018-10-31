<?php

/**
 * @file
 * Contains Drush commands for CMS Content Sync.
 */

use Drupal\cms_content_sync\Entity\Pool;
use Drupal\cms_content_sync\Entity\Flow;
use Drupal\cms_content_sync\SyncCorePoolExport;
use Drupal\cms_content_sync\SyncCoreFlowExport;

/**
 * Implements hook_drush_command().
 */
function cms_content_sync_drush_command() {
  $items['content-sync-export'] = [
    'description' => dt('Export configuration to the Sync Core.'),
    'aliases' => ['cse'],
  ];

  return $items;
}

/**
 * Export configuration to the Content Sync backend.
 */
function drush_cms_content_sync_content_sync_export() {
  drush_print('Started export of pools.');
  foreach (Pool::getAll() as $pool) {
    $exporter = new SyncCorePoolExport($pool);
    $steps    = $exporter->prepareBatch();
    foreach ($steps as $step) {
      $exporter->executeBatch($step);
    }
  }
  drush_print('Finished export of pools.');
  drush_print('Started export of flows.');
  foreach (Flow::getAll() as $flow) {
    $exporter = new SyncCoreFlowExport($flow);
    $steps    = $exporter->prepareBatch();
    foreach ($steps as $step) {
      $exporter->executeBatch($step);
    }
  }
  drush_print('Finished export of flows.');
}
