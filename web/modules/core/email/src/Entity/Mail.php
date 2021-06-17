<?php


namespace Drupal\email\Entity;


use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Entidad Mail.
 *
 * @ingroup pack
 *
 * @ContentEntityType(
 *   id = "mail",
 *   label = @Translation("Mail"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\email\Access\MailAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\email\Routing\MailHtmlRouteProvider",
 *     },
 *   "form" = {
 *       "edit" = "Drupal\email\Form\MailForm",
 *       "add" = "Drupal\email\Form\MailForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   base_table = "mails",
 *   translatable = TRUE,
 *   admin_permission = "administer mail entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode"
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/mail/{mail}/delete",
 *     "edit-form" = "/admin/config/mail/{mail}/edit",
 *     "add-form" = "/admin/config/mail/add",
 *   },
 *   field_ui_base_route = "mail.settings"
 * )
 */
class Mail extends ContentEntityBase {

  const TYPE_REGISTER = 'register';
  const TYPE_RESET_PASSWORD = 'reset_password';
  const TYPE_CONFIRM_ORDER = 'order_completed';
  const TYPE_CONFIRM_ORDER_MULTIPLE = 'order_completed_multiple';
  const TYPE_CONFIRM_ORDER_STORE = 'order_completed_store';
  const TYPE_CONFIRM_ORDER_PROVIDER = 'order_completed_provider';
  const TYPE_SENT_ORDER_PROVIDER = 'order_sent_provider';

  public static function getTypes() {
    return [
      self::TYPE_REGISTER => 'Registro',
      self::TYPE_RESET_PASSWORD => 'Recuperar contraseña',
      self::TYPE_CONFIRM_ORDER => 'Confirmar compra',
      self::TYPE_CONFIRM_ORDER_MULTIPLE => 'Confirmar compra multiple proveedor',
      self::TYPE_CONFIRM_ORDER_STORE => 'Confirmar compra tienda',
      self::TYPE_CONFIRM_ORDER_PROVIDER => 'Confirmar compra proveedor',
      self::TYPE_SENT_ORDER_PROVIDER => 'Pedido enviado por el proveedor',
    ];
  }

  /**
   * @inheritDoc
   */
  public static function load($id) {

    if (!is_numeric($id)) {
      $database = \Drupal::database();
      $sql = "SELECT id FROM mail_field_data where type = '" . $id . "'";
      $result = $database->query($sql);
      if ($result) {
        while ($row = $result->fetchAssoc()) {
          $id = $row['id'];
        }
      }
    }
    return parent::load($id);
  }


  /**
   * Obtener mensaje.
   *
   * @return mixed
   */
  public function getBody() {
    return $this->get('body')->value;
  }

  /**
   * Obtener asunto.
   *
   * @return mixed
   */
  public function getSubject() {
    return $this->get('subject')->value;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type'))
      ->setSettings([
        'max_length' => 60,
        'text_processing' => 0,
        'allowed_values' => self::getTypes(),
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 1,
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel('Asunto')
      ->setDescription('')
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);


    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel('Mensaje')
      ->setSettings([
        'default_value' => '',
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_format',
        'format' => 'html_basico',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

}
