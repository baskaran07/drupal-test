<?php

namespace Drupal\learning;

class EnquiryService {
  public function select($nid) {
    $result = array();
    $database = \Drupal::database();
    $result = $database->select('enquiry_enabled', 'e')
      ->fields('e', array('nid'))
      ->condition('nid', $nid, '=')
      ->execute()
      ->fetchAll();

    return $result;
  }
   public function insert($nid) {
    $fields = array('nid' => $nid);
    $database = \Drupal::database();
    $database->insert('enquiry_enabled')
      ->fields(array('nid' => $nid))
      ->execute();
   }

  public function delete($nid) {
    $fields = array('nid' => $nid);
    $database = \Drupal::database();
    $database->delete('enquiry_enabled')
      ->condition('nid', $nid, '=')
      ->execute();
   }
  public function enquiry_list() {
    $result = array();
    $database = \Drupal::database();
    $result = $database->select('enquiry_list', 'e')
      ->fields('e', array('uid','nid','mail'))
      ->execute()
      ->fetchAll();

    return $result;
  }
}
