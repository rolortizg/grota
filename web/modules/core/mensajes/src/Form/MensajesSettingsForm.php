<?php

namespace Drupal\mensajes\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that configures devel settings.
 */

/**
 * Configure example settings for this site.
 */
class MensajesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mensajes_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mensajes.settings',
    ];
  }


  protected function themeTypesOptions() {
    return [
      'mint' => 'Mint',
      'sunset' => 'Sunset',
      'relax' => 'Relax',
      'nest' => 'Nest',
      'metroui' => 'Metroui',
      'semanticui' => 'Semanticui',
      'light' => 'Light',
      'bootstrap-v3' => 'Bootstrap-v3',
      'bootstrap-v4' => 'Bootstrap-v4',
    ];
  }

  public static function messageTypeOptions() {
    return [
      'status' => 'Estado',
      'warning' => 'Advertencia',
      'error' => 'Error',
      'info' => 'Información',
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mensajes.settings');

    foreach ($this->messageTypeOptions() as $key => $valor) {
      $config_mensaje = $config->get($key);
      $form[$key] = [
        '#type' => 'fieldset',
        '#title' => $valor,
        '#tree' => TRUE,
      ];

      $form[$key]['icono'] = [
        '#type' => 'textfield',
        '#title' => 'Icono',
        '#description' => 'Especificar la clase de Boostrap. Ej.: done',
        '#default_value' => $config_mensaje['icono'],
      ];

      $form[$key]['tema'] = [
        '#type' => 'select',
        '#title' => 'Tema',
        '#options' => $this->themeTypesOptions(),
        '#description' => 'Ver temas: https://ned.im/noty/#/themes',
        '#default_value' => $config_mensaje['tema'],
      ];

      $form[$key]['tiempo'] = [
        '#type' => 'number',
        '#title' => 'Tiempo',
        '#field_suffix' => 'ms.',
        '#default_value' => $config_mensaje['tiempo'],
      ];

      $form[$key]['sonido'] = [
        '#type' => 'managed_file',
        '#title' => 'Sonido',
        '#upload_validators' => [
          'file_validate_extensions' => ['wav mp3'],
        ],
        '#upload_location' => 'public://mensajes',
        '#default_value' => $config_mensaje['sonido'],
      ];

    }


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('mensajes.settings');
    $estados = [];
    foreach ($this->messageTypeOptions() as $key => $valor) {
      $config_mensaje = $config->get($key);
      $fid_sonido = 0;
      if (isset($form_state->getValue([$key, 'sonido'])[0])) {
        $fid_sonido = $form_state->getValue([$key, 'sonido'])[0];
      }

      if ($fid_sonido != $config_mensaje['sonido'] && is_numeric($config_mensaje['sonido'])) {
        file_delete($config_mensaje['sonido']);
      }
      if (is_numeric($fid_sonido) && $fid_sonido > 0) {
        $file = File::load($fid_sonido);
        $file->setPermanent();
        $file->save();
        $urlSonido = file_create_url($file->uri);
      }
      else {
        if ($key == 'error') {
          $urlSonido = '/' . drupal_get_path('module', 'mensajes') . '/sounds/default_error.mp3';
        }
        else {
          $urlSonido = '/' . drupal_get_path('module', 'mensajes') . '/sounds/default.wav';
        }
      }
      $tiempo = $form_state->getValue([
        $key,
        'tiempo',
      ]) == '' ? NULL : $form_state->getValue([$key, 'tiempo']);
      $estados[$key] = [
        'icono' => $form_state->getValue([$key, 'icono']),
        'tema' => $form_state->getValue([$key, 'tema']),
        'tiempo' => $tiempo,
        'sonido' => $fid_sonido,
        'urlSonido' => $urlSonido,
      ];
    }

    $config = $this->configFactory->getEditable('mensajes.settings');

    foreach ($this->messageTypeOptions() as $key => $valor) {
      $config->set($key, $estados[$key]);
    }

    $config->save();
  }

}
