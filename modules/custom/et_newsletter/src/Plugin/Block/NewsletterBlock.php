<?php

namespace Drupal\et_newsletter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\et_newsletter\Form\NewsletterSubscribeForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a newsletter subscribe block with a form.
 *
 * @Block(
 *   id = "et_newsletter_subscribe",
 *   admin_label = @Translation("Newsletter Sign-up")
 * )
 */
class NewsletterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
   return [
     'form_id_suffix' => '',
   ] + parent::defaultConfiguration();

   return $defaults;
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = NewsletterSubscribeForm::create(\Drupal::getContainer());
    $form->setFormIdSuffix($this->getConfiguration()['form_id_suffix']);

    return $this->formBuilder->getForm($form);
  }

}
