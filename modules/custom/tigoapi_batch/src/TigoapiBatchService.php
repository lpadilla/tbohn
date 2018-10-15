<?php

namespace Drupal\tigoapi_batch;

use Drupal\tigoapi_batch\TigoapiBatchProcessInterface;

/**
 * Class TigoapiBatchService.
 *
 * @package Drupal\tigoapi_batch
 */
class TigoapiBatchService implements TigoapiBatchServiceInterface {

  //Guarda los nombres de los servicios recolectados
  private $processors = [];

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * @param $batch_name -> Nombre sel servicio recolectado
   * @param $search_key (Array) -> Se debe mandar siempre una llave llamada 'key'
   *
   * @return array
   * @throws \Exception
   *
   * Verifica que el servcio exista e inicializa el proceso con el metodo prepare() del servicio recolectado
   */
  public function process($batch_name, $search_key) {
    if (array_key_exists($batch_name, $this->processors)) {
      $row = $this->processors[$batch_name]->prepare($search_key);

      $hash_request = md5($batch_name . $search_key['key']);

      $tags = [
        $batch_name,
        $batch_name . ":" . $search_key['key'],
      ];

      $total_steps = ceil(count($row) / $this->processors[$batch_name]->getRowsBySteps());
      $lots = array_chunk($row, $this->processors[$batch_name]->getRowsBySteps());

      $steps = [];
      for ($n = 1; $n <= $total_steps; $n++) {
        $steps[$n] = [
          'status' => 'pending',
          'rows' => $lots[($n - 1)],
        ];
      }
      $data = [
        'batch_name' => $batch_name,
        'rows' => $row,
        'lots' => $lots,
        'steps' => $steps,
      ];

      \Drupal::cache()->set($hash_request, $data, $this->processors[$batch_name]->getCacheTime(), $tags);

      return [
        'hash' => $hash_request,
        'steps' => $steps,
        //'current_step' => 1,
        //'data' => $data,
      ];

    }
    else {
      throw new \Exception("Processor $batch_name does not exist");
    }
  }

  /**
   * @param $batch_name -> name of service
   * @param $batch_hash
   * @param $step
   *
   * @return array
   * @throws \Exception
   */
  public function doStep($batch_name, $batch_hash, $step) {
    $cache_data = \Drupal::cache()->get($batch_hash);

    if ($cache_data) {
      $data = $cache_data->data;
      $result = [];
      if ($step > count($data['steps'])) {
        throw new \Exception("Step $step does not exist for batch $batch_hash");
      }
      foreach ($data['steps'][$step]['rows'] as $index => $row) {
        if (is_array($row)) {
          $result[$row['key_data']] = $this->processors[$batch_name]->processStep($row);
        }
        else {
          $result[$row] = $this->processors[$batch_name]->processStep($row);
        }
      }
      $hash_step = $batch_hash . $step;
      \Drupal::cache()->set($hash_step, $result, $this->processors[$batch_name]->getCacheTime());

      return ['hash_step' => $hash_step];
    }
    else {
      throw new \Exception("Batch id $batch_hash does not exist");
    }
  }

  /**
   * @param $batch_hash
   *
   * @return array
   * @throws \Exception
   */
  public function getResult($batch_hash, $batch_name) {
    $cache_data = \Drupal::cache()->get($batch_hash);
    if ($cache_data) {

      $result = [];
      foreach ($cache_data->data['steps'] as $key => $step) {
        $hash_step = $batch_hash . ($key);
        $cache_result = \Drupal::cache()->get($hash_step);
        if (!$cache_result) {
          throw new \Exception("Batch is not completed");
        }
        $cache_result = $this->processors[$batch_name]->result($cache_result->data);
        $result = array_replace($result, (array) $cache_result);

      }

      return $this->object_to_array($result);
    }
    else {
      throw new \Exception("Batch id $batch_hash does not exist");
    }
  }

  /**
   * @param \Drupal\tigoapi_batch\TigoapiBatchProcessInterface $processor
   */
  public function addProcessor(TigoapiBatchProcessInterface $processor) {
    $this->processors[$processor->getId()] = $processor;
  }

  /**
   * @param $obj
   *
   * @return array
   */
  function object_to_array($obj) {
    if (is_object($obj)) {
      $obj = (array) $obj;
    }
    if (is_array($obj)) {
      $new = array();
      foreach ($obj as $key => $val) {
        $new[$key] = $this->object_to_array($val);
      }
    }
    else {
      $new = $obj;
    }

    return $new;
  }

}
