<?php

namespace Drupal\et_newsletter\Form;

use Dawehner\Bluehornet\Client;
use Dawehner\Bluehornet\MethodRequests\LegacyManageSubscriber;
use Dawehner\Bluehornet\MethodResponses\CouldNotAuthenticateError;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the newsletter subscribe form.
 */
class NewsletterSubscribeForm extends FormBase {

  /**
   * The bluehornet client.
   *
   * @var \Dawehner\Bluehornet\Client
   */
  protected $client;

  /**
   * The form ID suffix.
   *
   * In order to have a working AJAX system with twice the same form we need
   * to vary the form ID as well as the success message div. Each instance of
   * the newsletter form should so pass along a custom suffix.
   *
   * @var string
   */
  protected $formIdSuffix;

  /**
   * The flood protection.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Creates a new NewsletterSubscribeForm instance.
   *
   * @param \Dawehner\Bluehornet\Client $client
   *   The bluehornet client.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood protection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(Client $client, FloodInterface $flood, DateFormatterInterface $date_formatter) {
    $this->client = $client;
    $this->flood = $flood;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('bluehornet.client'),
      $container->get('flood'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'et_newsletter_subscribe' . '_' . $this->formIdSuffix;
  }

  /**
   * Sets the form ID suffix.
   *
   * @param string $form_id_suffix
   *   The form ID suffix.
   *
   * @return $this
   *   The form ID suffix.
   */
  public function setFormIdSuffix($form_id_suffix) {
    $this->formIdSuffix = $form_id_suffix;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your E-Mail Address'),
      '#placeholder' => $this->t('Your E-Mail Address'),
      '#required' => TRUE,
    ];

    $form['email_error_message'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#attributes' => [
        'class' => [
          'newsletter-error-message',
          'newsletter-error-message--email'
        ],
      ],
      '#value' => $this->t('What, no email?'),
    ];

    $successMessageId = 'success-message--' . $this->formIdSuffix;
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxRenderSuccess',
        'wrapper' => $successMessageId,
        'progress' => 'none',
      ],
    ];

    $form['success_message'] = [
      '#type' => 'container',
      '#prefix' => '<div id="' . $successMessageId . '" class="newsletter-success-message">',
      '#suffix' => '</div>',
    ];
    if ($form_state->get('subscribed')) {
      $form['success_message']['#markup'] = $this->t('All set! You are now subscribed!');
    }

    // Attach the et_newsletter.form library.
    $form['#attached']['library'][] = 'et_newsletter/form';

    return $form;
  }

  public function ajaxRenderSuccess(array &$form, FormStateInterface $form_state) {
    return $form['success_message'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Allow admins to submit more
    if (!$this->currentUser()->hasPermission('administer site configuration')) {
      $limit = 2;
      $interval_one_day = 86400;

      if (!$this->flood->isAllowed('newsletter_subscription', $limit, $interval_one_day)) {
        $form_state->setErrorByName('', $this->t('You cannot subscribe than %limit times in @interval. Try again later.', [
          '%limit' => $limit,
          '@interval' => $this->dateFormatter->formatInterval($interval_one_day),
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $request = $this->client->createRequest();
    $email = $form_state->getValue('email');

    try {
      $request->addMethodCall(new LegacyManageSubscriber($email));
      $response = $this->client->sendRequest($request);

      if ($response instanceof CouldNotAuthenticateError) {
        $this->logger('php_bluehornet')
          ->error('Could not authenticate when subscribing @email', ['@email' => $email]);
      }
      $form_state->set('subscribed', TRUE);
      $form_state->disableRedirect(TRUE);
      $form_state->setRebuild(TRUE);

      $interval_one_day = 86400;
      $this->flood->register('newsletter_subscription', $interval_one_day);
    }
    catch (\Exception $e) {
      watchdog_exception('bluehornet', $e);
    }
  }

}
