<?php
namespace Drupal\ctd_google_news\Controller;

use Drupal\ctd_google_news\NewsSitemapGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SitemapController
 */
class SitemapController
{
  /**
   *
   * @return Response
   */
  public function index() {
    /**
     * @var NewsSitemapGenerator $generator
     */
    $generator = \Drupal::service('ctd_google_news.generator');
    $output = $generator->getSitemap();
    if (!$output) {
      throw new NotFoundHttpException();
    }

    // Display sitemap with correct XML header.
    $response = new Response($output, Response::HTTP_OK, ['content-type' => 'application/xml']);

    return $response;
  }
}
