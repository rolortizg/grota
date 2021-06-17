<?php


namespace Drupal\pedido\Controller;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Controller\ControllerBase;

class CartController extends ControllerBase {
  public static function checkStock(Order $order) {
    $error = FALSE;
    foreach ($order->getItems() as $item) {
      if ($item instanceof OrderItem) {
        $quantity = (int) $item->getQuantity();
        $variation = $item->getPurchasedEntity();
        if ($variation instanceof ProductVariation) {
          $stock = (int) $variation->get('field_stock')->value;
          if ($stock < $quantity) {
            $product = $variation->getProduct();
            if ($product instanceof Product) {
              $error = 'El producto ' . $product->label() . ' ya no esta disponible';
            }
          }
        }
      }
    }
    return $error;
  }
}
