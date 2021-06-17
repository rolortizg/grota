<?php


namespace Drupal\proveedor\EventSubscriber;


use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

class BackendEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface {

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

  /**
   * Redirigir a valorar.
   */
  public function onRequestFrontPage() {
    $url_object = \Drupal::service('path.validator')->getUrlIfValid($this->request->getRequestUri());
    if ($url_object) {
      $route_name = $url_object->getRouteName();
      if ($route_name == 'entity.user.canonical' && $this->account->id()) {
        /** @var User $usuario */
        $usuario = User::load($this->account->id());
        if ($usuario->hasRole('proveedor')) {
          $url = '/backend/pedidos';
          $response = new RedirectResponse($url);
          $response->send();
          exit;
        }

      }
    }
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequestFrontPage', 220];
    return $events;
  }

}
