<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CategoryServicesRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class CategoryServicesRestService {

  protected $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser, $type, $category = NULL) {
    $this->currentUser = $currentUser;
    // Remove cache.
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    // if (!$this->currentUser->hasPermission('access list company')) {
    //  throw new AccessDeniedHttpException();
    // }
    switch ($type) {
      case 'category_services':
        $response = $this->getListEntityCategoryServices();
        break;

      case 'services_account':
        $response = $this->getPortfolioByContractId($category);
        break;
    }
    $_SESSION['adf_segment']['send_services'] = 1;
    return $response;
  }

  /**
   * @return $this
   */
  protected function getListEntityCategoryServices() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Get repository.
    $service = \Drupal::service('tbo_account.categories_services');

    $data = $service->getCategories();

    $uuid = \Drupal::request()->get('block');
    $cid = 'config:block:' . $uuid;
    $block = \Drupal::cache()->get($cid);

    if (!$block) {
      $error = [
        'error_code' => "700",
        'error_message' => "No se pudo obtener la configuración del widget",
      ];
      $response = new ResourceResponse($error, 500);
      return $response;
    }
    $data_block = $block->data;

    $fields = $data_block['table_options']['table_fields'];

    uasort($fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $data2 = [];

    if (isset($data['error'])) {
      return new ResourceResponse($data);
    }

    if (is_array($data)) {
      foreach ($fields as $key => $field) {
        if (isset($data[$key]) && $field['show']) {
          $data2[$key] = $data[$key];
        }
      }
    }

    return new ResourceResponse($data2);
  }

  /**
   * @param $category
   * @return static
   */
  protected function getPortfolioByContractId($category = '') {
    \Drupal::service('page_cache_kill_switch')->trigger();

    try {

      $service = \Drupal::service('tbo_account.categories_services');
      $response = $service->getPortfolioByContractId();

      // Segment parameters.
      $response['userId'] = $this->currentUser->id();
      $response['send'] = $_SESSION['adf_segment']['send_services'];

    }
    catch (\Exception $e) {
      $error = [
        'error_code' => "700",
        'error_message' => "No se obtuvo información del pago, consulta en 5 minutos el estado de tu factura.",
      ];
      $response = new ResourceResponse($error, 500);
      return $response;
    }
    // \Drupal::logger('$response')->notice(print_r($response, TRUE));.
    return new ResourceResponse($response);

  }

}
