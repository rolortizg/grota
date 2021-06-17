<?php


namespace Drupal\admin_web\Controller;


use Drupal\Core\Controller\ControllerBase;

class AdminWebController extends ControllerBase {

  /**
   * Página entidades.
   *
   * @return string[]
   */
  public function entidadesPage() {
    return [
      '#type' => 'markup',
      '#markup' => 'Entidades',
    ];
  }
}
