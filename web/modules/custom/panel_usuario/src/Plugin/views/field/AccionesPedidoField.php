<?php
namespace Drupal\panel_usuario\Plugin\views\field;
use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("acciones_pedido_field")
 */
class AccionesPedidoField extends FieldPluginBase {
  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->currentDisplay = $view->current_display;
  }
  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }
  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }
  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // First check whether the field should be hidden if the value(hide_alter_empty = TRUE) /the rewrite is empty (hide_alter_empty = FALSE).
    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }
  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $productos = [];
    $order = \Drupal\commerce_order\Entity\Order::load($values->order_id);
    if($order instanceof \Drupal\commerce_order\Entity\Order){
      $factura = ['enlace' => '', 'titulo' => 'Factura aún no disponible'];
      if(isset($order->field_factura->entity)){
        $uri = $order->field_factura->entity->getFileUri();
      }
      if(isset($uri)){
        $url = Url::fromUri(file_create_url($uri));
        $factura = ['enlace' => $url, 'titulo' => 'Factura'];
      }
      /*foreach ($order->getItems() as $key => $order_item) {
        $options = ['absolute' => TRUE];
        $estadoDevolver = $order_item->field_estado->value;
        switch ($estadoDevolver) {
          case "":
          case 'devolucion':
            $fecha = $order->getCompletedTime() != null ? $order->getCompletedTime() : $order->getCreatedTime();
            $fechaDevolucion = strtotime ( '+15 days' , $fecha ) ;
            if($fechaDevolucion >= time()){
              $url = "/mi-cuenta/devolver/" . $order_item->order_item_id->value;
              $devolver = [
                'enlace' => $url,
                'titulo' => 'Devolver',
                'class' => 'devolver'
              ];
            }
            break;
          case "solicitado":
            $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => 24], $options);
            $url = $url->toString();
            $devolver = [
              'enlace' => $url,
              'titulo' => 'Solicitado',
              'class' => 'solicitado'
            ];
            break;
          case "validando":
            $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => 18], $options);
            $url = $url->toString();
            $devolver = [
              'enlace' => $url,
              'titulo' => 'Validando',
              'class' => 'validando'
            ];
            break;
          case "recogida":
            $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => 17], $options);
            $url = $url->toString().'?tipo_envio='.$order_item->get('field_tipo_envio')->value;
            $devolver = [
              'enlace' => $url,
              'titulo' => 'Recogida',
              'class' => 'recogida'
            ];
            break;
          case "devuelto":
            if($order_item->get('field_tipo_devolucion')->value == 'cambio'){
              $fecha = $order->getCompletedTime() != null ? $order->getCompletedTime() : $order->getCreatedTime();
              $fechaDevolucion = strtotime ( '+15 days' , $fecha ) ;
              if($fechaDevolucion >= time()){
                $url = "/mi-cuenta/devolver/" . $order_item->order_item_id->value."?devolucion=1";
                $devolver = [
                  'enlace' => $url,
                  'titulo' => 'Devolver',
                  'class' => 'devolver'
                ];
              }else{
                $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => 25], $options);
                $url = $url->toString();
                $devolver = [
                  'enlace' => $url,
                  'titulo' => 'Devuelto',
                  'class' => 'devuelto'
                ];
              }
            }else{
              $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => 25], $options);
              $url = $url->toString();
              $devolver = [
                'enlace' => $url,
                'titulo' => 'Devuelto',
                'class' => 'devuelto'
              ];
            }
            break;
        }
        if (isset($devolver)) {
          $productos[] = $devolver;
        }
      }*/
    }
    $theme = [
      '#theme' => 'acciones_pedido',
      '#linkFactura' => $factura,
      //'#linkProductos' => $productos,
      '#cache' => ['max-age' => 0],
    ];
    return ['#markup' => \Drupal::service('renderer')->render($theme)];
  }
}
