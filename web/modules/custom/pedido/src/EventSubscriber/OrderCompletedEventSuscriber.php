<?php


namespace Drupal\pedido\EventSubscriber;


use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\email\Entity\Mail;
use Drupal\node\Entity\Node;
use Drupal\pedido\Controller\OrderController;
use Drupal\profile\Entity\Profile;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\user\Entity\User;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderCompletedEventSuscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * Constructs a new OrderFulfillmentSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    MailManagerInterface $mail_manager
  ) {
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
  }

  public static function getSubscribedEvents(): array {
    return [
      'commerce_order.place.post_transition' => ['orderCompleted', 200],
      'commerce_order.fulfill.post_transition' => ['orderSent', 0],
    ];
  }

  /**
   * Pedido completado.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function orderCompleted(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getEntity();
    $this->sendEmailCompleted($order);
    $this->updateStock($order);
    try {
      $this->setBillingProfile($order);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('pedido')->error($e->getMessage());
    }
    $this->setUserInformation($order);
  }

  /**
   * Actualizar datos del usuario.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   */
  public function setUserInformation(Order $order) {
    $profiles = $order->collectProfiles();

    if (isset($profiles['shipping']) && $profiles['shipping'] instanceof Profile && $order->getCustomerId()) {
      /** @var User $user */
      $user = User::load($order->getCustomerId());
      $user->set('field_nombre', $profiles['shipping']->get('address')->given_name);
      $user->set('field_apellidos', $profiles['shipping']->get('address')->family_name);
      $user->set('field_telefono', $profiles['shipping']->get('field_telefono')->value);
      try {
        $user->save();
      }
      catch (EntityStorageException $e) {
        \Drupal::logger('pedido')->error($e->getMessage());
      }
    }

  }

  /**
   * Guardar perfil de facturación.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setBillingProfile(Order $order) {
    $billing_profile = $order->getBillingProfile();
    if ($billing_profile instanceof Profile && $order->getCustomerId()) {
      $address = $billing_profile->get('address')->getValue()[0];
      $dni = $billing_profile->get('field_dni')->value;
      $phone = $billing_profile->get('field_telefono')->value;

      $storage = \Drupal::entityTypeManager()->getStorage('profile');
      $user = User::load($order->getCustomerId());
      $profiles = $storage->loadMultipleByUser($user, 'factura');

      if (!empty($profiles)) {
        /** @var Profile $profile */
        $profile = current($profiles);
        $profile->set('address', $address);
        $profile->set('field_dni', $dni);
        $profile->set('field_telefono', $phone);
      }
      else {
        $profile = Profile::create([
          'type' => 'factura',
          'uid' => $order->getCustomerId(),
          'address' => $address,
          'field_dni' => $dni,
          'field_telefono' => $phone,
        ]);
      }

      try {
        $profile->save();
      }
      catch (EntityStorageException $e) {
        \Drupal::logger('pedido')->error($e->getMessage());
      }

    }
  }

  /**
   * Obtener gastos de envio por proveedor.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *
   * @return array
   */
  private function getShippingProvider(Order $order): array {

    $gastos_proveedores = [];

    $profile = Profile::load($order->get('shipments')->target_id);

    if ($profile instanceof Profile) {
      $administrative_area = $profile->get('address')->administrative_area;

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

      foreach ($order->getItems() as $item) {
        /** @var OrderItem $line */
        $line = $item;
        if ($line->get('field_proveedor')->target_id) {
          $usuario = User::load($line->get('field_proveedor')->target_id);
          if ($usuario instanceof User) {
            if ($usuario->get($field_costes)->value) {
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
                  'free' => $usuario->get('field_total_pedido_envio_gratis_')->value ? $usuario->get('field_total_pedido_envio_gratis_')->value : 1000000,
                  'total' => $total,
                ];
              }
            }
          }
        }
      }

      foreach ($proveedores as $uid => &$proveedor) {
        $gastos_proveedores[$uid] = $proveedor['total'] >= $proveedor['free'] ? '0.00' : $proveedor['amount'];
      }
    }

    return $gastos_proveedores;

  }

  /**
   * Enviar mail pedido completado.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   */
  public function sendEmailCompleted(Order $order) {

    $to = $order->getEmail();

    $gatos_envio = 0;
    $gastos_envio_proveedor = $this->getShippingProvider($order);
    foreach ($order->getAdjustments() as $adjustment) {
      $price = $adjustment->getAmount();
      $price = $price->toArray();
      $gatos_envio += $price['number'];
    }

    $profiles = $order->collectProfiles();

    $resumen = [
      '#theme' => 'correo_resumen_pedido',
      '#order_entity' => $order,
      '#gastos_envio' => $gatos_envio
    ];

    $resumen = \Drupal::service('renderer')->render($resumen);

    $envio = '';
    if (isset($profiles['shipping']) && $profiles['shipping'] instanceof Profile) {
      $envio = [
        '#theme' => 'correo_informacion_envio',
        '#profile' => $profiles['shipping'],
      ];
      $envio = \Drupal::service('renderer')->render($envio);
    }

    if ($customer = $order->getCustomer()) {
      $langcode = $customer->getPreferredLangcode();
    }
    else {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();
    }

    // Correo del cliente
    $mail = Mail::load(Mail::TYPE_CONFIRM_ORDER);
    $orderController = new OrderController($order);
    if ($orderController->checkMultiProvider()) {
      $mail = Mail::load(Mail::TYPE_CONFIRM_ORDER_MULTIPLE);
    }

    if ($mail instanceof Mail) {
      $subject = $mail->getSubject();
      $body = $mail->getBody();

      $token_service = \Drupal::token();
      $subject = $token_service->replace($subject, [
        'commerce_order' => $order
      ]);
      $body = $token_service->replace($body, [
        'commerce_order' => $order
      ]);

      $body = str_replace('[resumen]', $resumen, $body);
      $body = str_replace('[datos_envio]', $envio, $body);

      $params = [
        'from' => $order->getStore()->getEmail(),
        'subject' => $subject,
        'body' => ['#markup' => Markup::create($body)],
      ];


      $this->mailManager->mail('commerce', 'receipt', $to, $langcode, $params);

      \Drupal::logger('correo')->info('Pedido #' . $order->id() . ' completado enviado a ' . $to);
    }

    // Correo del propietario
    $mail = Mail::load(Mail::TYPE_CONFIRM_ORDER_STORE);
    if ($mail instanceof Mail) {
      $subject = $mail->getSubject();
      $body = $mail->getBody();

      $token_service = \Drupal::token();
      $subject = $token_service->replace($subject, [
        'commerce_order' => $order
      ]);
      $body = $token_service->replace($body, [
        'commerce_order' => $order
      ]);

      $body = str_replace('[resumen]', $resumen, $body);
      $body = str_replace('[datos_envio]', $envio, $body);

      $params = [
        'from' => $order->getStore()->getEmail(),
        'subject' => $subject,
        'body' => ['#markup' => Markup::create($body)],
      ];

      $query = \Drupal::entityQuery('user');
      $query->condition('roles', ['propietario'], 'IN');
      $result = $query->execute();
      foreach ($result as $id) {
        /** @var User $user */
        $user = User::load($id);
        $to = $user->getEmail();
        $this->mailManager->mail('commerce', 'admin_order_completed', $user->getEmail(), $langcode, $params);
        \Drupal::logger('correo')->info('Pedido #' . $order->id() . ' completado enviado al propietario ' . $to);
      }
    }

    // Correo del proveedor
    $mail = Mail::load(Mail::TYPE_CONFIRM_ORDER_PROVIDER);
    if ($mail instanceof Mail) {
      $subject = $mail->getSubject();
      $body = $mail->getBody();

      $token_service = \Drupal::token();
      $subject = $token_service->replace($subject, [
        'commerce_order' => $order
      ]);
      $body = $token_service->replace($body, [
        'commerce_order' => $order
      ]);
      $body = str_replace('[datos_envio]', $envio, $body);

      $params = [
        'from' => $order->getStore()->getEmail(),
        'subject' => $subject,
        'body' => ['#markup' => Markup::create($body)],
      ];

      $proveedores = [];

      foreach ($order->getItems() as $item) {
        /** @var OrderItem $line */
        $line = $item;
        if ($line->get('field_proveedor')->target_id) {
          $usuario = User::load($line->get('field_proveedor')->target_id);
          if ($usuario instanceof User) {
            $total = $line->getTotalPrice();
            $price = $total->toArray();
            if (!isset($proveedores[$usuario->id()])) {
              $proveedores[$usuario->id()]['user'] = $usuario;
              $proveedores[$usuario->id()]['total'] = $price['number'];
            }
            else {
              $proveedores[$usuario->id()]['total'] += $price['number'];
            }
            $proveedores[$usuario->id()]['lines'][] = $line;
          }
        }
      }

      foreach ($proveedores as $uid => $proveedor) {

        $resumen = [
          '#theme' => 'correo_resumen_pedido_proveedor',
          '#order_items' => $proveedor['lines'],
          '#total' => $proveedor['total']
        ];

        if (isset($gastos_envio_proveedor[$uid])) {
          $resumen['#total'] += $gastos_envio_proveedor[$uid];
          $resumen['#gastos_envio'] = $gastos_envio_proveedor[$uid];
        }

        $resumen = \Drupal::service('renderer')->render($resumen);
        $body_proveedor = str_replace('[resumen]', $resumen, $body);
        $body_proveedor = $token_service->replace($body_proveedor, [
          'user' => $proveedor['user']
        ]);
        $params['body'] =  ['#markup' => Markup::create($body_proveedor)];
        $to = $proveedor['user']->getEmail();
        $this->mailManager->mail('commerce', 'proveedor_order_completed', $proveedor['user']->getEmail(), $langcode, $params);
        \Drupal::logger('correo')->info('Pedido #' . $order->id() . ' completado enviado al proveedor ' . $to);
      }
    }

  }

  /**
   * Actualizar Stock.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   */
  public function updateStock(Order $order) {

    foreach ($order->getItems() as $key => $order_item) {
      /** @var \Drupal\commerce_order\Entity\OrderItem $item */
      $item = $order_item;
      $cantidad = $item->getQuantity();
      if ($product_variation = $item->getPurchasedEntity()) {
        if ($product_variation instanceof ProductVariation && $cantidad) {
          $stock = $product_variation->get('field_stock')->value ? $product_variation->get('field_stock')->value : 0;
          $stock -= $cantidad;
          if ($stock < 0) {
            $stock = 0;
          }
          $product_variation->set('field_stock', $stock);
          try {
            $product_variation->save();
            \Drupal::logger('pedido')->info($product_variation->getTitle() . ' nuevo stock ' . $stock);
          }
          catch (EntityStorageException $e) {
            \Drupal::logger('pedido')->error($e->getMessage());
          }
        }
      }
    }
  }

  /**
   * Pedido enviado.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function orderSent(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getEntity();
  }

  /**
   * Crear cliente en Stripe para usuarios anónimos.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   * @param \Drupal\user\Entity\User $user
   */
  public function addCustomerStripe(Order $order, User $user) {
    $commerce_payment = NULL;
    try {
      $commerce_payment = \Drupal::entityTypeManager()->getStorage('commerce_payment')
        ->loadByProperties(['order_id' => $order->id()]);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('pedido')->error($e->getMessage());
    }

    if ($commerce_payment) {
      $commerce_payment = reset($commerce_payment);
    }

    if ($commerce_payment instanceof Payment) {
      $remote_id = $commerce_payment->getRemoteId();

      if ($remote_id) {
        $commerce_payment_gateway = NULL;
        try {
          $commerce_payment_gateway = \Drupal::entityTypeManager()->getStorage('commerce_payment_gateway')
            ->loadByProperties(['plugin' => 'stripe']);
        }
        catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
          \Drupal::logger('pedido')->error($e->getMessage());
        }
        $config = NULL;
        if ($commerce_payment_gateway) {
          $commerce_payment_gateway = reset($commerce_payment_gateway);
          $plugin = $commerce_payment_gateway->getPlugin();
          if ($plugin->getPluginId() == 'stripe') {
            $config = $plugin->getConfiguration();
          }
        }

        if ($config) {
          $provider = 'stripe|' . $config['mode'];
          \Stripe\Stripe::setApiKey($config['secret_key']);
          $customer = NULL;
          if ($order->get('field_customer_id')->value) {
            try {
              Customer::update($order->get('field_customer_id')->value, ['email' => $order->getEmail()]);
            }
            catch (ApiErrorException $e) {
              \Drupal::logger('pedido')->error($e->getMessage());
            }
            $user->set('commerce_remote_id', ['provider' => $provider, 'remote_id' => $order->get('field_customer_id')->value]);
            try {
              $user->save();
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
