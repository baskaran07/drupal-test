<?php
// https://www.webomelette.com/choose-your-theme-dynamically-drupal-8-theme-negotiation
namespace Drupal\learning\Theme;

use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;

class ThemeNegotiator implements ThemeNegotiatorInterface {
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Use this theme on a certain route.
    // return $route_match->getRouteName() == 'example_route_name';

    // Or use this for more than one route:
    $possible_routes = array(
        'entity.taxonomy_term.add_form',
        'entity.taxonomy_term.edit_form'
    );
    
    //Prints the overridden configuration values.
    //kint(\Drupal::config('system.site')->get('name', FALSE));
    //Prints the original configuration value or Initial Default value.
    //kint(\Drupal::config('system.site')->getOriginal('name', FALSE));

    
//      kint(\Drupal::currentUser()->isAuthenticated());exit;
//     kint(\Drupal::config());
    if (($node = $route_match->getParameter('node')) && ($node->bundle() == 'article1') && (\Drupal::currentUser()->isAuthenticated())) {
//        if ($node->bundle() == 'article') {
         return TRUE;
//        }
    }
//     kint($node->bundle());
//     kint(\Drupal::Currentuser());
     return FALSE;
    //return (in_array($route_match->getRouteName(), $possible_routes));
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Here you return the actual theme name.
    return 'seven';
  }

}