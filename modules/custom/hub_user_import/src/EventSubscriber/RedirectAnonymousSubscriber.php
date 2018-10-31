<?php

namespace Drupal\hub_user_import\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber subscribing to KernelEvents::REQUEST.
 */
class RedirectAnonymousSubscriber implements EventSubscriberInterface {

  public function __construct() {
    $this->account = \Drupal::currentUser();
  }

  public function checkAuthStatus(GetResponseEvent $event) {
    if ($this->account->isAnonymous()) {
      // Anonymous user...
      $current_uri = \Drupal::request()->getRequestUri();
      $path_arguments = explode('/' , $current_uri);
      if ($path_arguments[1] == 'hub') {
        $response = new RedirectResponse("/user/login", '301');
        $response->send();
        return;
      }
    }
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkAuthStatus', 100];
    return $events;
  }
}