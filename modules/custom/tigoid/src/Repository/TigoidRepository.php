<?php

namespace Drupal\tigoid\Repository;

/**
 *
 */
class TigoidRepository {

  /**
   * @param $uid
   * @return mixed
   */
  public function getTigoId($uid) {
    $query = \Drupal::database()->select('openid_connect_authmap', 'open')
      ->fields('open', ['sub'])
      ->condition('uid', $uid, '=');

    $tigoId = $query->execute()->fetchField();

    return $tigoId;
  }

}
