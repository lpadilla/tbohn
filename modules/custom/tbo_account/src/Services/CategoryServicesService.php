<?php

namespace Drupal\tbo_account\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class CategoryServicesService.
 *
 * @package Drupal\tbo_account\Services
 */
class CategoryServicesService {

  private $categories;
  protected $collections;

  protected $api;

  protected $getRecurringInfo = NULL;

  /**
   * HomeRecurringPaymentService constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * @return mixed
   */
  public function getCategories() {

    $service = __FUNCTION__;
    $options = [];
    $options = BaseApiCache::get("entity", $service, array_merge([], []));
    if (empty($options)) {
      try {
        $categories = \Drupal::entityQuery('category_services_entity')->execute();
      }
      catch (\Exception $e) {
        // Return message in rest.
        return UtilMessage::getMessage($e);
      }

      $options = [];

      foreach ($categories as $category => $value) {
        $entity = \Drupal::entityTypeManager()
          ->getStorage('category_services_entity')
          ->load($category);

        $options[$entity->get('id')] = $entity->getValues();
      }

      // Save categories in cache.
      BaseApiCache::set("entity", $service, array_merge([], []), $options, 120);
    }

    $this->categories = $options;

    $categories = [];
    // Get categories logic in cache.
    $categories = BaseApiCache::get('data', $service, array_merge([], []));
    if (empty($categories)) {
      foreach ($this->categories as $key => $category) {
        $file = File::load($category['icon']);
        $style = ImageStyle::load('thumbnail');
        if ($file) {
          // Generates file url.
          $category['icon_url'] = $style->buildUrl($file->getFileUri());
        }
        $categories[$key] = $category;
      }
      // Save categories logic in cache.
      BaseApiCache::set('data', $service, array_merge([], []), $categories, 120);
    }

    return $categories;
  }

  /**
   *
   */
  public function getCategoriesById($id_category) {

    $service = __FUNCTION__;

    try {
      $category = \Drupal::entityQuery('category_services_entity')->condition('id', $id_category)->execute();
    }
    catch (\Exception $e) {
      // Return message in rest.
      return UtilMessage::getMessage($e);
    }

    $entity = \Drupal::entityTypeManager()
      ->getStorage('category_services_entity')
      ->load(reset($category));

    return $entity;
  }

  /**
   * @return mixed
   */
  public function getPortfolioByContractId() {
    if (is_null($this->collections)) {

      if (isset($_SESSION['company'])) {
        $clientId = $_SESSION['company']['nit'];
        $docType = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : 'NT';
      }
      else {
        return "no se ha seleccionado ninguna empresa";
      }

      $data = [];
      // Get data fixed.
      if ($_SESSION['company']['environment'] == 'both' || $_SESSION['company']['environment'] == 'fijo') {
        // Get client data
        // Change document type.
        switch (strtoupper($docType)) {
          case 'NIT':
            $docType = 'NT';
        }

        // Parameters for service.
        $params['tokens'] = [
          'documentType' => $docType,
          'documentNumber' => $clientId,
        ];

        $response = $this->api->getByAccountUsingCustomer($params);

        if ($response) {
          foreach ($response as $key => $category) {
            if ($category->productId != 1) {
              $data[$category->productId] = [
                'productId' => $category->productId,
                'productName' => $category->productName,
              ];
            }
          }
        }
      }

      if ($_SESSION['company']['environment'] == 'both' || $_SESSION['company']['environment'] == 'movil') {
        // Another
        // Get data from WS getContractInformationMobile, use the apiClient.
        $params = [];
        $params['query'] = [
          'documentNumber' => $clientId,
          'documentType' => isset($_SESSION['company']['docType']) ? strtoupper($_SESSION['company']['docType']) : '',
        ];

        // No generate exception.
        $params['no_exception'] = TRUE;

        // Consultamos si la cuenta tiene servicio de movil.
        $response_movil = $this->api->getContractInformation($params);

        if ($response_movil) {
          $data["-1"] = [
            'productId' => "-1",
            'productName' => 'Movil',
          ];
        }
      }

      $this->collections = $data;
    }
    return $this->collections;
  }

  /**
   * @param $category
   * @return mixed
   */
  public function getCategory($category) {
    $categories = $this->getCategories();
    $portfolio = $this->getPortfolioByContractId();
    foreach ($categories as $key => $c) {
      if ($key === $category && array_key_exists($c['parameter'], $portfolio)) {
        return $c;
      }
    }
  }

}
