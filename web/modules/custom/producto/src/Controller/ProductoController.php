<?php


namespace Drupal\producto\Controller;


use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

class ProductoController extends ControllerBase {
  const TIENDA_PREDETERMINADA = 1;


  /**
   * Formulario editar variación de producto.
   *
   * @param \Drupal\commerce_product\Entity\Product $commerce_product
   * @param \Drupal\commerce_product\Entity\ProductVariation $commerce_product_variation
   *
   * @return array
   */
  public function editarVariacion(Product $commerce_product, ProductVariation $commerce_product_variation): array {
    $form = \Drupal::service('entity.form_builder')->getForm($commerce_product_variation, 'edit');
    return [
      '#markup' =>  render($form),
    ];
  }

  /**
   * Titulo página formulario editar variación de producto.
   *
   * @param \Drupal\commerce_product\Entity\Product $commerce_product
   * @param \Drupal\commerce_product\Entity\ProductVariation $commerce_product_variation
   *
   * @return string
   */
  public function titleProductVariation(Product $commerce_product, ProductVariation $commerce_product_variation): string {
    return 'Editar ' . $commerce_product_variation->label();
  }

  /**
   * Permiso para acceder a editar variación de producto.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\commerce_product\Entity\Product $commerce_product
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function accessProductVariation(AccountInterface $account, Product $commerce_product) {
    $access = AccessResult::forbidden();

    if ($commerce_product->get('field_proveedor')->target_id) {
      $nodo = Node::load($commerce_product->get('field_proveedor')->target_id);
      if ($nodo instanceof Node) {
        if ($nodo->get('field_proveedor')->target_id) {
          if ($nodo->get('field_proveedor')->target_id == $account->id()) {
            $access = AccessResult::allowed();
          }
        }
      }
    }

    return $access;
  }
}
