<?php


namespace Drupal\admin_web\Routing;


use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.admin_config')) {
      $route->setRequirement('_permission', 'access site configuration');
    }

    if ($route = $collection->get('system.admin_structure')) {
      $route->setRequirement('_permission', 'access structure');
    }
  }

}
