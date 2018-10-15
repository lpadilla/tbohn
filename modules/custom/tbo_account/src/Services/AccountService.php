<?php

namespace Drupal\tbo_account\Services;

use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class AccountService.
 *
 * @package Drupal\tbo_account\Services
 */
class AccountService {

  protected $api;

  /**
   *
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * @param $category
   * @return mixed
   */
  public function getNameCompany($params) {
    $company = $this->api->customerByCustomerId($params);
    return $company;
  }

}
