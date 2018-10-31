<?php

namespace Drupal\learning\Controller;


use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for Learning page routes.
 */

class EnquiryListController extends ControllerBase {
  public function load() {
    //$query = \Drupal::database()->select('node', 'n');
    //$query->fields('n', array());
    //$result = $query->execute()->fetchAll();
    
   // $result = \Drupal::service("learning.get_nodes")->load();
    //kint($result);
    
//  $config = \Drupal::config('learning.messages');
 //drupal_set_message($config->get('learning_message'));
    $results = [];
    $results = \Drupal::service("learning.enquiry")->enquiry_list();
    $data = [];
    foreach($results as $result) {
      //kint($result->uid);
      $account = \Drupal\user\Entity\User::load($result->uid); // pass your uid
      $node = \Drupal\node\Entity\Node::load($result->nid);
      $data[] = [$account->getUsername(),$node->getTitle(),$result->mail];
    }
//kint($data);
    return [
      '#theme' => 'table',
      //'#cache' => ['disabled' => TRUE],
    '#caption' => 'Below is a list of all Event RSVPs including username, email address and the name of the event they will be attending.',
    '#header' => ['Name','Event','Email'],
    '#rows' => $data,
    ];
  }
}