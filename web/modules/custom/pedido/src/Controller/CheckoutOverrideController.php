<?php

namespace Drupal\pedido\Controller;

use Drupal\commerce_cart\CartSession;
use Drupal\commerce_cart\CartSessionInterface;
use Drupal\commerce_checkout\CheckoutOrderManagerInterface;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides the checkout form page.
 */
class CheckoutOverrideController implements ContainerInjectionInterface {

  use DependencySerializationTrait;

  /**
   * The checkout order manager.
   *
   * @var \Drupal\commerce_checkout\CheckoutOrderManagerInterface
   */
  protected $checkoutOrderManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The cart session.
   *
   * @var \Drupal\commerce_cart\CartSessionInterface
   */
  protected $cartSession;

  /**
   * Constructs a new CheckoutController object.
   *
   * @param \Drupal\commerce_checkout\CheckoutOrderManagerInterface $checkout_order_manager
   *   The checkout order manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\commerce_cart\CartSessionInterface $cart_session
   *   The cart session.
   */
  public function __construct(CheckoutOrderManagerInterface $checkout_order_manager, FormBuilderInterface $form_builder, CartSessionInterface $cart_session) {
    $this->checkoutOrderManager = $checkout_order_manager;
    $this->formBuilder = $form_builder;
    $this->cartSession = $cart_session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_checkout.checkout_order_manager'),
      $container->get('form_builder'),
      $container->get('commerce_cart.cart_session')
    );
  }

  /**
   * Builds and processes the form provided by the order's checkout flow.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The render form.
   */
  public function formPage(RouteMatchInterface $route_match) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');
    $requested_step_id = $route_match->getParameter('step');
    $step_id = $this->checkoutOrderManager->getCheckoutStepId($order, $requested_step_id);
    if ($requested_step_id != $step_id) {
      $url = Url::fromRoute('commerce_checkout.form', [
        'commerce_order' => $order->id(),
        'step' => $step_id,
      ]);
      return new RedirectResponse($url->toString());
    }
    $checkout_flow = $this->checkoutOrderManager->getCheckoutFlow($order);
    $checkout_flow_plugin = $checkout_flow->getPlugin();

    if ($step_id == 'complete' && $order->get('shipments')->target_id) {

      $profile = Profile::load($order->get('shipments')->target_id);

      if ($profile instanceof Profile) {
        foreach ($order->getAdjustments([
          'custom',
          'shipping',
        ]) as $adjustments) {
          $order->removeAdjustment($adjustments);
        }

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

        $proveedores = [];
        foreach ($order->getItems() as $item) {
          /** @var OrderItem $line */
          $line = $item;
          if ($line->get('field_proveedor')->target_id) {
            $usuario = User::load($line->get('field_proveedor')->target_id);
            if ($usuario instanceof User) {
              if ($usuario->get($field_costes)->value) {
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

        foreach ($proveedores as $uid => $proveedor) {
          if ($proveedor['total'] >= $proveedor['free']) continue;
          $price = new Price($proveedor['amount'], $total->getCurrencyCode());
          $adjustment = new Adjustment([
            'type' => 'shipping',
            'label' => 'Gastos de envio (' . $proveedor['name'] . ')',
            'amount' => $price,
            'source_id' => $uid
          ]);

          $order->addAdjustment($adjustment);
        }

        try {
          $order->save();
        }
        catch (EntityStorageException $e) {
          \Drupal::logger('pedido')->error($e->getMessage());
        }


      }
    }

    return $this->formBuilder->getForm($checkout_flow_plugin, $step_id);
  }

  /**
   * Checks access for the form page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');
    if ($order->getState()->getId() == 'canceled') {
      return AccessResult::forbidden()->addCacheableDependency($order);
    }

    // The user can checkout only their own non-empty orders.
    if ($account->isAuthenticated()) {
      $customer_check = $account->id() == $order->getCustomerId();
    }
    else {
      $active_cart = $this->cartSession->hasCartId($order->id(), CartSession::ACTIVE);
      $completed_cart = $this->cartSession->hasCartId($order->id(), CartSession::COMPLETED);
      $customer_check = $active_cart || $completed_cart;
    }

    $access = AccessResult::allowedIf($customer_check)
      ->andIf(AccessResult::allowedIf($order->hasItems()))
      ->andIf(AccessResult::allowedIfHasPermission($account, 'access checkout'))
      ->addCacheableDependency($order);

    return $access;
  }

}
