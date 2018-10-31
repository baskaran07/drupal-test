<?php

namespace Drupal\hub_user_import\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class HubUserImportController extends ControllerBase {

  /**
   * Constructs a simple page.
   *
   * The router _controller callback, maps the path
   * 'examples/page-example/simple' to this method.
   *
   * _controller callbacks return a renderable array for the content area of the
   * page. The theme system will later render and surround the content with the
   * appropriate blocks, navigation, and styling.
   */
  public function createusers() {
    $path = drupal_get_path('module', 'hub_user_import') . '/files/users_list.csv';
    $handle = fopen($path, 'r');
    kint($path);
    while ($row = fgetcsv($handle)) {
      $record = array();
      kint($row);exit;
      foreach ($row as $i => $field) { 
        $record[$columns[$i]] = $field;
      }
    }
    
    
    
    exit;
    $user = \Drupal\user\Entity\User::create();

    // Mandatory.
    $user->setPassword('password');
    $user->enforceIsNew();
    $user->setEmail('email');
    $user->setUsername('user_name');
    // Save user account.
    $result = $user->save();
    return [
      '#markup' => '<p>' . $this->t('Simple page: The quick brown fox jumps over the lazy dog.') . '</p>',
    ];
  }
}
