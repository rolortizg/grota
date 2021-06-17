<?php


namespace Drupal\proveedor\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

class ProveedorController extends ControllerBase {

  /**
   * Redirigir a editar perfil.
   *
   * @return array
   */
  public function editarPerfil() {
    $entity = User::load(\Drupal::currentUser()->id());
    $form = \Drupal::service('entity.form_builder')->getForm($entity, 'default');
    return [
      '#markup' =>  render($form),
    ];
  }

}
