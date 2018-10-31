<?php

namespace Drupal\et_newsletter\Form;

use Drupal\Core\Form\FormStateInterface;

class NewsletterPageForm extends NewsletterSubscribeForm {

  /**
   * @inheritDoc
   */
  protected $formIdSuffix = 'page';

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Add terms and conditions.
    $form['terms'] = [
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#title' => $this->t('By checking this box you acknowledge our <a href=":privary_uri" target="_blank" rel="noopener" class="link">Privacy Policy</a> and agree to the <a href=":term_uri" target="_blank" class="link">Terms of Use</a>.', [
        ':term_uri' => 'https://www.cbsinteractive.com/legal/cbsi/terms-of-use',
        ':privacy_uri' => 'https://www.cbsinteractive.com/legal/cbsi/privacy-policy',
      ]),
    ];

    $form['terms_error_message'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#attributes' => [
        'class' => [
          'newsletter-error-message',
          'newsletter-error-message--terms'
        ],
      ],
      '#value' => $this->t('Please accept the Terms and Conditions.'),
    ];

    // Change the value of the submit button.
    $form['submit']['#value'] = $this->t('Sign up');

    return $form;
  }
}
