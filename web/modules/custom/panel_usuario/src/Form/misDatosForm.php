<?php

namespace Drupal\panel_usuario\Form;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Class misDatosForm.
 */
class misDatosForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mis_datos_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tipo = NULL) {
    $account = \Drupal::currentUser();
    $list = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties([
        'uid' => $account->id(),
        'is_default' => 1,
      ]);
    $billing_profile = NULL;
    $shipping_profile = NULL;
    if (!empty($list)) {
      /** @var Profile $profile */
      foreach ($list as $profile) {
        if ($profile->bundle() == 'customer') {
          $shipping_profile = $profile;
        }
        elseif ($profile->bundle() == 'factura') {
          $billing_profile = $profile;
        }
      }
    }


    /** @var User $usuario */
    $usuario = User::load($account->id());

    $form['#tree'] = TRUE;

    $form['columna1'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'group-col1',
        ],
      ],
      '#weight' => 0,
    ];
    $form['columna1']['texto'] = [
      '#markup' => '<p>Mis datos</p>',
    ];
    $form['columna1']['datos_personales'] = [
      '#type' => 'fieldset',
      '#title' => 'Datos personales',
      '#attributes' => [
        'class' => [
          'group-datos-personales',
        ],
      ],
      '#weight' => 0,
    ];

    $form['columna1']['datos_personales']['nombre'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre',
      '#title_display' => 'none',
      '#default_value' => isset($usuario->get('field_nombre')->value) ? $usuario->get('field_nombre')->value : '',
      '#attributes' => [
        'placeholder' => ['Nombre'],
        'data-toggle' => 'tooltip',
        'title' => 'Nombre'
      ],
    ];
    $form['columna1']['datos_personales']['apellidos'] = [
      '#type' => 'textfield',
      '#title' => 'Apellidos',
      '#title_display' => 'none',
      '#default_value' => isset($usuario->get('field_apellidos')->value) ? $usuario->get('field_apellidos')->value : '',
      '#attributes' => [
        'placeholder' => ['Apellidos'],
        'data-toggle' => 'tooltip',
        'title' => 'Apellidos'
      ],
    ];

    $form['columna1']['datos_personales']['email'] = [
      '#type' => 'textfield',
      '#title' => 'Email',
      '#title_display' => 'none',
      '#default_value' => $usuario->getEmail(),
      '#attributes' => [
        'placeholder' => ['Email'],
        'data-toggle' => 'tooltip',
        'title' => 'Email'
      ],
    ];
    $form['columna1']['datos_personales']['telefono'] = [
      '#type' => 'textfield',
      '#title' => 'Teléfono',
      '#title_display' => 'none',
      '#default_value' => isset($usuario->get('field_telefono')->value) ? $usuario->get('field_telefono')->value : '',
      '#attributes' => [
        'placeholder' => ['Número de teléfono'],
        'data-toggle' => 'tooltip',
        'title' => 'Teléfono'
      ],
    ];

    $form['columna1']['datos_fiscales'] = [
      '#type' => 'fieldset',
      '#title' => 'Datos fiscales',
      '#attributes' => [
        'class' => [
          'group-envio',
        ],
      ],
      '#weight' => 1,
    ];

    $form['columna1']['datos_fiscales']['dni'] = [
      '#type' => 'textfield',
      '#title' => 'DNI',
      '#title_display' => 'none',
      '#default_value' => $billing_profile ? $billing_profile->get('field_dni')->value : '',
      '#attributes' => [
        'placeholder' => ['CIF/NIF'],
        'data-toggle' => 'tooltip',
        'title' => 'CIF/NIF'
      ],
    ];

    $form['columna1']['datos_fiscales']['direccion'] = [
      '#type' => 'address',
      '#title' => 'Datos fiscales',
      '#profile_id' => $billing_profile ? $billing_profile->id() : 0,
      '#default_value' => $billing_profile ?  $billing_profile->toArray()['address'][0] : ['country_code' => 'ES'],
      '#used_fields' => [
        AddressField::ORGANIZATION,
        AddressField::ADDRESS_LINE1,
        AddressField::ADDRESS_LINE2,
        AddressField::LOCALITY,
        AddressField::ADMINISTRATIVE_AREA,
        AddressField::POSTAL_CODE,
      ],
      '#available_countries' => ['ES'],
      '#after_build' => [
        '::address_modify',
      ],
    ];
    $form['columna2'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'group-col2',
        ],
      ],
      '#weight' => 0,
    ];
    $form['columna2']['texto'] = [
      '#markup' => '<p>Direcciones</p>',
    ];
    $form['columna2']['envio'] = [
      '#type' => 'fieldset',
      '#attributes' => [
        'class' => [
          'group-envio',
        ],
      ],
      '#weight' => 1,
    ];


    $form['columna2']['envio']['datos_envio'] = [
      '#type' => 'address',
      '#title' => 'Datos de envio',
      '#default_value' => $shipping_profile ?  $shipping_profile->toArray()['address'][0] : ['country_code' => 'ES'],
      '#profile_id' => $shipping_profile ? $shipping_profile->id() : 0,
      '#used_fields' => [
        AddressField::ADDRESS_LINE1,
        AddressField::ADDRESS_LINE2,
        AddressField::LOCALITY,
        AddressField::ADMINISTRATIVE_AREA,
        AddressField::POSTAL_CODE,
      ],
      '#available_countries' => ['ES'],
      '#after_build' => [
        '::address_modify',
      ],
    ];


    $form['columna3'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'group-col3',
        ],
      ],
      '#weight' => 0,
    ];
    $form['columna3']['texto'] = [
      '#markup' => '<p>Mis tarjetas</p>',
    ];

    $form['columna3']['tarjetas'] = [
      '#theme' => 'tarjetas',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Guardar cambios',
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $account = \Drupal::currentUser();
    $usuario = User::load($account->id());
    $mail = $usuario->mail->value;
    $values = $form_state->getValues();
    if(isset($values['columna1']['datos_personales']['email'])){
      if($values['columna1']['datos_personales']['email'] != $mail){
        $existe = Drupal\grota_config\Controller\UsuarioController::comprobar_mail($values['columna1']['datos_personales']['email']);
        if($existe != false){
          $form_state->setErrorByName('email', $this->t('El email introducido ya existe'));
        }
      }
    }

    parent::validateForm($form, $form_state);
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = \Drupal::currentUser();
    $values = $form_state->getValues();
    /** @var User $usuario */
    $usuario = User::load($account->id());

    $usuario->set('field_nombre', $values['columna1']['datos_personales']['nombre']);
    $usuario->set('field_apellidos', $values['columna1']['datos_personales']['apellidos']);
    $usuario->set('field_telefono', $values['columna1']['datos_personales']['telefono']);
    $usuario->setEmail($values['columna1']['datos_personales']['email']);

    $address_billing = $values['columna1']['datos_fiscales']['direccion'];
    $address_billing['given_name'] = $values['columna1']['datos_personales']['nombre'];
    $address_billing['family_name'] = $values['columna1']['datos_personales']['apellidos'];

    $address_shipping = $values['columna2']['envio']['datos_envio'];
    $address_shipping['given_name'] = $values['columna1']['datos_personales']['nombre'];
    $address_shipping['family_name'] = $values['columna1']['datos_personales']['apellidos'];

    if ($form['columna1']['datos_fiscales']['direccion']['#profile_id']) {
      /** @var Profile $billing_profile */
      $billing_profile = Profile::load($form['columna1']['datos_fiscales']['direccion']['#profile_id']);

      $billing_profile->set('address', $address_billing);
      $billing_profile->set('field_telefono', $values['columna1']['datos_personales']['telefono']);
      $billing_profile->set('field_dni', $values['columna1']['datos_fiscales']['dni']);
    }
    else {
      $billing_profile = Profile::create([
        'type' => 'factura',
        'uid' => $usuario->id(),
        'address' => $address_billing,
        'field_dni' => $values['columna1']['datos_fiscales']['dni'],
        'field_telefono' => $values['columna1']['datos_personales']['telefono']
      ]);
    }


    if ($form['columna2']['envio']['datos_envio']['#profile_id']) {
      /** @var Profile $shipping_profile */
      $shipping_profile = Profile::load($form['columna2']['envio']['datos_envio']['#profile_id']);

      $shipping_profile->set('address', $address_shipping);
      $shipping_profile->set('field_telefono', $values['columna1']['datos_personales']['telefono']);
      $shipping_profile->set('field_dni', $values['columna1']['datos_fiscales']['dni']);
    }
    else {
      $shipping_profile = Profile::create([
        'type' => 'customer',
        'uid' => $usuario->id(),
        'address' => $address_shipping,
        'field_dni' => $values['columna1']['datos_fiscales']['dni'],
        'field_telefono' => $values['columna1']['datos_personales']['telefono']
      ]);
    }

    try {
      $usuario->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('panel_usuario')->error($e->getMessage());
    }
    try {
      $billing_profile->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('panel_usuario')->error($e->getMessage());
    }
    try {
      $shipping_profile->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('panel_usuario')->error($e->getMessage());
    }

    $this->messenger()->addStatus('Datos guardados');

  }

  public static function address_modify($element, $form_state){
    $element['organization']["#title_display"] = 'none';
    $element['organization']["#attributes"]['placeholder'] = 'Razón social';
    $element['organization']["#attributes"]['data-toggle'] = 'tooltip';
    $element['organization']["#attributes"]['title'] = $element['organization']["#attributes"]['placeholder'];
    $element['given_name']["#title_display"] = 'none';
    $element['given_name']["#attributes"]['placeholder'] = 'Nombre';
    $element['given_name']["#attributes"]['data-toggle'] = 'tooltip';
    $element['given_name']["#attributes"]['title'] = $element['given_name']["#attributes"]['placeholder'];
    $element['family_name']["#title_display"] = 'none';
    $element['family_name']["#attributes"]['placeholder'] = 'Apellidos';
    $element['family_name']["#attributes"]['data-toggle'] = 'tooltip';
    $element['family_name']["#attributes"]['title'] = $element['family_name']["#attributes"]['placeholder'];
    $element['address_line1']["#title_display"] = 'none';
    $element['address_line1']["#attributes"]['placeholder'] = 'Calle y número';
    $element['address_line1']["#attributes"]['data-toggle'] = 'tooltip';
    $element['address_line1']["#attributes"]['title'] = 'Calle y número';
    $element['address_line2']["#title_display"] = 'none';
    $element['address_line2']["#attributes"]['placeholder'] = 'Escalera y piso';
    $element['address_line2']["#attributes"]['data-toggle'] = 'tooltip';
    $element['address_line2']["#attributes"]['title'] = 'Escalera y piso';
    $element['postal_code']["#title_display"] = 'none';
    $element['postal_code']["#attributes"]['placeholder'] = 'Código postal';
    $element['postal_code']["#attributes"]['data-toggle'] = 'tooltip';
    $element['postal_code']["#attributes"]['title'] = $element['postal_code']["#attributes"]['placeholder'];
    $element['locality']["#title_display"] = 'none';
    $element['locality']["#attributes"]['placeholder'] = 'Ciudad';
    $element['locality']["#attributes"]['data-toggle'] = 'tooltip';
    $element['locality']["#attributes"]['title'] = $element['locality']["#attributes"]['placeholder'];
    $element['administrative_area']["#title_display"] = 'none';
    $element['administrative_area']["#options"][''] = '- Provincia -';
    $element['administrative_area']["#attributes"]['data-toggle'] = 'tooltip';
    $element['administrative_area']["#attributes"]['title'] ='Provincia';

    $element['country_code']['#attributes']['class'][] = 'hidden';

    return $element;
  }


}
