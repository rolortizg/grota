<?php


namespace Drupal\admin_web\Controller;


use Drupal\cookies\CookiesKnockOutService;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CookieController extends ControllerBase
{

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Famous Logger Channel Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->request = $container->get('request_stack')->getCurrentRequest();
    $instance->logger = $container->get('logger.factory');
    return $instance;
  }

  public function callback() {
    $content = ($this->request->getMethod() == 'POST')
      ? json_decode($this->request->getContent(), TRUE)
      : $this->request->query->all();
    $string = '';
    $enabled = false;
    foreach ($content as $k => $v) {
      $val = ($v) ? 'true' : 'false';
      if ($val == 'true') {
        $enabled = true;
      }
      $string .= " {$k}={$val};";
    }

    $this->logger->get('cookies')->notice('User (re-)set cookie: %content', [
      '%content' => $string,
    ]);
    return new JsonResponse([
      'data' => [
        'message' => 'Thank you, Sir.',
      ],
    ]);
  }

}
