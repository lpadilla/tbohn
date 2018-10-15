<?php

namespace Drupal\tbo_account\Services\Rest;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\user\Entity\User;

/**
 * Class ReAssignBusinessBetweenTigoAdminRestService.
 *
 * @package Drupal\tbo_account\Services\Rest
 */
class ReAssignBusinessBetweenTigoAdminRestService {

  protected $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param string $tigoadmin
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser, $tigoadmin = '') {
    $this->currentUser = $currentUser;
    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    if ($tigoadmin) {
      if (!$this->currentUser->hasPermission('access list company')) {
        throw new AccessDeniedHttpException();
      }

      $userTigoAdmin = User::load($tigoadmin);

      if (!($userTigoAdmin->isActive() && $userTigoAdmin->hasRole('tigo_admin'))) {
        throw new HttpException(t('TigoAdmin wasn\'t provided'));
      }

      try {

        $request = $_GET;
        if (isset($_GET['q'])) {
          $q = $_GET['q'];
        }

        $filters = $request;
        unset($filters['_format']);
        unset($filters['config_columns']);
        unset($filters['config_name']);
        if (isset($q)) {
          unset($filters['q']);
        }

        $config_name = $request['config_name'];

        // Get columns table.
        $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_account');
        $columns_table = $tempstore->get($config_name . $request['config_columns']);
        $config_paginate = $tempstore->get($config_name . '_pager' . $request['config_columns']);

        $fields_table = $columns_table;

        unset($columns_table['reasignar']);
        unset($columns_table['reasignar_a']);

        // Get repository.
        $account_repository = \Drupal::service('tbo_account.repository');

        $others_field = [
          'company' => [
            'id',
          ],
        ];

        $data = $account_repository->getQueryCompanies($filters, $columns_table, $config_paginate, $others_field, $tigoadmin);

        $data2 = [];
        foreach ($data as $key => $content) {
          if (isset($data2[$content->id]) && isset($content->full_name)) {
            if (strpos($data2[$content->id]['full_name'], $content->full_name) === FALSE) {
              $data2[$content->id]['full_name'] .= ', ' . $content->full_name;
            }
          }
          else {
            $data2[$content->id] = [
              'full_name' => $content->full_name,
              'name' => $content->name,
            ];
          }
        }

        $aux_data = [];
        foreach ($data2 as $key => $valor) {
          $arreglo = [];
          foreach (array_keys($fields_table) as $llave) {
            if ($llave == 'user_name') {
              $llave = 'full_name';
            }
            if ($llave == 'reasignar') {
              $arreglo['reasignar'] = $key;
            }
            elseif ($llave == 'reasignar_a') {
              $arreglo['reasignarA'] = $key;
            }
            else {
              $arreglo[$llave] = $valor[$llave];
            }

          }
          $aux_data[] = $arreglo;
        }

        $rta = [
          'lista' => $aux_data,
          'num' => $account_repository->getNumEnterprisesByTigoAdmin($tigoadmin),
        ];

        if ($rta['num'] == 0) {
          $rta['message'] = t('No se encontró información relacionada');
        }
        return new ResourceResponse($rta);
      }
      catch (\Exception $e) {
        return new ResourceResponse(UtilMessage::getMessage($e));
      }

    }

    throw new HttpException(t('TigoAdmin wasn\'t provided'));
  }

  /**
   * Responds to POST requests.
   * calls create method.
   *
   * @param $tigoadmin
   * @param $data
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post($tigoadmin, $data) {
    try {
      $servicio = \Drupal::service('tbo_account.companies_list_tigoadmin');

      $response = $servicio->updateTigoCompanies($tigoadmin, $data);

      if ($response) {
        drupal_set_message($response);
        return new ResourceResponse($response);
      }
    }
    catch (\Exception $e) {
      $message = UtilMessage::getMessage($e);
      drupal_set_message($message['message'], 'error');
      return new ResourceResponse($message);
    }
    return new ResourceResponse([]);
  }

}
