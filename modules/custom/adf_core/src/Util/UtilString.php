<?php

namespace Drupal\adf_core\Util;

/**
 * Class UtilString.
 *
 * @package Drupal\adf_core\Util
 */
class UtilString {

  /**
   * @param $keys
   * @return string
   */
  public static function getHash($keys) {
    if (empty($keys) || !is_array($keys)) {
      return "";
    }
    else {
      return md5(implode("_", $keys));
    }
  }

  /**
   * @param $array
   * @param $glue
   * @return bool|string
   */
  public static function getMultiImplode($array, $glue) {
    $ret = '';
    foreach ($array as $item) {
      if (is_array($item)) {
        $ret .= UtilString::getMultiImplode($item, $glue) . $glue;
      }
      else {
        $ret .= $item . $glue;
      }
    }
    $ret = substr($ret, 0, 0 - strlen($glue));
    return $ret;
  }

  /**
   * Reemplaza los tokens dentro de un string por sus correspondientes valores.
   *
   * @param $base_url
   *   Un arreglo con la siguiente estrucutra
   *   array(
   *                    'http://{env}.example.com', // Endpoint con tokens para traducir
   *                    array ( 'env' => 'test') // Arreglo con los valores de tokens
   *                  )
   *
   * @return string   URL con tokens remplazados, para el ejemplo la salida sería http://test.example.com
   */
  public static function replaceTokensUrl($base_url) {
    foreach ($base_url[1] as $key => $value) {
      $base_url[1]['{' . $key . '}'] = $value;
      unset($base_url[1][$key]);
    }
    return strtr($base_url[0], $base_url[1]);
  }

}
