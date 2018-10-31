<?php

namespace Drupal\et_amp;

use Drupal\Component\Utility\Html;
use Drupal\Core\Template\AttributeValueBase;

class AttributeJson extends AttributeValueBase {

  protected $data;

  public function __construct($name, $value) {
    parent::__construct($name, $value);

    $this->data = json_decode($value);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $value = (string) $this;
    if (isset($this->value) && static::RENDER_EMPTY_ATTRIBUTE || !empty($value)) {
      return Html::escape($this->name) . '=\'' . $value . '\'';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function value() {
    return json_encode($this->data);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->value();
  }

}
