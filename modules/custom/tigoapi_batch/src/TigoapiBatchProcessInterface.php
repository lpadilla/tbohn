<?php

namespace Drupal\tigoapi_batch;

/**
 * Interface TigoapiBatchServiceInterface.
 *
 * @package Drupal\tigoapi_batch
 */
interface TigoapiBatchProcessInterface {

  /**
   *
   */
  public function getId();

  /**
   *
   */
  public function prepare($search_key);

  /**
   *
   */
  public function processStep($row);

  /**
   *
   */
  public function result($row);

  /**
   *
   */
  public function getRowsBySteps();

  /**
   *
   */
  public function getCacheTime();

}
