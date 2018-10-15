<?php

namespace Drupal\adf_core\Util;

use Drupal\file\Entity\File;

/**
 * Class UtilString.
 *
 * @package Drupal\adf_core\Util
 */
class UtilFile {

  /**
   * Function to save image files.
   *
   * @param mixed $fid
   *    The file id to image, can be mixed.
   * @param string $module
   *    The module name to file usage.
   */
  public static function setPermanentFile($fid, $module = '') {
    // If it's array use array_shift.
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }

    // Load file.
    $file = File::load($fid);

    // Validate if an object is not obtained.
    if (!is_object($file)) {
      return;
    }

    // Place the file as permanent.
    $file->setPermanent();

    // Save File.
    $file->save();

    // Add usage file.
    \Drupal::service('file.usage')->add($file, $module, $module, 1);
  }

}
