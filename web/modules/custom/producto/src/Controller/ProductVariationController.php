<?php


namespace Drupal\producto\Controller;


use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

class ProductVariationController{

  /**
   * @var \Drupal\commerce_product\Entity\ProductVariation
   */
  private ProductVariation $entity;

  public function __construct(ProductVariation $productVariation) {
    $this->entity = $productVariation;
  }


  /**
   * Comporbar stock.
   *
   * @return mixed
   */
  public function comprobarStock() {
    return $this->entity->get('field_stock')->value;
  }

  /**
   * Obtener variación disponible.
   *
   * @param \Drupal\commerce_product\Entity\Product $product
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface|\Drupal\Core\Entity\ContentEntityInterface|null
   */
  public function getVariacionDisponible(Product $product) {
    foreach ($product->getVariations() as $variation) {
      if ($variation->get('field_stock')->value) {
        return $variation;
      }
    }
    return NULL;
  }

}
