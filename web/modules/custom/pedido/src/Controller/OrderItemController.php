<?php


namespace Drupal\pedido\Controller;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

class OrderItemController extends ControllerBase {

  /**
   * Formulario para editar linea de pedido.
   *
   * @param \Drupal\commerce_order\Entity\Order $commerce_order
   * @param \Drupal\commerce_order\Entity\OrderItem $commerce_order_item
   *
   * @return array
   */
  public function editOrderItem(Order $commerce_order, OrderItem $commerce_order_item): array {
    $form = \Drupal::service('entity.form_builder')->getForm($commerce_order_item, 'default');
    return [
      '#markup' =>  render($form),
    ];
  }

  /**
   * Titulo de la página de edición de la linea de pedido.
   *
   * @param \Drupal\commerce_order\Entity\Order $commerce_order
   * @param \Drupal\commerce_order\Entity\OrderItem $commerce_order_item
   *
   * @return string
   */
  public function titleOrderItem(Order $commerce_order, OrderItem $commerce_order_item) {
    return 'Editar Pedido #' . $commerce_order->getOrderNumber() . ' - ' . $commerce_order_item->label();
  }

  /**
   * Permiso para acceder a editar la linea de pedido.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\commerce_order\Entity\Order $commerce_order
   * @param \Drupal\commerce_order\Entity\OrderItem $commerce_order_item
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function accessOrderItem(AccountInterface $account, Order $commerce_order, OrderItem $commerce_order_item) {
    $access = AccessResult::forbidden();

    if ($commerce_order_item->get('field_proveedor')->target_id && $commerce_order_item->get('field_proveedor')->target_id == $account->id()) {
      $access = AccessResult::allowed();
    }

    return $access;
  }
}
