<?php

namespace Drupal\tigoid;

/**
 * Interface TigoidUserInterface.
 *
 * @package Drupal\tigoid
 */
interface TigoidUserInterface {

  /**
   *
   */
  public function getAccountByMsisdn($msisdn);

  /**
   *
   */
  public function cleanIndicative($msisdn);

}
