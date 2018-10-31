<?php

namespace Drupal\et_amp\EventSubscriber;

use Drupal\amp\Routing\AmpContext;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disables browser new relic tracking on AMP requests.
 */
class DisableNewRelic implements EventSubscriberInterface {

  /**
   * The route amp context to determine whether a route is an amp one.
   *
   * @var \Drupal\amp\Routing\AmpContext
   */
  protected $ampContext;

  /**
   * Constructs an AmpHtmlResponseSubscriber object.
   *
   * @param \Drupal\amp\Routing\AmpContext $amp_context
   *   The amp context.
   */
  public function __construct(AmpContext $amp_context) {
    $this->ampContext = $amp_context;
  }

  /**
   * Processes markup for HtmlResponse responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    $response = $event->getResponse();

    if (!$response instanceof HtmlResponse) {
      return;
    }

    if ($this->ampContext->isAmpRoute() && function_exists('newrelic_disable_autorum')) {
      newrelic_disable_autorum();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Runs before \Drupal\amp\EventSubscriber\AmpHtmlResponseSubscriber.
    $events[KernelEvents::RESPONSE][] = ['onRespond', -512];
    return $events;
  }

}
