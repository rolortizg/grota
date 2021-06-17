<?php
namespace Drupal\grota_config\EventSubscriber;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Ajax\ReplaceCommand;
/**
 * Class ChangeAttributeCartEventSubscriber.
 *
 * @package Drupal\proceso_compra\EventSubscriber
 */
class ChangeAttributeCartEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait ;
  /**
   * {@inheritdoc}
   *
   * @return array
   * The events to subscribe to and the methods they should execute.
   */
  public static function getSubscribedEvents () {
    return [
      ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE => 'changeImageProduct' ,
    ];
  }
  /**
   * Cambia la imagen del producto al cambiar el atributo
   *
   * @param \Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent $event
   *
   */
  public function changeImageProduct(ProductVariationAjaxChangeEvent $event) {
    $variation = $event->getProductVariation();
    $imagen = '';
   // \Drupal::state()->get('aux', $variation);
    if($variation instanceof ProductVariation){
      if($variation->get('field_foto_del_color')[0] != null){
        $imagen .= '<div class="carousel-inner">';
        foreach($variation->get('field_foto_del_color') as $index=>$foto){
          if($index == 0){
            $imagen .= '<div class="carousel-item active">';
          }else{
            $imagen .= '<div class="carousel-item">';
          }
          if(is_object($foto->entity)){
            $uri = file_create_url($foto->entity->getFileUri());
            $imagen .= '<img class="d-block w-100" src="'.$uri.'" >';
          }
          $imagen .= '</div>';
        }
        $imagen .='</div>';
      }
    }

    if($imagen != ''){
      $output = $imagen;
      $event->getResponse()->addCommand(new ReplaceCommand('.carousel-inner', $output));
    }

  }
}
