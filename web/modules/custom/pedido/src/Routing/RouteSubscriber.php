<?php
namespace Drupal\pedido\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('commerce_checkout.form')) {
      $defaults = $route->getDefaults();
      $defaults['_controller'] = '\Drupal\pedido\Controller\CheckoutOverrideController:formPage';
      $route->setDefaults($defaults);
    }
  }

}
