<?php


namespace Drupal\logos\Entity;


use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\file\Entity\File;

/**
 * Entidad Logo.
 *
 * @ingroup logo
 *
 * @ContentEntityType(
 *   id = "logo",
 *   label = @Translation("Logo"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\logos\Access\LogoAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\logos\Routing\LogoHtmlRouteProvider",
 *     },
 *   "form" = {
 *       "edit" = "Drupal\logos\Form\LogoForm",
 *       "add" = "Drupal\logos\Form\LogoForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   base_table = "logos",
 *   admin_permission = "administer logo entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/logo/{logo}/delete",
 *     "edit-form" = "/admin/config/logo/{logo}/edit",
 *     "add-form" = "/admin/config/logo/add",
 *   }
 * )
 */
class Logo extends ContentEntityBase {

  const TYPE_HEADER = 'header';
  const TYPE_FOOTER = 'footer';
  const TYPE_MAIL = 'mail';

  public static function getTypes() {
    return [
      self::TYPE_HEADER => 'Cabecera',
      self::TYPE_FOOTER => 'Pie',
      self::TYPE_MAIL => 'Correo',
    ];
  }

  /**
   * @inheritDoc
   */
  public static function load($id) {

    if (!is_numeric($id)) {
      $database = \Drupal::database();
      $sql = "SELECT id FROM logos where type = '" . $id . "'";
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
   * Obtener url del logo.
   *
   * @return string|null
   */
  public function getUrl() {
    $url = NULL;
    if ($this->get('logo')->target_id) {
      $file = File::load($this->get('logo')->target_id);
      if ($file instanceof File) {
        $url =  file_create_url($file->getFileUri());
      }
    }
    return $url;
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

    $fields['logo'] = BaseFieldDefinition::create('file')
      ->setLabel('Imagen')
      ->setRequired(TRUE)
      ->setSettings(
        [
          'file_directory' => 'logos',
          'alt_field_required' => FALSE,
          'file_extensions' => 'png jpg jpeg svg',
          'max_filesize' => '5M',
        ]
      )
      ->setDisplayOptions('form', [
        'type' => 'file',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
