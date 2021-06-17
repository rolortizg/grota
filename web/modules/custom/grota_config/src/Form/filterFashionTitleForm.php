<?php

namespace Drupal\grota_config\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;


/**
 * Filter by province form
 *
 * @internal
 */
class filterFashionTitleForm extends FormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'filter_fashion_title_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['title_filter'] = [
            '#type' => 'textfield',
            /*'#autocomplete_route_name' => 'grota_config.autocomplete.title',*/
            '#attributes' => array(
                'class' => array('input-filter'),
                'placeholder' => '¿buscas algo concreto?',
            ),
        ];

        $form['submit'] = [
            '#type' => 'item',
            '#markup' => '<span id="search-fashion-title" href="#">b</span>',
        ];

        return $form;
    }



    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        return $form;
    }
}
