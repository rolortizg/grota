<?php


namespace Drupal\email\Controller;


use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\email\Entity\Mail;

class MailViewController extends EntityViewController {

  /**
   * Titulo de las páginas de administración de correos.
   *
   * @param \Drupal\email\Entity\Mail|null $mail
   *
   * @return string
   */
  public function title(Mail $mail = NULL) {
    $title = 'Añadir correo';
    if ($mail) {
      $types = Mail::getTypes();
      $title = 'Editar correo de ' . $types[$mail->get('type')->value] ;
    }
    return $title;
  }

}
