<?php

namespace Drupal\grota_config\EventSubscriber;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\grota_config\Controller\DatosTotalesController;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
/**
 * Redirect .html pages to corresponding Node page.
 */
class PanelUsuarioSubscriber implements EventSubscriberInterface {

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $account;

  /**
   * The current request.
   *
   * @var ?\Symfony\Component\HttpFoundation\Request
   */
  protected ?\Symfony\Component\HttpFoundation\Request $request;


  public function __construct(AccountProxyInterface $account,  RequestStack $request_stack) {
    $this->account = $account;
    $this->request = $request_stack->getCurrentRequest();
  }

  public function checkoutRedirect() {
    $resultado = 0;
    $url_object = \Drupal::service('path.validator')->getUrlIfValid($this->request->getRequestUri());
    if ($url_object) {
      $route_name = $url_object->getRouteName();
      if ($route_name == 'commerce_checkout.form') {
        $params = $url_object->getRouteParameters();
        if (isset($params['step']) && $params['step'] == 'complete') {
          if(isset($params['commerce_order'])){
            $total_Co2 = DatosTotalesController::total_cantidad_co2();
            $total_organico = DatosTotalesController::total_cantidad_organico();
            $total_compostable = DatosTotalesController::total_cantidad_compostable();
            $total_biosintetico = DatosTotalesController::total_cantidad_biosintetico();
            $total_reciclable = DatosTotalesController::total_cantidad_reciclable();
            $total_reciclado = DatosTotalesController::total_cantidad_reciclado();
            $total_biodegradable = DatosTotalesController::total_cantidad_biodegradable();
            $pedido = Order::load($params['commerce_order']);
            if(isset($pedido->get('order_items')[0])){
              foreach($pedido->get('order_items') as $item){
                $order_item = OrderItem::load($item->target_id);
                $producto = Product::load($order_item->getPurchasedEntity()->get('product_id')->target_id);
                if(isset($producto->field_medicion_de_impacto[0])){
                  $tid = $producto->field_medicion_de_impacto[0]->target_id;
                  $term = Term::load($tid);
                  $industria = 0;
                  $grota = 0;
                  if(isset($term->field_co2_industria[0])) {
                    $industria = $term->field_co2_industria[0]->value;
                  }
                  if(isset($term->field_co2_grota[0])) {
                    $grota = $term->field_co2_grota[0]->value;
                  }
                  if($industria && $grota){
                    $resultado = $industria - $grota;
                  }else{
                    $resultado = 0;
                  }
                }

                //Total CO2
                //\Drupal::state()->set('aux2', $resultado);
                $cantidad = $order_item->quantity[0]->value;
                $total_producto = $resultado * $cantidad;
                $total_Co2 = $total_Co2 + $total_producto;
                //Total organico
                if(isset($producto->field_material_organico[0])){
                  $mat_organico = $producto->field_material_organico[0]->value;
                }else{
                  $mat_organico = 0;
                }
                $total_producto_org = $mat_organico * $cantidad;
                $total_organico = $total_organico + $total_producto_org;
                //Total compostable
                if(isset($producto->field_material_compostable[0])){
                  $mat_compostable = $producto->field_material_compostable[0]->value;
                }else{
                  $mat_compostable = 0;
                }
                $total_producto_comp = $mat_compostable * $cantidad;
                $total_compostable = $total_compostable + $total_producto_comp;

                //Total Biosintetico
                if(isset($producto->field_material_biosintetico[0])){
                  $mat_biosintetico = $producto->field_material_biosintetico[0]->value;
                }else{
                  $mat_biosintetico = 0;
                }
                $total_producto_bios = $mat_biosintetico * $cantidad;
                $total_biosintetico = $total_biosintetico + $total_producto_bios;

                //Total Reciclable
                if(isset($producto->field_material_reciclable[0])){
                  $mat_reciclable = $producto->field_material_reciclable[0]->value;
                }else{
                  $mat_reciclable = 0;
                }
                $total_producto_recicl = $mat_reciclable * $cantidad;
                $total_reciclable = $total_reciclable + $total_producto_recicl;

                //Total Reciclado
                if(isset($producto->field_material_reciclado[0])){
                  $mat_reciclado = $producto->field_material_reciclado[0]->value;
                }else{
                  $mat_reciclado = 0;
                }
                $total_producto_reciclado = $mat_reciclado * $cantidad;
                $total_reciclado = $total_reciclado + $total_producto_reciclado;

                //Total Biodegradable
                if(isset($producto->field_material_biodegradable[0])){
                  $mat_biodegradable = $producto->field_material_biodegradable[0]->value;
                }else{
                  $mat_biodegradable = 0;
                }
                $total_producto_biodegradable = $mat_biodegradable * $cantidad;
                $total_biodegradable = $total_biodegradable + $total_producto_biodegradable;
              }
            }
            //CO2 - Guardar en BBDD
            $sql = \Drupal::database()->update('node__field_total_co2');
            $sql->fields([
              'field_total_co2_value' => $total_Co2
            ]);
            $sql->execute();

            //Organico - Guardar en BBDD
            //\Drupal::state()->set('aux', $total_Co2);
            $sql = \Drupal::database()->update('node__field_total_organico');
            $sql->fields([
              'field_total_organico_value' => $total_organico
            ]);
            $sql->execute();

            //Compostable - Guardar en BBDD
            $sql = \Drupal::database()->update('node__field_total_compostable');
            $sql->fields([
              'field_total_compostable_value' => $total_compostable
            ]);
            $sql->execute();

            //Biosintetico - Guardar en BBDD
            $sql = \Drupal::database()->update('node__field_total_biosintetico');
            $sql->fields([
              'field_total_biosintetico_value' => $total_biosintetico
            ]);
            $sql->execute();

            //Reciclable - Guardar en BBDD
            $sql = \Drupal::database()->update('node__field_total_reciclable');
            $sql->fields([
              'field_total_reciclable_value' => $total_reciclable
            ]);
            $sql->execute();

            //Reciclado - Guardar en BBDD
            $sql = \Drupal::database()->update('node__field_total_reciclado');
            $sql->fields([
              'field_total_reciclado_value' => $total_reciclado
            ]);
            $sql->execute();

            //Biodegradable - Guardar en BBDD
            $sql = \Drupal::database()->update('node__field_total_biodegradable');
            $sql->fields([
              'field_total_biodegradable_value' => $total_biodegradable
            ]);
            $sql->execute();
          }
        }
      }
    }
  }

  /**
   * Listen to kernel.request events and call customRedirection.
   * {@inheritdoc}
   * @return array Event names to listen to (key) and methods to call (value)
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkoutRedirect', 220];
    return $events;
  }
}
