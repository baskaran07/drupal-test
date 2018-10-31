<?php

namespace Drupal\learning;

class NodesService {
  public function load() {
    $result = array();
    $query = \Drupal::database()->select('node', 'n');
    $query->fields('n', array());
    $result = $query->execute()->fetchAll();
    
    return $result;
  }
}
