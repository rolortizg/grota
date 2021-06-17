<?php

namespace Drupal\grota_config\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;



/**
 * Filter by province form
 *
 * @internal
 */
class OrdenForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'orden_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['container'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => ['class' => ['container']],
    ];

    $form['container']['precio'] = [
      '#type' => 'select',
      '#options' => [
        'all' => 'Precio',
        'ASC' => 'Asc',
        'DESC' => 'Desc',
      ],
      '#attributes' => ['class' => ['orden-precio']],
      '#validate' => TRUE,
      '#default_value' => 'all',
    ];
    $form['container']['impacto'] = [
      '#type' => 'select',
      '#options' => [
        'all' => 'Ahorro de impacto',
        'ASC' => 'Asc',
        'DESC' => 'Desc',
      ],
      '#attributes' => ['class' => ['orden-impacto']],
      '#validate' => TRUE,
      '#default_value' => 'all',
    ];

   return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
