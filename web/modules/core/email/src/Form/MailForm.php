<?php


namespace Drupal\email\Form;


use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class MailForm extends ContentEntityForm {



  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\email\Entity\Mail $entity */
    $entity = $this->entity;
    $form = parent::buildForm($form, $form_state);

    if (!$entity->isNew()) {
      $form['type']['#access'] = FALSE;
    }

    $form['body']['tokens'] = $this->getTokens($entity->get('type')->value);

    $form['actions']['submit']['#value'] = 'Guardar';

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\email\Entity\Mail $entity */
    $entity = $this->entity;
    if ($entity->isNew()) {
      $values = $form_state->getValues();
      $tipo = $values['type'][0]['value'];
      $database = \Drupal::database();
      $sql = "SELECT id FROM mail_field_data where type = '" . $tipo . "'";
      $result = $database->query($sql);
      if ($result) {
        while ($row = $result->fetchAssoc()) {
          $form_state->setErrorByName('type', 'Ya existe un correo para el tipo ' . $tipo);
          break;
        }
      }
    }
  }


  /**
   * @inheritDoc
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    /** @var \Drupal\email\Entity\Mail $entity */
    $entity = $this->entity;
  }


  /**
   * Obtener tokens disponibles.
   *
   * @param $tipo
   *
   * @return string[]
   */
  private function getTokens($tipo) {

    $tokens = [
      '#theme' => 'item_list',
      '#title' => 'Tokens de reemplazo disponibles',
      '#list_type' => 'ul',
    ];

    $items = [];

    switch ($tipo) {
      case 'order_completed':
      case 'order_sent_provider':
      case 'order_completed_multiple':
        $items[] = '[commerce_order:order_number] => Número de pedido';
        $items[] = '[commerce_order:mail] => Correo del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:given_name]  => Nombre del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:family_name]  => Apellidos del cliente';
        $items[] = '[resumen] => Resumen pedido';
        $items[] = '[datos_envio] => Datos del envío';
        break;
      case 'order_completed_store':
        $items[] = '[commerce_order:order_number] => Número de pedido';
        $items[] = '[commerce_order:mail] => Correo del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:given_name]  => Nombre del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:family_name]  => Apellidos del cliente';
        $items[] = '[commerce_order:admin-url] => Url administración';
        $items[] = '[resumen] => Resumen pedido';
        $items[] = '[datos_envio] => Datos del envío';
        break;
      case 'order_completed_provider':
        $items[] = '[user:field_nombre] => Nombre del proveedor';
        $items[] = '[commerce_order:order_number] => Número de pedido';
        $items[] = '[commerce_order:mail] => Correo del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:given_name]  => Nombre del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:family_name]  => Apellidos del cliente';
        $items[] = '[commerce_order:admin-url] => Url administración';
        $items[] = '[resumen] => Resumen pedido';
        $items[] = '[datos_envio] => Datos del envío';
        break;
      default:
        $items[] = '[user:field_nombre] => Nombre del usuario';
        $items[] = '[user:field_apellidos] => Apellidos del usuario';
        $items[] = '[user:mail] => Email del usuario';
        $items[] = '[user:one-time-login-url] => Enlace de inicio de sesión único para establecer contraseña';
    }

    $items[] = '[site:name] => Nombre del sitio';
    $items[] = '[site:login-url] => Url de acceso';

    $tokens['#items'] = $items;

    return $tokens;
  }

}
