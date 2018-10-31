<?php
/**
 * @file Contains the code for custom drush commmands for learning purpose.
 */
 
/**
* Implements hook_drush_command().
*/
function learning_drush_command() {
  $items = array();
  $items['publish'] = [
    'description' => 'Drush command to change node status',
    'aliases' => ['pub'],
    'arguments' => [
      'nid' => 'Node id of the page',
    ],
  ];
  $items['unpublish'] = [
    'description' => 'Drush command to change node status',
    'aliases' => ['unpub'],
    'arguments' => [
      'nid' => 'Node id of the page',
    ],
  ];
  return $items;
}

/**
* Callback function for custom drush command.
*/

function drush_learning_publish($nid = '') {
  drush_print('Drush commands are working fine');
  if ($nid) {
    $node = \Drupal\node\Entity\Node::load($nid);
    $node->set('title','New title');
    $node->setPublished(TRUE);
    $node->save();
    drush_print('Argument value is ' . $nid);
  }
}

/**
* Callback function for custom drush command.
*/

function drush_learning_unpublish($nid = '') {
  drush_print('Drush commands are working fine');
  if ($nid) {
    $node = \Drupal\node\Entity\Node::load($nid);
    $node->setPublished(FALSE);
    $node->save();
    drush_print('Argument value is ' . $nid);
  }
}