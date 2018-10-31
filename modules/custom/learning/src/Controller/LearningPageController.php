<?php

namespace Drupal\learning\Controller;


use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for Learning page routes.
 */

class LearningPageController extends ControllerBase {
  public function simple() {
    //$query = \Drupal::database()->select('node', 'n');
    //$query->fields('n', array());
    //$result = $query->execute()->fetchAll();
    
   // $result = \Drupal::service("learning.get_nodes")->load();
    //kint($result);
    
//  $config = \Drupal::config('learning.messages');
 //drupal_set_message($config->get('learning_message'));
    $config = \Drupal::service('config.factory').getEditable('learning.messages');
    $config->set('learning_message','baskarans');
 kint($config);exit;

    return [
      '#markup' => $this->t('Simple page to check learning page is working or not')
    ];
  }
}