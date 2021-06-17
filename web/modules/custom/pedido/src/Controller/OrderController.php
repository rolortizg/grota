<?php


namespace Drupal\pedido\Controller;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

class OrderController extends ControllerBase {

  /**
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  private OrderInterface $order;

  public function __construct(OrderInterface $order) {
    $this->order = $order;
  }

  /**
   * Obtener las lineas de pedido del un proveedor.
   *
   * @param \Drupal\user\Entity\User $proveedor
   *
   * @return array
   */
  public function getOrderItemsProvider(User $proveedor): array {
    $lines = [];
    foreach ($this->order->getItems() as $item) {
      if ($item->get('field_proveedor')->target_id == $proveedor->id()) {
        $lines[] = $item;
      }
    }
    return $lines;
  }

  /**
   * Comprobar si un pedido tiene varios proveedores.
   *
   * @return bool
   */
  public function checkMultiProvider(): bool {
    $proveedores = [];
    foreach ($this->order->getItems() as $item) {
      if ($item->get('field_proveedor')->target_id) {
        $proveedores[$item->get('field_proveedor')->target_id] = $item->get('field_proveedor')->target_id;
      }
    }
    return count($proveedores) > 1;
  }

}
