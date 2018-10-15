<?php

namespace Drupal\tbo_account_hn\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\tbo_account;


/**
 * Class CreateCompaniesRestServiceHn.
 *
 * @package Drupal\tbo_account_hn\Services\Rest
 */
class CreateCompaniesRestServiceHn {

  private $currentUser;

  /**
   * @param AccountProxyInterface $currentUser
   * @return ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();
    $data2 = [];

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access list company')) {
      throw new AccessDeniedHttpException();
    }

    //Get repository
    $account_repository = \Drupal::service('tbo_account.repository');
    $account_hn_repository = \Drupal::service('tbo_account_hn.repository');

    $request = $_GET;
    if(isset($_GET['q'])){
      $q = $_GET['q'];
    }

    if(isset($_GET['autocomplete'])){
      if($_GET['autocomplete']) {
        try {
          $data = $account_repository->getAutocompleteCompanies($_GET['autocomplete']);
        }
        catch (\Exception $e) {
          return new ResourceResponse(UtilMessage::getMessage($e));
        }
        foreach ($data as $key => $content) {
          array_push($data2, (array) $content);
        }
        return new ResourceResponse($data2);
      }
    }

    $filters = $request;
    unset($filters['_format']);
    unset($filters['config_columns']);
    unset($filters['config_name']);
    if (isset($q)) {
      unset($filters['q']);
    }

    $config_name = $request['config_name'];

    //Get columns table
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
    $columns_table = $tempstore->get($config_name . $request['config_columns']);
    $config_paginate = $tempstore->get($config_name . '_pager' . $request['config_columns']);

    try {
      $others_field = [
        'user' => [
          'phone_number',
          'mail',
        ]
      ];
      $data = $account_hn_repository->getQueryCompaniesHn($filters, $columns_table, $config_paginate, $others_field);
      $exists_username = FALSE;
      $name_value = $mail_value = $phone_value = '';
      /**
       * el valor del nombre del admin es concatenado
       */
      foreach ($data as $key => $value ){
        foreach ($value as $key2 => $value2){
          if($key2 == 'full_name'){
            $exists_username = TRUE;
            $name_value = $value2;
          }elseif ($key2 == 'mail'){
            $mail_value = $value2;
            unset($data[$key]->mail);
          }elseif ($key2 == 'phone_number'){
            $phone_value = $value2;
            unset($data[$key]->phone_number);
          }
        }
        if ($exists_username) {
          $data[$key]->full_name = [$name_value, $mail_value, $phone_value];
        }
      }
    }
    catch (\Exception $e) {
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    foreach ($data as $key => $content) {
      array_push($data2, (array) $content);
    }

    return new ResourceResponse($data2);
  }
}