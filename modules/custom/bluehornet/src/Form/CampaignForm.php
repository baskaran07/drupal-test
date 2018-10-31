<?php

namespace Drupal\bluehornet\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class CampaignForm extends ContentEntityForm {

  /**
   * @var \Drupal\bluehornet\Entity\Campaign
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['send'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#submit' => ['::submitForm', '::save', '::send'],
    );

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\bluehornet\BluehornetSender $sender */
    $sender = \Drupal::service('bluehornet.sender');

    $sender->send($this->entity);
  }

}
