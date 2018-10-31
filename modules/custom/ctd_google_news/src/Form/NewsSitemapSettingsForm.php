<?php
namespace Drupal\ctd_google_news\Form;


use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ctd_google_news\NewsSitemapGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewsSitemapSettingsForm extends ConfigFormBase
{
  /**
   * @var array
   */
  protected $formSettings = [
    'base_url',
    'default_keywords',
    'content_type_list',
  ];

  /**
   * @var NewsSitemapGenerator
   */
  var $generator;

  /**
   * NewsSitemapSettingsForm constructor.
   *
   * @param NewsSitemapGenerator $generator
   */
  public function __construct(NewsSitemapGenerator $generator)
  {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url;
    $markup = '<p>' . $this->t('This will generate the XML sitemap for news articles.') . '</p>';
    $markup .= '<p>' . $this->t('Note: content for the Google news sitemap is limited to content within a 48hr range.') . '</p>';

    $form['ctd_google_news_settings']['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open' => TRUE,
    ];

    $form['ctd_google_news_settings']['advanced']['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default base URL'),
      '#default_value' => $this->generator->getSetting('base_url', $base_url),
      '#size' => 30,
      '#description' => $this->t('Set the domain that should be rendered in the news.xml file.<br/>Example: <em>@url</em>', ['@url' => $GLOBALS['base_url']]),
    ];

    $form['ctd_google_news_settings']['advanced']['default_keywords'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default keywords'),
      '#default_value' => $this->generator->getSetting('default_keywords', "Entertainment,Celebrities"),
      '#size' => 30,
      '#description' => $this->t('Add keywords that will always be added for each news item. <br/>NOTE: Please separate each keyword with a comma.'),
    ];
  
    foreach(node_type_get_types() as $machine_name => $content_type) {
      $options[$machine_name ] = $content_type->label();
    }
//     kint($options);exit;
    $form['ctd_google_news_settings']['advanced']['content_type_list'] = [
      '#title' => 'Content Type',
      '#description' => 'Choose the content type for the google news',
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->config('ctd_google_news.settings')->get('content_type_list'),
    ];

    $form['ctd_google_news_settings']['generate'] = [
      '#type'   => 'fieldset',
      '#title'  => $this->t('Generate News sitemap'),
      '#submit' => ['::generateSitemap'],
      '#markup' => $markup,
    ];

    $form['ctd_google_news_settings']['generate']['generate_submit'] = [
      '#attributes'  => ['name' => 'generate-sitemap'],
      '#type'  => 'submit',
      '#value' => $this->t('Generate sitemap'),
      '#validate' => [],
    ];

    $form['ctd_google_news_generate_now'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Generate sitemap after hitting <em>Save</em>'),
      '#description'   => $this->t('This setting will generate the news sitemap including the above changes.'),
      '#default_value' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }
    /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('ctd_google_news.generator'));
  }

  public function generateSitemap()
  {
    if ($this->generator->generateSitemapIndex(NULL)) {
      // Let Google know that the Google News Sitemap has updated.
      $this->generator->pingGoogle();
     drupal_set_message($this->t('The News Sitemap has been successfully generated. <a href="@url" target="@target">news.xml</a>',
        ['@url' => '/news.xml', '@target' => '_blank']));
    } else {
      drupal_set_message($this->t('The News Sitemap has not been successfully generated.'), 'error');
    }
  }
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId()
  {
    return 'ctd_google_news_settings_form';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $trigger = $form_state->getTriggeringElement();

    switch ($trigger['#id']) {
      case 'edit-submit' :
        foreach ($this->formSettings as $setting_name) {
          $this->generator->saveSetting($setting_name, $form_state->getValue($setting_name));
        }

        parent::submitForm($form, $form_state);
        if (!$form_state->getValue('ctd_google_news_generate_now')) {
          break;
        }

      case 'edit-generate-submit':
        $this->generateSitemap();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $base_url = $form_state->getValue('base_url');
    $form_state->setValue('base_url', rtrim($base_url, '/'));
    if ($base_url != '' && !UrlHelper::isValid($base_url, TRUE)) {
      $form_state->setErrorByName('base_url', t('The base URL is invalid.'));
    }
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames()
  {
    return ['ctd_google_news.settings'];
  }
}
