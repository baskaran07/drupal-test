<?php

namespace Drupal\et_amp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\et_amp\AttributeJson;

/**
 * Provides an AMP Google DoubleClick for Publishers block
 *
 * @Block(
 *   id = "et_amp_google_doubleclick_block",
 *   admin_label = @Translation("ET AMP DFP"),
 * )
 */
class AmpGoogleDoubleClickBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'height' => '',
      'width' => '',
      'data_slot' => '',
      'json' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get global DoubleClick configuration.
    $amp_config = \Drupal::config('amp.settings');
    $doubleclick_id = $amp_config->get('google_doubleclick_id');
    if (empty($doubleclick_id)) {
      return array(
        '#markup' => $this->t('This block requires a Google DoubleClick Network ID.')
      );
    }

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    $data_slot = $config['data_slot'];
    $height = $config['height'];
    $width = $config['width'];
    $json = $config['json'];

    $result = [
      'inside' => [
        '#theme' => 'amp_ad',
        '#type' => 'doubleclick',
        '#attributes' => [
          'height' => $height,
          'width' => $width,
          'data-slot' => $doubleclick_id . '/' . $data_slot
        ]
      ]
    ];
    if (!empty($json)) {
      $data = [];
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      if (\Drupal::request()->attributes->has('_entity') && $entity = \Drupal::request()->attributes->get('_entity')) {
        $data[$entity->getEntityTypeId()] = $entity;
      }

      $bubbleable_metadata = new BubbleableMetadata();
      $result['inside']['#attributes']['json'] = new AttributeJson('json', \Drupal::token()->replace($json, $data, [], $bubbleable_metadata));
      $bubbleable_metadata->applyTo($result['inside']);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    $form['width'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => isset($config['width']) ? $config['width'] : '',
      '#maxlength' => 25,
      '#size' => 20,
    );
    $form['height'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => isset($config['height']) ? $config['height'] : '',
      '#maxlength' => 25,
      '#size' => 20,
    );
    $form['data_slot'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Data-slot'),
      '#default_value' => isset($config['data_slot']) ? $config['data_slot'] : '',
      '#maxlength' => 25,
      '#size' => 20,
    );

    $form['json'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('JSON'),
      '#default_value' => isset($config['json']) ? $config['json'] : '',
    );

    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $form['tokens'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => 'all',
        '#global_types' => FALSE,
        '#dialog' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('width', $form_state->getValue('width'));
    $this->setConfigurationValue('height', $form_state->getValue('height'));
    $this->setConfigurationValue('data_slot', $form_state->getValue('data_slot'));
    $this->setConfigurationValue('json', $form_state->getValue('json'));
  }

}
