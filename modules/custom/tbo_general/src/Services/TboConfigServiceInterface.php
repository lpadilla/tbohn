<?php

namespace Drupal\tbo_general\Services;

/**
 * Interface TboConfigServiceInterface.
 *
 * @package Drupal\tbo_general\Services
 */
interface TboConfigServiceInterface {

  /**
   * Obtener valor de variable de configuracion.
   *
   * @param $group
   *   //Grupo de configuracion
   * @param $property
   *   //Propiedad de configuracion
   * @param mixed //Valor por defecto en caso de no encontrar valor
   *
   * @return mixed Valor de configuracion o default var si no existe
   */
  public function getConfig($group, $property, $default = FALSE);

  /**
   * Obtener grupo de variables de configuracion.
   *
   * @param $group
   *   //Grupo a obtener
   * @param mixed $default
   *   Valor por defecto a retornar si no existe.
   *
   * @return mixed Grupo de configuracion o valor por defecto
   */
  public function getConfigGroup($group, $default = NULL);

  /**
   * Format currency value.
   *
   * @param $value
   *
   * @return string
   */
  public function formatCurrency($value);

  /**
   * Formatea una fecha de acuerdo al formato establecido en al configuracion de TBO.
   *
   * @param $value
   *
   * @return mixed
   */
  public function formatDate($value);

}
