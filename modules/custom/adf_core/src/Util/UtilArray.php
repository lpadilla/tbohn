<?php

namespace Drupal\adf_core\Util;

/**
 * Class UtilArray.
 *
 * @package Drupal\adf_core\Util
 */
class UtilArray {

  /**
   * Some webservices return an array of objects when there is more than 1 result,
   * or the object directly if there is only 1 result. This function puts that
   * lonely object in an array to standarize the output.
   *
   * @param mixed $record
   *   The unknown output that might be an array or an object.
   *
   * @return array         The array version of the $record.
   */
  public static function tbo_util_unique_object_to_array($record) {
    if (empty($record)) {
      return [];
    }
    elseif (is_array($record)) {
      return $record;
    }
    else {
      return [$record];
    }
  }

}
