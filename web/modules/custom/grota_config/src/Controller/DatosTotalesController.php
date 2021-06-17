<?php


namespace Drupal\grota_config\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\content_statistics\Controller;
use Drupal\taxonomy\Entity\Term;


/**
 * Class DatosTotalesController.
 */
class DatosTotalesController extends ControllerBase {

  /*Dato que obtiene el total de CO2 que se pinta en el Home*/
  public static function total_cantidad_co2(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_co2','t');
    $sql->fields('t',array('field_total_co2_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_co2_value'];
    }
    return $total;
  }

  /*Dato que obtiene el total de Orgánico que se pinta en el Home*/
  public static function total_cantidad_organico(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_organico','o');
    $sql->fields('o',array('field_total_organico_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_organico_value'];
    }
    return $total;
  }

  /*Dato que obtiene el total de Compostable que se pinta en el Home*/
  public static function total_cantidad_compostable(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_compostable','c');
    $sql->fields('c',array('field_total_compostable_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_compostable_value'];
    }
    return $total;
  }

  /*Dato que obtiene el total de Biosintético que se pinta en el Home*/
  public static function total_cantidad_biosintetico(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_biosintetico','b');
    $sql->fields('b',array('field_total_biosintetico_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_biosintetico_value'];
    }
    return $total;
  }

  /*Dato que obtiene el total de Reciclable que se pinta en el Home*/
  public static function total_cantidad_reciclable(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_reciclable','r');
    $sql->fields('r',array('field_total_reciclable_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_reciclable_value'];
    }
    return $total;
  }


  /*Dato que obtiene el total de Reciclado que se pinta en el Home*/
  public static function total_cantidad_reciclado(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_reciclado','r');
    $sql->fields('r',array('field_total_reciclado_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_reciclado_value'];
    }
    return $total;
  }


  /*Dato que obtiene el total de Biodegradable que se pinta en el Home*/
  public static function total_cantidad_biodegradable(){
    $total =0;
    $sql = \Drupal::database()->select('node__field_total_biodegradable','b');
    $sql->fields('b',array('field_total_biodegradable_value'));
    $resul = $sql->execute();
    while( $fil= $resul->fetchAssoc() ) {
      $total = $fil['field_total_biodegradable_value'];
    }
    return $total;
  }

}

