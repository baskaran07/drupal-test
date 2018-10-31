<?php
/**
 *  @file Contains \Drupal\learning\Plugin\Field\Fieldformatters\YoutubeLinkFormatter.php
 */
namespace Drupal\learning\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'youtube_link' formatter.
 *
 * @FieldFormatter(
 *   id = "youtube_link",
 *   label = @Translation("Youtube Link Formatter"),
 *   field_types = {
 *     "link",
 *   }
 * )
 */

class YoutubeLinkFormatter extends FormatterBase {
  
  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    
    $settings = $this->getSettings();
    $summary = [];
    $summary[] = t('Displays the youtube videos in Height : @height and Widht : @width', ['@height' => $settings['height'],'@width' => $settings['width']]);

    return $summary;
  }
  
  /**
 * {@inheritdoc}
 */
public static function defaultSettings() {
  return [
    // Declare a setting named 'text_length', with
    // a default value of 'short'
    'height' => '',
    'width' => '',
  ] + parent::defaultSettings();
}
  
  public function settingsForm(array $form, FormStateInterface $form_state) {
    
    $settings = $this->getSettings();
    
    $element['height'] = [
      '#title' => $this->t('Height'),
      '#type' => 'number',
      '#description' => $this->t('Enter iframe height in pixels'),
      '#default_value' => $settings['height'],
    ];
    
    $element['width'] = [
      '#title' => $this->t('Width'),
      '#type' => 'number',
      '#description' => $this->t('Enter iframe width in pixels'),
      '#default_value' => $settings['width'],
    ];
    
    return $element;
  }
  
  /**
   *  {@inheritdoc}
   */
  
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();
//     kint($items);exit;
    foreach($items as $delta => $item) {
      
//       kint($item);
//       kint($item->uri);
      
//       exit;
      $data['url'] = $item->uri;
      $data['height'] = $settings['height'];
      $data['width'] = $settings['width'];

      $elements[$delta] = array(
        '#theme' => 'youtube_link_formatter',
        '#data' => $data,
      );
    }
    return $elements;
  }
}