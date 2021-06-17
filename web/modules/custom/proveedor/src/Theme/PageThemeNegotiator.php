<?php


namespace Drupal\proveedor\Theme;


use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class PageThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * The proxy to the current user.
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
   * @inheritDoc
   */
  public function applies(RouteMatchInterface $route_match): bool {
    $apply = FALSE;

    $user = User::load($this->account->id());
    if ($user instanceof User) {
      if ($user->hasRole('proveedor')) {
        if (preg_match("/backend*/i", $this->request->getRequestUri())) {
          $apply = TRUE;
        }
      }
    }

    return $apply;
  }

  /**
   * @inheritDoc
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'gin';
  }
}
