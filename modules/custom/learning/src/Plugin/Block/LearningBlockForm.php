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
 *   id = "learn_block_form",
 *   admin_label = @Translation("Learning Blocks Form")
 * ) 
 */

class LearningBlockForm extends BlockBase {
  /**
  * {@inheritdoc}
  */
  public function build() {
  
//     return [
//       '#markup' => t("This is for Learning block"),
//     ];
    
    $form = \Drupal::formBuilder()->getForm('Drupal\learning\Form\EnquiryForm');
    
    return $form;
  }
  
  public function blockAccess(AccountInterface $account) {
    $node = \Drupal::routeMatch()->getParameter('node');

    if ($node instanceof \Drupal\node\NodeInterface) {
      $allowed_types = \Drupal::config('learning.settings')->get('enquiry_form_content_type_list');
      if (in_array($node->getType(), $allowed_types, true)) {
         //return AccessResult::allowed();
          if (!empty(\Drupal::service("learning.enquiry")->select($node->id()))) {
            return AccessResult::allowed();
          }
        }
    }
  // Example code that would prevent displaying the 'Powered by Drupal' block in
  // a region different than the footer.
//     if (\Drupal::currentUser()->isAuthenticated()) {
//       return AccessResult::allowed();
//     }
   // kint($account);
//      kint($account->id());
//     kint(\Drupal::currentUser());
    //return AccessResult::allowedIfHasPermissions($account, ['access enquire form']);
    //return AccessResult::allowedIf(\Drupal::currentUser()->isAuthenticated());
    // No opinion.
    return AccessResult::forbidden();
    //return AccessResult::allowed();
  }

}
