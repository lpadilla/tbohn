<?php

namespace Drupal\tbo_entities_co\Services;

use Drupal\tbo_entities\Entity\CompanyEntity;

/**
 * Class TboEntitiesCoService.
 *
 * @package Drupal\tbo_entities_co\Services
 */
class TboEntitiesCoService {

  /**
   * @param $nit
   * @return int
   */
  public function getDv($nit) {
    $dv = -1;
    if (is_numeric($nit)) {
      $primos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
      $posPrimo = strlen($nit) - 1;
      $sumatoria = 0;
      $nit = str_split($nit);
      foreach ($nit as $n) {
        $sumatoria += $n * $primos[$posPrimo];
        $posPrimo--;
      }
      $resto = $sumatoria % 11;
      if ($resto == 0 || $resto == 1) {
        $dv = $resto;
      }
      else {
        $dv = 11 - $resto;
      }
    }
    return $dv;
  }

  /**
   * Concatena el digito de verificación al nit o cc de los parametros.
   *
   * @param $params
   *   arreglo de parametros
   * @param $key
   *   clave del arreglo con la que se concatenará
   *
   * @return mixed arreglo de parametros
   */
  public function concatenarDV($params,$key) {
    $dv= $this->getDv($params[$key]);
    $params[$key].=$dv;
    return $params;
  }

}
