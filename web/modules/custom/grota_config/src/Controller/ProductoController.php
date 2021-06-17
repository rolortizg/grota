<?php


namespace Drupal\grota_config\Controller;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Controller\ControllerBase;
use Drupal\content_statistics\Controller;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ProductoController.
 */
class ProductoController extends ControllerBase {

  /**
   * Contenido Moda.
   *
   * @return string
   */
  public function producto(){
    $theme = [
      '#theme' => 'moda',
      '#content' => '',
      '#cache' => ['max-age' => 0],
    ];
    $output = [
      '#type' => 'markup',
      '#markup' => \Drupal::service('renderer')->render($theme),
    ];
    return $output;
  }

  public function producto_hogar(){
    $theme = [
      '#theme' => 'hogar',
      '#content' => '',
      '#cache' => ['max-age' => 0],
    ];
    $output = [
      '#type' => 'markup',
      '#markup' => \Drupal::service('renderer')->render($theme),
    ];
    return $output;
  }
  public function producto_dia_a_dia(){
    $theme = [
      '#theme' => 'dia_a_dia',
      '#content' => '',
      '#cache' => ['max-age' => 0],
    ];
    $output = [
      '#type' => 'markup',
      '#markup' => \Drupal::service('renderer')->render($theme),
    ];
    return $output;
  }

  public static function labor_social(){
    $context = stream_context_create(
      array(
        "http" => array(
          "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
      )
    );
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->join('commerce_product__field_labor_social', 'l', 'l.field_labor_social_target_id = t.tid');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','labor_social','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['tid']);
      if($term->field_icono[0]){
        $terms[$fil['tid']] = file_get_contents($term->field_icono[0]->entity->uri->value, false, $context).'<p>'.$term->name->value.'</p>';
      }
    }
    return $terms;
  }

  public static function labor_social_opciones(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','labor_social','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['tid']);
      if($term->field_icono[0]){
        $terms[$fil['tid']] = '<img src="'.file_create_url($term->field_icono[0]->entity->uri->value).'" />';
      }
    }
    return $terms;
  }

  public static function produccion(){
    $context = stream_context_create(
      array(
        "http" => array(
          "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
      )
    );
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->join('commerce_product__field_produccion', 'p', 'p.field_produccion_target_id = t.tid');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','produccion','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['tid']);
      if($term->field_icono[0]){
        $terms[$fil['tid']] = file_get_contents($term->field_icono[0]->entity->uri->value, false, $context).'<p>'.$term->name->value.'</p>';
      }
    }
    return $terms;
  }

  public static function produccion_opciones(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','produccion','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['tid']);
      if($term->field_icono[0]){
        $terms[$fil['tid']] = '<img src="'.file_create_url($term->field_icono[0]->entity->uri->value).'" />';
      }
    }
    return $terms;
  }
  public static function materiales(){
    $context = stream_context_create(
      array(
        "http" => array(
          "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
      )
    );
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->join('commerce_product__field_materiales', 'm', 'm.field_materiales_target_id = t.tid');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','materiales','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['tid']);
      if($term->field_icono[0]){
        $terms[$fil['tid']] = file_get_contents($term->field_icono[0]->entity->uri->value, false, $context).'<p>'.$term->name->value.'</p>';
      }
    }
    return $terms;
  }

  public static function materiales_opciones(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','materiales','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['tid']);
      if(isset($term->field_icono[0])){
        $terms[$fil['tid']] = '<img src="'.file_create_url($term->field_icono[0]->entity->uri->value).'" />';
      }
    }
    return $terms;
  }
  public static function padres(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term__parent','p');
    $sql->join('taxonomy_term_field_data', 't', 'p.entity_id = t.tid');
    $sql->fields('p',array('parent_target_id'));
    $sql->condition('p.parent_target_id',0,'!=');
    $sql->condition('t.vid','categoria_de_producto','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['parent_target_id']);
      $name = $term->name->value;
      $name = str_replace(' ', '_', $name);
      $terms[$fil['parent_target_id']] = strtolower($name);
    }
    return $terms;
  }
  public static function padres_hogar(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term__parent','p');
    $sql->join('taxonomy_term_field_data', 't', 'p.entity_id = t.tid');
    $sql->fields('p',array('parent_target_id'));
    $sql->condition('p.parent_target_id',0,'!=');
    $sql->condition('t.vid','hogar_categoria_de_producto_deco','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['parent_target_id']);
      $name = $term->name->value;
      $name = str_replace(' ', '_', $name);
      $terms[$fil['parent_target_id']] = strtolower($name);
    }
    return $terms;
  }
  public static function padres_dia_a_dia(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term__parent','p');
    $sql->join('taxonomy_term_field_data', 't', 'p.entity_id = t.tid');
    $sql->fields('p',array('parent_target_id'));
    $sql->condition('p.parent_target_id',0,'!=');
    $sql->condition('t.vid','dia_a_dia_categoria_de_producto_','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['parent_target_id']);
      $name = $term->name->value;
      $name = str_replace(' ', '_', $name);
      $terms[$fil['parent_target_id']] = strtolower($name);
    }
    return $terms;
  }
  public static function publico_dirigido(){
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term_field_data','t');
    $sql->fields('t',array('tid', 'name'));
    $sql->condition('t.vid','textil_tipo_de_publico','=');
    $resul = $sql->execute();
    $terms['all'] = 'Todos';
    while( $fil= $resul->fetchAssoc() ) {
      $terms[$fil['tid']] = $fil['name'];
    }
    return $terms;
  }
  public static function tids_hogar_padres(){
    $tids = [];
    $sql = \Drupal::database()->select('taxonomy_term__parent','p');
    $sql->fields('p',array('parent_target_id'));
    $sql->condition('p.bundle','hogar_categoria_de_producto_deco','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $tids[$fil['parent_target_id']] = $fil['parent_target_id'];
    }
    return $tids;
  }
  public static function tids_moda_padres(){
    $tids = [];
    $sql = \Drupal::database()->select('taxonomy_term__parent','p');
    $sql->fields('p',array('parent_target_id'));
    $sql->condition('p.bundle','categoria_de_producto','=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $tids[$fil['parent_target_id']] = $fil['parent_target_id'];
    }
    return $tids;
  }

  public static function hijos($padre, $publico){
    $tids_moda = ProductoController::tids_moda_padres();
    $tids_hogar = ProductoController::tids_hogar_padres();
    $aux1 = false;
    if(!empty($tids_moda)){
      foreach($tids_moda as $tid){
        if($tid == $padre){
          $aux1=true;
        }
      }
    }

    $aux2 = false;
    if(!empty($tids_hogar)){
      foreach($tids_hogar as $tid){
        if($tid == $padre){
          $aux2=true;
        }
      }
    }
    $terms = [];
    $sql = \Drupal::database()->select('taxonomy_term__parent','p');
    $sql->join('taxonomy_term_field_data', 't', 'p.entity_id = t.tid');

    if($aux1 == true){

      $sql->join('commerce_product__73aa65daa1', 'c', 'c.field_textil_categoria_del_produ_target_id = t.tid');
      if($publico != 'all'){
        $sql->join('commerce_product__field_categoria_de_producto', 'cp', 'cp.entity_id = c.entity_id');
      }

    }else if($aux2 == true){
      $sql->join('commerce_product__81fd118133', 'c', 'c.field_hogar_categoria_de_product_target_id = t.tid');
      if($publico != 'all'){
        $sql->join('commerce_product__field_categoria_de_producto', 'cp', 'cp.entity_id = c.entity_id');
      }
    }else{
      $sql->join('commerce_product__85eaf71432', 'c', 'c.field_dia_a_dia_categoria_de_pro_target_id = t.tid');
      if($publico != 'all'){
        $sql->join('commerce_product__field_categoria_de_producto', 'cp', 'cp.entity_id = c.entity_id');
      }
    }
    $sql->fields('p',array('parent_target_id','entity_id'));
    $sql->fields('t',array('name', 'tid'));
    $sql->condition('p.parent_target_id',$padre,'=');
    if($publico != 'all'){
      $sql->condition('cp.field_categoria_de_producto_target_id',$publico,'=');
    }
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $term = Term::load($fil['parent_target_id']);
      $name = $term->name->value;
      $name = str_replace(' ', '_', $name);
      $terms[$fil['tid']] = $fil['name'];
    }

    return $terms;
  }

  //Productos relacionados
  public static function productos_relacionados($producto_actual_id, $marca_id){
    $productos = '';
    $sql = \Drupal::database()->select('commerce_product__field_proveedor','p');
    $sql->fields('p',array('entity_id'));
    $sql->condition('p.entity_id',$producto_actual_id,'!=');
    $sql->condition('p.field_proveedor_target_id',$marca_id,'=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      //$producto = Product::load($fil['entity_id']);
      //dpm($producto);
      //$datos['id'] = $fil['entity_id'];
     // $name = $term->name->value;
      //$name = str_replace(' ', '_', $name);
      $productos .= $fil['entity_id'].'+';
    }
    return $productos;
  }

  public static function editar_cesta($product_id, $order_id){
    $session = \Drupal::request()->getSession();
    $session->set('order_id', $order_id);
    $current_store = \Drupal::service('commerce_store.current_store');
    $store = $current_store->getStore();
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $cart = $cart_provider->getCart("default", $store);
    foreach($cart->getItems() as $order_item){
      $order = $order_item->get('order_item_id')->value;
      if($order == $order_id){
        if(isset($order_item->getPurchasedEntity()->get('attribute_color')->target_id)){
          $color = $order_item->getPurchasedEntity()->get('attribute_color')->target_id;
          $session->set('color_linea_pedido', $color);
        }

        if(isset($order_item->getPurchasedEntity()->attribute_talla)){
          $talla = $order_item->getPurchasedEntity()->get('attribute_talla')->target_id;
          $session->set('talla_linea_pedido', $talla);
        }

      }
    }

    $url = Url::fromUri('internal:/product/'.$product_id); // choose a path
    $destination = $url->toString();
    $response = new RedirectResponse($destination, 301);
    return $response->send();
  }

  /**
   * Eliminar item del pedido.
   *
   * @param $order_id
   * @param $order_item_id
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public static function deleteOrderItem($order_id, $order_item_id) {

    try {
      $order = Order::load($order_item_id);
      $order_item = OrderItem::load($order_id);

      $order->removeItem($order_item);
      $order_item->delete();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('pedido')->error($e->getMessage());
    }
    return new RedirectResponse('/checkout/' . $order_id . '/order_information');
  }

  public static function productos_destacados(){
    $productos=[];
    $sql = \Drupal::database()->select('commerce_product__field_destacar_en_sostenibles','s');
    $sql->fields('s',array('entity_id'));
    $sql->condition('s.field_destacar_en_sostenibles_value',1,'=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $entity_type = 'commerce_product';
      $entity_id = $fil['entity_id'];
      $view_mode = 'token';
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
      $pre_render = $view_builder->view($entity, $view_mode);
      $productos[] = $pre_render;

    }
    return [
      '#theme' => 'productos',
      '#content' => $productos
    ];
  }
  public static function productos_favoritos(){
    $productos=[];
    $sql = \Drupal::database()->select('commerce_product__field_destacar_en_fav','f');
    $sql->fields('f',array('entity_id'));
    $sql->condition('f.field_destacar_en_fav_value',1,'=');
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $entity_type = 'commerce_product';
      $entity_id = $fil['entity_id'];
      $view_mode = 'token';
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
      $pre_render = $view_builder->view($entity, $view_mode);
      $productos[] = $pre_render;

    }
    return [
      '#theme' => 'productos_favoritos',
      '#content' => $productos
    ];
  }

}

