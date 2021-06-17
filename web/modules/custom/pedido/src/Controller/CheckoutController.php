<?php


namespace Drupal\pedido\Controller;


use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

class CheckoutController extends ControllerBase {

  /**
   * Ajax gastos de envio.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public static function ajaxRefreshForm(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $element = NULL;
    if (!empty($triggering_element['#ajax']['element'])) {
      $element = NestedArray::getValue($form, $triggering_element['#ajax']['element']);
    }
    // Element not specified or not found. Show messages on top of the form.
    if (!$element) {
      $element = $form;
    }

    /** @var Order $order */
    $order = \Drupal::routeMatch()->getParameter('commerce_order');
    foreach ($order->getAdjustments(['custom', 'shipping']) as $adjustments) {
      $order->removeAdjustment($adjustments);
    }

    $values = $form_state->getValues();

    $administrative_area = NULL;
    $shipping_profile = $form_state->get('shipping_profile');
    if ($shipping_profile instanceof Profile) {
      $administrative_area = $shipping_profile->get('address')->administrative_area;
    }
    elseif(isset($values['shipping_information']['shipping_profile']['select_address'])) {
      $profile = Profile::load($values['shipping_information']['shipping_profile']['select_address']);
      if ($profile instanceof Profile) {
        $administrative_area = $profile->get('address')->administrative_area;
      }
    }

    if ($administrative_area) {
      switch ($administrative_area) {
        case 'Las Palmas':
        case 'Santa Cruz de Tenerife':
          $field_costes = 'field_coste_envio_islas_';
          break;
        case 'Balears':
          $field_costes = 'field_coste_envio_islas_baleares';
          break;
        case 'Ceuta':
        case 'Melilla':
          $field_costes = 'field_coste_envio_ceuta_y_melill';
          break;
        default:
          $field_costes = 'field_coste_de_envio';
      }

      $proveedores = [];
      foreach ($order->getItems() as $item) {
        /** @var OrderItem $line */
        $line = $item;
        if ($line->get('field_proveedor')->target_id) {
          $usuario = User::load($line->get('field_proveedor')->target_id);
          if ($usuario instanceof User) {
            if ($usuario->get($field_costes)->value ) {
              $query = \Drupal::entityQuery('node');
              $query->condition('field_proveedor', $usuario->id());
              $result = $query->execute();
              $titulo = NULL;
              foreach ($result as $id) {
                $nodo = Node::load($id);
                if ($nodo instanceof Node) {
                  $titulo = $nodo->label();
                }
              }

              if (!$titulo) {
                if ($usuario->get('field_nombre')->value) {
                  $titulo = $usuario->get('field_nombre')->value;
                }
                else {
                  $titulo = $usuario->getDisplayName();
                }
              }

              /** @var Price $price */
              $price = $line->getTotalPrice();
              $total = $price->toArray();
              $total = $total['number'];

              if (isset($proveedores[$usuario->id()])) {
                $proveedores[$usuario->id()]['total'] += $total;
              }
              else {
                $proveedores[$usuario->id()] = [
                  'amount' => $usuario->get($field_costes)->value,
                  'name' => $titulo,
                  'free' => $usuario->get('field_total_pedido_envio_gratis_')->value ? $usuario->get('field_total_pedido_envio_gratis_')->value : 1000000,
                  'total' => $total,
                ];
              }
            }
          }
        }
      }

      /** @var Price $total */
      $total = $order->getTotalPrice();

      $amount = 0;
      foreach ($proveedores as $proveedor) {
        if ($proveedor['total'] >= $proveedor['free']) continue;
        $amount += $proveedor['amount'];
      }

      if ($amount) {
        $price = new Price($amount, $total->getCurrencyCode());
        $adjustment = new Adjustment(['type' => 'custom', 'label' => 'Gastos de envio', 'amount' => $price]);
        $order->addAdjustment($adjustment);
      }
    }

    try {
      $order->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('pedido')->error($e->getMessage());
    }

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('[data-drupal-selector="' . $form['#attributes']['data-drupal-selector'] . '"]', $form));
    $response->addCommand(new PrependCommand('[data-drupal-selector="' . $element['#attributes']['data-drupal-selector'] . '"]', ['#type' => 'status_messages']));

    return $response;
  }

  public static function _address_field($element, $form_state) {

    \Drupal::state()->set('aux', $element);

    $nombre = '';
    $apellidos = '';
    if (\Drupal::currentUser()->id()) {
      /** @var \Drupal\user\Entity\User $user */
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      $nombre = $user->get('field_nombre')->value;
      $apellidos = $user->get('field_apellidos')->value;
      if (!isset($element['given_name']['#value']) || $element['given_name']['#value'] == '') {
        $element['given_name']['#value'] = $nombre;
      }

      if (!isset($element['family_name']['#value']) || $element['family_name']['#value'] == '') {
        $element['family_name']['#value'] = $apellidos;
      }
    }



    $element['organization']["#title_display"] = 'none';
    $element['organization']["#attributes"]['placeholder'] = 'Empresa';
    $element['organization']["#attributes"]['data-toggle'] = 'tooltip';
    $element['organization']["#attributes"]['title'] = $element['organization']["#attributes"]['placeholder'];
    $element['given_name']["#title_display"] = 'none';
    $element['given_name']["#attributes"]['placeholder'] = 'Nombre';
    $element['given_name']["#attributes"]['data-toggle'] = 'tooltip';
    $element['given_name']["#attributes"]['title'] = $element['given_name']["#attributes"]['placeholder'];
    $element['family_name']["#title_display"] = 'none';
    $element['family_name']["#attributes"]['placeholder'] = 'Apellidos';
    $element['family_name']["#attributes"]['data-toggle'] = 'tooltip';
    $element['family_name']["#attributes"]['title'] = $element['family_name']["#attributes"]['placeholder'];
    $element['address_line1']["#title_display"] = 'none';
    $element['address_line1']["#attributes"]['placeholder'] = 'Calle y número';
    $element['address_line1']["#attributes"]['data-toggle'] = 'tooltip';
    $element['address_line1']["#attributes"]['title'] = 'Calle y número';
    $element['address_line2']["#title_display"] = 'none';
    $element['address_line2']["#attributes"]['placeholder'] = 'Escalera y piso';
    $element['address_line2']["#attributes"]['data-toggle'] = 'tooltip';
    $element['address_line2']["#attributes"]['title'] = 'Escalera y piso';
    $element['postal_code']["#title_display"] = 'none';
    $element['postal_code']["#attributes"]['placeholder'] = 'Código postal';
    $element['postal_code']["#attributes"]['data-toggle'] = 'tooltip';
    $element['postal_code']["#attributes"]['title'] = $element['postal_code']["#attributes"]['placeholder'];
    $element['locality']["#title_display"] = 'none';
    $element['locality']["#attributes"]['placeholder'] = 'Ciudad';
    $element['locality']["#attributes"]['data-toggle'] = 'tooltip';
    $element['locality']["#attributes"]['title'] = $element['locality']["#attributes"]['placeholder'];
    $element['administrative_area']["#title_display"] = 'none';
    $element['administrative_area']["#options"][''] = '- Provincia -';
    $element['administrative_area']["#attributes"]['data-toggle'] = 'tooltip';
    $element['administrative_area']["#attributes"]['title'] ='Provincia';

    if (isset($element['#name']) && $element['#name'] == 'order_fields:checkout[billing_profile][0][profile][address][0][address]') {
      $billing_profile = $form_state->get('billing_profile_default');
      if ($billing_profile instanceof Profile) {
        $element['organization']['#value'] = $billing_profile->get('address')->organization;
        $element['given_name']['#value'] = $billing_profile->get('address')->given_name;
        $element['family_name']['#value'] = $billing_profile->get('address')->family_name;
        $element['address_line1']['#value'] = $billing_profile->get('address')->address_line1;
        $element['address_line1']['#value'] = $billing_profile->get('address')->address_line1;
        $element['address_line2']['#value'] = $billing_profile->get('address')->address_line2;
        $element['postal_code']['#value'] = $billing_profile->get('address')->postal_code;
        $element['locality']['#value'] = $billing_profile->get('address')->locality;
        $element['administrative_area']['#value'] = $billing_profile->get('address')->administrative_area;
      }
    }

    return $element;
  }

  /**
   * Comprobar stock de los artículos del pedido
   * @param \Drupal\commerce_order\Entity\Order $order
   *
   * @return \Drupal\Component\Render\MarkupInterface|false|string
   */
  private function checkStock(Order $order) {
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
              $error = Markup::create($error);
            }
          }
        }
      }
    }
    return $error;
  }

  /**
   * Validar stock.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function stockValidate(array &$form, FormStateInterface $form_state) {
    $object = $form_state->getformObject();
    if (method_exists($object, 'getOrder')) {
      /** @var Order $order */
      $order = $object->getOrder();
      if ($error = (new CheckoutController())->checkStock($order)) {
        $form_state->setError($form, $error);
      }
    }
  }

  /**
   * Submit form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    $billing_profile = NULL;
    if (isset($form['order_fields:checkout']['billing_profile']['widget'][0]['profile']["#inline_form"])) {
      /** @var \Drupal\commerce\Plugin\Commerce\InlineForm\EntityInlineFormInterface $inline_form */
      $inline_form = $form['order_fields:checkout']['billing_profile']['widget'][0]['profile']["#inline_form"];
      $billing_profile = $inline_form->getEntity();
    }

    if(isset($values['order_fields:checkout']['billing_profile'][0]['profile']['copy_fields']['enable']) && $values['order_fields:checkout']['billing_profile'][0]['profile']['copy_fields']['enable'] == 0) {
      if (!isset($values['order_fields:checkout']['billing_profile'][0]['profile']['address'])) {
        $storage = \Drupal::entityTypeManager()->getStorage('profile');
        $user = User::load(\Drupal::currentUser()->id());
        $profiles = $storage->loadMultipleByUser($user, 'factura');
        if (!empty($profiles)) {
          $billing_profile = current($profiles);
        }
      }
    }

    $object = $form_state->getformObject();
    if (method_exists($object, 'getOrder')) {
      /** @var Order $order */
      $order = $object->getOrder();
      if ($billing_profile instanceof Profile) {
        $order->setBillingProfile($billing_profile);
        $order->save();
      }
    }
  }

  /**
   * Añadir perfil de facturación al usuario nuevo.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function registerSubmit(array &$form, FormStateInterface $form_state) {
    $object = $form_state->getformObject();
    if (method_exists($object, 'getOrder')) {
      /** @var Order $order */
      $order = $object->getOrder();
      if ($order instanceof Order) {
        \Drupal::service('commerce_order.order_receipt_subscriber')->setBillingProfile($order);
        \Drupal::service('commerce_order.order_receipt_subscriber')->setUserInformation($order);
      }
    }
  }
}
