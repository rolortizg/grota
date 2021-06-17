<?php


namespace Drupal\pedido\EventSubscriber;


use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartEventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      CartEvents::CART_ENTITY_ADD => 'addProveedor',
    ];
  }

  /**
   * Añadir proveedor a la linea de pedido.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   */
  public function addProveedor(CartEntityAddEvent $event) {
    /** @var ProductVariation $variation */
    $variation = $event->getEntity();
    /** @var \Drupal\commerce_product\Entity\Product $product */
    $product = $variation->getProduct();
    /** @var \Drupal\commerce_order\Entity\OrderItem $line */
    $line = $event->getOrderItem();

    if ($product->get('field_proveedor')->target_id) {
      $nodo_proveedor = Node::load($product->get('field_proveedor')->target_id);
      if ($nodo_proveedor instanceof Node) {
        if ($nodo_proveedor->get('field_proveedor')->target_id) {
          $proveedor = User::load($nodo_proveedor->get('field_proveedor')->target_id);
          if ($proveedor instanceof User) {
            $line->set('field_proveedor', $proveedor->id());
            try {
              $line->save();
            }
            catch (EntityStorageException $e) {
              \Drupal::logger('pedido')->error($e->getMessage());
            }
          }
        }
      }
    }
  }

}
