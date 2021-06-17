<?php


namespace Drupal\pedido\Event;


use Drupal\commerce_order\Entity\OrderItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Evento para cambio de estado de la linea de pedido.
 *
 * @package Drupal\pedido\Event
 */
class OrderItemStateEvent extends Event {

  const UPDATE_STATE = 'commerce_order_item.state.update';

  /**
   * The order item.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  protected OrderItemInterface $orderItem;

  /**
   * Constructs a new OrderItemEvent.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item.
   */
  public function __construct(OrderItemInterface $order_item) {
    $this->orderItem = $order_item;
  }

  /**
   * Gets the order item.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface
   *   The order item.
   */
  public function getOrderItem(): OrderItemInterface {
    return $this->orderItem;
  }


}
