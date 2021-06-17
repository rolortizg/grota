<?php


namespace Drupal\pedido\EventSubscriber;


use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\email\Entity\Mail;
use Drupal\pedido\Controller\OrderController;
use Drupal\pedido\Event\OrderItemStateEvent;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderItemEventSuscriber implements EventSubscriberInterface {

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
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $account;


  /**
   * OrderItemEventSuscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   */
  public function __construct(AccountProxyInterface $account, LanguageManagerInterface $language_manager, MailManagerInterface $mail_manager) {
    $this->account = $account;
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
  }

  /**
   * Comprobar si el pedido esta completado por el proveedor.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $commerce_order
   *
   * @return bool
   */
  private function checkOrderSent(OrderInterface $commerce_order): bool {
    $order = new OrderController($commerce_order);
    /** @var User $proveedor */
    $proveedor = User::load($this->account->id());
    $enviado = TRUE;
    foreach ($order->getOrderItemsProvider($proveedor) as $line) {
      if ($line->get('field_estado')->value != 'enviado') {
        $enviado = FALSE;
        break;
      }
    }
    return $enviado;
  }

  private function checkOrderCompleted(OrderInterface $order) {
    $enviado = TRUE;
    foreach ($order->getItems() as $item) {
      if ($item->get('field_estado')->value != 'enviado') {
        $enviado = FALSE;
        break;
      }
    }

    if ($enviado) {
      $order->set('state', 'completed');
      try {
        $order->save();
      }
      catch (EntityStorageException $e) {
        \Drupal::logger('pedido')->error($e->getMessage());
      }
    }
  }

  /**
   * Enviar correo al cliente.
   *
   * @param \Drupal\pedido\Event\OrderItemStateEvent $event
   */
  public function onSendMail(OrderItemStateEvent $event) {
    $line = $event->getOrderItem();
    if ($line->get('field_estado')->value == 'enviado') {
      $order = $line->getOrder();
      if ($this->checkOrderSent($order)) {
        $commerce_order = new OrderController($order);
        /** @var User $proveedor */
        $proveedor = User::load($this->account->id());
        $lines = $commerce_order->getOrderItemsProvider($proveedor);

        $mail = Mail::load(Mail::TYPE_SENT_ORDER_PROVIDER);
        if ($mail instanceof Mail) {
          $subject = $mail->getSubject();
          $body = $mail->getBody();

          if ($customer = $order->getCustomer()) {
            $langcode = $customer->getPreferredLangcode();
          }
          else {
            $langcode = $this->languageManager->getDefaultLanguage()->getId();
          }

          $to = $order->getEmail();

          $profiles = $order->collectProfiles();
          $envio = '';
          if (isset($profiles['shipping']) && $profiles['shipping'] instanceof Profile) {
            $envio = [
              '#theme' => 'correo_informacion_envio',
              '#profile' => $profiles['shipping'],
            ];
            $envio = \Drupal::service('renderer')->render($envio);
          }

          $token_service = \Drupal::token();
          $subject = $token_service->replace($subject, [
            'commerce_order' => $order
          ]);
          $body = $token_service->replace($body, [
            'commerce_order' => $order
          ]);
          $body = str_replace('[datos_envio]', $envio, $body);

          $resumen = [
            '#theme' => 'correo_resumen_pedido_proveedor',
            '#order_items' => $lines,
          ];
          $resumen = \Drupal::service('renderer')->render($resumen);
          $body = str_replace('[resumen]', $resumen, $body);
          $params = [
            'from' => $order->getStore()->getEmail(),
            'subject' => $subject,
            'body' => ['#markup' => Markup::create($body)],
          ];

          $this->mailManager->mail('commerce', 'receipt', $to, $langcode, $params);

          \Drupal::logger('correo')->info('Pedido #' . $order->id() . ' enviado a ' . $to);

        }
      }
      $this->checkOrderCompleted($order);
    }
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    $events[OrderItemStateEvent::UPDATE_STATE][] = ['onSendMail', 0];
    return $events;
  }

}
