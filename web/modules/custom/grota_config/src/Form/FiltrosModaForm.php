<?php

namespace Drupal\grota_config\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\grota_config\Controller\ProductoController;
use Drupal\taxonomy\Entity\Term;



/**
 * Filter by province form
 *
 * @internal
 */
class FiltrosModaForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'filtros_moda_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $opciones_labor_social = ProductoController::labor_social();
    $opciones_produccion = ProductoController::produccion();
    $opciones_materiales = ProductoController::materiales();
    $padres = ProductoController::padres();
    $publico = ProductoController::publico_dirigido();

    $form['container'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => ['id' => ['container-global-form'], 'class' => 'container'],
    ];

    $form['container']['group_publico'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container']],
    ];
    $form['container']['group_publico']['texto'] = [
      '#markup' =>'<p>Moda</p>'
    ];
    $form['container']['group_publico']['publico'] = [
      '#type' => 'radios',
      '#options' => $publico,
      '#attributes' => ['class' => ['publico']],
      '#validate' => TRUE,
      '#default_value' => 'all',
      '#ajax' => [
        'event' => 'change',
        'callback' => '::form_cambio_callback',
        'wrapper' => 'container-global-form',
        'method' => 'replace'
      ],
    ];

    $form['container']['mostrar_filtros'] = [
      '#markup' =>'<a class="boton-filtros boton-filtrar">Filtrar</a>'
    ];
    $form['container']['filtros_vista'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-filtros-vista']],
    ];
    $form['container']['filtros_vista']['filtros'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-filtros', 'ocultar-filtros']],
    ];
    $form['container']['filtros_vista']['filtros']['texto'] = [
      '#markup' => '<p>Filtra por símbolo:</p>',
    ];
    $form['container']['filtros_vista']['filtros']['group_labor_social'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container', 'contenido-acordeon']],
    ];
    $form['container']['filtros_vista']['filtros']['group_labor_social']['labor_social'] = [
      '#markup' => '<p class="flecha-bottom">Labor social</p>',
    ];
    $form['container']['filtros_vista']['filtros']['group_labor_social']['opciones'] = [
      '#type' => 'checkboxes',
      '#options' => $opciones_labor_social,
      '#attributes' => ['class' => ['labor-social']],
      '#validate' => TRUE,
      '#ajax' => [
        'event' => 'change',
        'callback' => '::form_cambio_callback',
        'wrapper' => 'container-global-form',
        'method' => 'replace'
      ],
    ];
    $form['container']['filtros_vista']['filtros']['group_produccion'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container', 'contenido-acordeon']],
    ];
    $form['container']['filtros_vista']['filtros']['group_produccion']['producción'] = [
      '#markup' => '<p class="flecha-bottom">Producción</p>',
    ];
    $form['container']['filtros_vista']['filtros']['group_produccion']['opciones'] = [
      '#type' => 'checkboxes',
      '#options' => $opciones_produccion,
      '#attributes' => ['class' => ['produccion']],
      '#ajax' => [
        'event' => 'change',
        'callback' => '::form_cambio_callback',
        'method' => 'replace',
        'wrapper' => 'container-global-form',
      ],
    ];
    $form['container']['filtros_vista']['filtros']['group_materiales'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container', 'contenido-acordeon']],
    ];
    $form['container']['filtros_vista']['filtros']['group_materiales']['materiales'] = [
      '#markup' => '<p class="flecha-bottom">Materiales</p>',
    ];

    $form['container']['filtros_vista']['filtros']['group_materiales']['opciones'] = [
      '#type' => 'checkboxes',
      '#options' => $opciones_materiales,
      '#attributes' => ['class' => ['materiales']],
      '#ajax' => [
        'event' => 'change',
        'callback' => '::form_cambio_callback',
        'method' => 'replace',
        'wrapper' => 'container-global-form',
      ],
    ];
    $grupo_publico = 'all';
    if($form_state->getTriggeringElement()) {
      $values = $form_state->getValues();
      if (isset($values['container']['group_publico']['publico'])) {
        $grupo_publico = $values['container']['group_publico']['publico'];
      }else{
        $grupo_publico = 'all';
      }
    }
    $form['container']['filtros_vista']['filtros']['texto2'] = [
      '#markup' => '<p>Filtra por tipo:</p>',
    ];
    foreach ($padres as $index => $padre) {
      $termino = Term::load($index);
      $opciones_hijo = ProductoController::hijos($index, $grupo_publico);
      $nombre = $termino->name->value;
      if(!empty($opciones_hijo)){
        $form['container']['filtros_vista']['filtros']['group_' .$padre] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['container', 'contenido-acordeon']],
        ];
        $form['container']['filtros_vista']['filtros']['group_' . $padre][$padre] = [
          '#markup' => '<p class="flecha-bottom">' . $nombre . '</p>',
        ];

        $form['container']['filtros_vista']['filtros']['group_' . $padre]['opciones'] = [
          '#type' => 'checkboxes',
          '#options' => $opciones_hijo,
          '#attributes' => ['class' => ['group-' . $padre]],
          '#ajax' => [
            'event' => 'change',
            'callback' => '::form_cambio_callback',
            'wrapper' => 'container-global-form',
            'method' => 'replace',
          ],
        ];
      }

    }
    $form['container']['filtros_vista']['group_vista'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-vista']],
    ];
    $form['container']['filtros_vista']['group_vista']['texto'] = [
      '#markup' => '<p class="cargando-productos">Cargando productos...</p>',
    ];
    $form['container']['filtros_vista']['group_vista']['vista'] = [
      '#type' => 'view',
      '#name' => 'tipos_de_productos',
      '#display_id' => 'moda',
      '#arguments' => [],
      '#prefix' => '<div id="vista-productos">',
      '#suffix' => '</div>',
    ];
    if($form_state->getTriggeringElement()){
      $values = $form_state->getValues();
      //\Drupal::state()->set('aux', $values);
      $labor_social = '0';
      if (isset($values['container']['filtros_vista']['filtros']['group_labor_social']['opciones'])) {
        foreach ($values['container']['filtros_vista']['filtros']['group_labor_social']['opciones'] as $index => $opcion) {
          if ($opcion != 0) {
            $labor_social = $labor_social . '+' . $opcion;
          }
        }
      }
      if ($labor_social == '0') {
        $labor_social = 'all';
      }
      $produccion = '0';
      if (isset($values['container']['filtros_vista']['filtros']['group_produccion']['opciones'])) {
        foreach ($values['container']['filtros_vista']['filtros']['group_produccion']['opciones'] as $index => $opcion) {
          if ($opcion != 0) {
            $produccion = $produccion . '+' . $opcion;
          }
        }
      }
      if ($produccion == '0') {
        $produccion = 'all';
      }
      $materiales = '0';
      if (isset($values['container']['filtros_vista']['filtros']['group_materiales']['opciones'])) {
        foreach ($values['container']['filtros_vista']['filtros']['group_materiales']['opciones'] as $index => $opcion) {
          if ($opcion != 0) {
            $materiales = $materiales . '+' . $opcion;
          }
        }
      }
      if ($materiales == '0') {
        $materiales = 'all';
      }
      $tipos = '0';

      $padres = ProductoController::padres();
      foreach ($padres as $index => $padre) {

        if ($values['container']['filtros_vista']['filtros']['group_'.$padre]['opciones']) {

          foreach ($values['container']['filtros_vista']['filtros']['group_'.$padre]['opciones'] as $key => $option) {
            //\Drupal::state()->set('aux2', $option);

            if ($option != 0) {
              $tipos = $tipos . '+' . $key;
            }
          }
        }
      }
      if ($tipos == '0') {
        $tipos = 'all';
      }
      $grupo_publico = 'all';
      if (isset($values['container']['group_publico']['publico'])) {
        $grupo_publico = $values['container']['group_publico']['publico'];
      }
      $form['container']['filtros_vista']['group_vista']['vista']['#arguments'] = [$labor_social, $produccion, $materiales, $tipos, $grupo_publico];
    }else{
      $form['container']['filtros_vista']['group_vista']['vista'] =[
     '#type' => 'hidden',
     '#prefix' => '<div id="vista-productos">',
     '#suffix' => '</div>',
   ];
    }

    /*$form['container']['vista'] =[
      '#type' => 'textfield',
      '#prefix' => '<div id="vista-productos">',
      '#suffix' => '</div>',
    ];*/
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  public function form_cambio_callback(array &$form, FormStateInterface $form_state) {
    return $form['container'];
  }

}
