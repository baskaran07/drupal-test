<?php

namespace Drupal\learning\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class MessagesForm extends ConfigFormBase {
  public function getEditableConfigNames() {
    return [
      'learning.messages',
    ];
  }
  public function buildForm (array $form, FormStateInterface $form_state) {
    $default_value = $this->config('learning.messages')->get('learning_message');
    $form['messages'] = [
      '#title' => 'Message Notification',
      '#description' => 'Message description',
      '#type' => 'textfield',
      '#default_value' => $default_value,
    ];
    
    return parent::buildForm($form, $form_state);
  }

  public function getFormId () {
    return 'learning_messages_form';
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $this->config('learning.messages')
      ->set('learning_message', $form_state->getvalue('messages'))
      ->save();
    drupal_set_message('Admin configuration settings changed');
  }
}