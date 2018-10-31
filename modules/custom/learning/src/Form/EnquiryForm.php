<?php

namespace Drupal\learning\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Implements a Enquiry form.
*/
class EnquiryForm extends FormBase {
  /**
    *  (@inheritdoc)
    **/
  public function buildForm(array $form, FormStateInterface $form_state) {
  
    $form['mail'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#description' => 'Please enter valid email address',
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    
    return $form;
  }

  public function getFormId() {
    return 'learning_enquiry_form';
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $fields = [
      'mail' => $form_state->getValue('mail'),
      'uid' => \Drupal::currentUser()->id(),
      'created' => REQUEST_TIME,
    ];  
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $fields['nid'] = $node->id();
    }
    $database = \Drupal::database();
    $database->insert('enquiry_list')
      ->fields($fields)
      ->execute();
    drupal_set_message($this->t('Form is submitted and email address is %email',['%email' => $form_state->getValue('mail')]));
  }
}