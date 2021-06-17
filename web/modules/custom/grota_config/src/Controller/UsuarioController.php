<?php


namespace Drupal\grota_config\Controller;


use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Controller\ControllerBase;
use Drupal\content_statistics\Controller;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\taxonomy\Entity\Term;
use Drupal\grota_config\Controller\ProductoController;


/**
 * Class UsuarioController.
 */
class UsuarioController extends ControllerBase {

  public static function datos_usuario(){
    $current_user = \Drupal::currentUser();
    //$user = \Drupal\user\Entity\User::load($current_user->id());
    $pedidos = [];
    $sql = \Drupal::database()->select('commerce_order','c');
    $sql->join('commerce_order_item', 'i', 'c.order_id = i.order_id');
    $sql->join('commerce_order__order_items', 'o', 'o.order_items_target_id = i.order_item_id');
    $sql->fields('i',array('order_item_id'));
    $sql->fields('o',array('entity_id'));
    $sql->condition('c.uid',$current_user->id(),'=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $pedidos[$fil['order_item_id']] = $fil['entity_id'];
    }
    $ahorro_impacto = 0;
    $industria_total = 0;
    $grota_total = 0;
    $resultados = [];
    $resultados['materiales_sostenibles'] =[];
    $resultados['materiales_sostenibles']['biodegradable'] = 0;
    $resultados['materiales_sostenibles']['biosintetico'] = 0;
    $resultados['materiales_sostenibles']['compostable'] = 0;
    $resultados['materiales_sostenibles']['organico'] = 0;
    $resultados['materiales_sostenibles']['reciclable'] = 0;
    $resultados['materiales_sostenibles']['reciclado'] = 0;
    $resultados['labor_social'] =[];
    $resultados['materiales'] =[];
    $resultados['produccion'] =[];
    foreach($pedidos as $key=>$order_item){
      $item_pedido = OrderItem::load($key);
      $productId = $item_pedido->getPurchasedEntity()->get('product_id')->target_id;
      $producto = Product::load($productId);
      if(isset($producto->field_medicion_de_impacto[0])){
        $tid_mi = $producto->field_medicion_de_impacto[0]->target_id;
        $term = Term::load($tid_mi);
        //kint($term);
        if(isset($term->field_co2_industria[0])) {
          $industria = $term->field_co2_industria[0]->value;
          $industria_total = $industria_total + $industria;
        }
        if(isset($term->field_co2_grota[0])) {
          $grota = $term->field_co2_grota[0]->value;
          $grota_total = $grota_total + $grota;
        }
      }

      if(isset($producto->field_material_biodegradable[0])){
        $resultados['materiales_sostenibles']['biodegradable'] = $resultados['materiales_sostenibles']['biodegradable'] +$producto->field_material_biodegradable[0]->value;
      }
      if(isset($producto->field_material_biosintetico[0])){
        $resultados['materiales_sostenibles']['biosintetico'] = $resultados['materiales_sostenibles']['biosintetico'] +$producto->field_material_biosintetico[0]->value;
      }
      if(isset($producto->field_material_compostable[0])){
        $resultados['materiales_sostenibles']['compostable'] = $resultados['materiales_sostenibles']['compostable'] +$producto->field_material_compostable[0]->value;
      }
      if(isset($producto->field_material_organico[0])){
        $resultados['materiales_sostenibles']['organico'] = $resultados['materiales_sostenibles']['organico'] + $producto->field_material_organico[0]->value;
      }
      if(isset($producto->field_material_reciclable[0])){
        $resultados['materiales_sostenibles']['reciclable'] = $resultados['materiales_sostenibles']['reciclable'] +$producto->field_material_reciclable[0]->value;
      }
      if(isset($producto->field_material_reciclado[0])){
        $resultados['materiales_sostenibles']['reciclado'] = $resultados['materiales_sostenibles']['reciclado'] +$producto->field_material_reciclado[0]->value;
      }
      $opciones_labor_social = ProductoController::labor_social_opciones();
      $opciones_produccion = ProductoController::produccion_opciones();
      $opciones_materiales = ProductoController::materiales_opciones();

      if(isset($producto->field_labor_social[0])){
        foreach($opciones_labor_social as $key => $opcion){
              $labor = Term::load($key);
              if(isset($labor->field_icono[0])){
                $resultados['labor_social'][$key]['icono'] = $labor->field_icono[0]->entity->uri->value;
                $aux = false;
                foreach($producto->field_labor_social as $tid){
                  if($aux == false){
                    if($key == $tid->target_id){
                      $resultados['labor_social'][$key]['mostrar'] = true;
                      $aux = true;
                    }else{
                      $resultados['labor_social'][$key]['mostrar'] = false;
                    }
                  }
                }

              }
            }

      }

      if(isset($producto->field_produccion[0])){
        foreach($opciones_produccion as $key=>$prod){
          $prod = Term::load($key);
          if(isset($prod->field_icono[0])){
            $resultados['produccion'][$key]['icono'] = $prod->field_icono[0]->entity->uri->value;
            $aux2 = false;
            foreach($producto->field_produccion as $tid){
              if($aux2 == false){
                if($key == $tid->target_id){
                  $resultados['produccion'][$key]['mostrar'] = true;
                  $aux2 = true;
                }else{
                  $resultados['produccion'][$key]['mostrar'] = false;
                }
              }
            }

          }
        }

      }

      if(isset($producto->field_materiales[0])){
        foreach($opciones_materiales as $key=>$opc){
          $material = Term::load($key);
          if(isset($material->field_icono[0])) {
            $resultados['materiales'][$key]['icono'] = $material->field_icono[0]->entity->uri->value;
            $aux3 = FALSE;
            foreach ($producto->field_materiales as $tid) {
              if ($aux3 == FALSE) {
                if ($key == $tid->target_id) {
                  $resultados['materiales'][$key]['mostrar'] = TRUE;
                  $aux3 = TRUE;
                }
                else {
                  $resultados['materiales'][$key]['mostrar'] = FALSE;
                }
              }
            }
          }

        }

      }
    }
    if($industria_total && $grota_total){
      $resultado = $industria_total - $grota_total;
    }else{
      $resultado = 0;
    }
    $resultados['industria'] = $industria_total;
    $resultados['grota'] = $grota_total;
    $resultados['ahorro'] = $resultado;
    return $resultados;
  }

  public static function comprobar_mail($email){
    $sql = \Drupal::database()->select('users_field_data','u');
    $sql->fields('u', array('uid'))
      ->condition('mail', $email, '=');
    $resul = $sql->execute();
    if ($fil= $resul->fetchAssoc()) {
      return  $fil['uid'];
    }
    return false;
  }
}

