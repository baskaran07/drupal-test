<?php

namespace Drupal\learning\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a information about the block.
 *
 * @Block(
 *   id = "learn_block",
 *   admin_label = @Translation("Learning Blocks")
 * ) 
 */

class LearningBlock extends BlockBase {
  /**
  * {@inheritdoc}
  */
  public function build() {
    return [
      '#markup' => t("This is for Learning block"),
    ];
  }
  
  public function blockAccess(AccountInterface $account) {

  // Example code that would prevent displaying the 'Powered by Drupal' block in
  // a region different than the footer.
//     if (\Drupal::currentUser()->isAuthenticated()) {
//       return AccessResult::allowed();
//     }
   // kint($account);
//      kint($account->id());
//     kint(\Drupal::currentUser());
    return AccessResult::allowedIf(\Drupal::currentUser()->isAuthenticated());
    // No opinion.
    //return AccessResult::forbidden();
    //return AccessResult::allowed();
  }

}
