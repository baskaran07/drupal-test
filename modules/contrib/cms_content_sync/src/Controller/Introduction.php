<?php

namespace Drupal\cms_content_sync\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class Introduction provides a static page describing how
 * CMS Content Sync can be used.
 */
class Introduction extends ControllerBase {

  /**
   * @return array The content array to theme the introduction.
   */
  public function content() {
    return [
      '#theme' => 'cms_content_sync_introduction',
    ];
  }

}
