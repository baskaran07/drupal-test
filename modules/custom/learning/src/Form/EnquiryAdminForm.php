<?php

namespace Drupal\learning\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class EnquiryAdminForm extends ConfigFormBase {
  public function getEditableConfigNames() {
    return [
      'learning.settings',
    ];
  }
  public function buildForm (array $form, FormStateInterface $form_state) {
    
    foreach(node_type_get_types() as $machine_name => $content_type) {
      $options[$machine_name ] = $content_type->label();
    }
//     kint($options);exit;
    $form['enquiry_form_content_type_list'] = [
      '#title' => 'Enquiry Form Admin Settings page',
      '#description' => 'Choose the content type for the enquiry form',
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->config('learning.settings')->get('enquiry_form_content_type_list') ? $this->config('learning.settings')->get('enquiry_form_content_type_list') : [],
    ];
    
    return parent::buildForm($form, $form_state);
  }

  public function getFormId () {
    return 'enquiry_admin_settings_form';
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $this->config('learning.settings')
      ->set('enquiry_form_content_type_list', $form_state->getvalue('enquiry_form_content_type_list'))
      ->save();
    drupal_set_message('Admin configuration settings changed');
  }
}