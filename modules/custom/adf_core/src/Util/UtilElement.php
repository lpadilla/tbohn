<?php

namespace Drupal\adf_core\Util;

use Drupal\file\Entity\File;

/**
 * Class UtilElement.
 *
 * @package Drupal\adf_core\Util
 */
class UtilElement {

  /**
   * @param $object
   * @return bool|int
   */
  public static function getSize($object) {
    $serialized = serialize($object);
    if (function_exists('mb_strlen')) {
      $size = mb_strlen($serialized, '8bit');
    }
    else {
      $size = strlen($serialized);
    }
    return $size;
  }

  /**
   * Method to save file permanenty in the database.
   *
   * @param string $fid
   *   File id.
   */
  public function setFileAsPermanent($fid, $module) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    $file = File::load($fid);

    // If file doesn't exist return.
    if (!is_object($file)) {
      return;
    }

    // Set as permanent.
    $file->setPermanent();

    // Save file.
    $file->save();

    // Add usage file.
    \Drupal::service('file.usage')->add($file, $module, $module, 1);
  }

}
