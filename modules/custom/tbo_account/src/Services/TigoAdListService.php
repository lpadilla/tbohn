<?php

namespace Drupal\tbo_account\Services;

use Drupal\adf_core\Util\UtilMessage;

/**
 *
 */
class TigoAdListService {

  /**
   * $conecction to database.
   */
  protected $conecction;
  protected $log;
  protected $repository_account;
  /**
   * Get data User.
   */
  protected $user;

  /**
   * Construct \Drupal DB and Current User.
   */
  public function __construct() {
    $this->conecction = \Drupal::database();
    $this->user = \DrupaL::currentUser();
    $this->log = \Drupal::service('tbo_core.audit_log_service');
    $this->repository_account = \Drupal::service('tbo_account.repository');
  }

  /**
   * Implements disableAdmin().
   *   Enable - Disable TigoAdmin.
   *
   * @param $params
   *
   * @return array|string
   */
  public function disableAdmin($params) {
    $userName = $params['name'];

    $params['status'] = (array_key_exists('status', $params)) ? $params['status'] : '';

    $status_value = ($params['status'] == 1) ? 0 : 1;
    $message = ($params['status'] == 1) ? t('ha sido desactivado') : t('ha sido activado');

    $response = ($params['status'] == 1) ? ['status' => 0] : ['status' => 1];

    // Execute (Change State)
    try {
      $r = $this->repository_account->changeStatusUserTigoAdmin($status_value, $params['disable_admin']);

      if ($r === 1) {
        drupal_set_message(t('Usuario %name %state correctamente', ['%name' => $userName, '%state' => $message]), 'status');

        $response['success'] = 'success';

        // Save Audit log.
        $this->log->loadName();

        // Create array data[].
        $data = [
          'event_type' => 'Cuenta',
          'description' => t('Usuario activó Tigo Admin'),
          'details' => t('El usuario @userName fue activado', ['@userName' => $userName]),
        ];

        if ($params['status'] == 1) {
          $data['description'] = t('Usuario desactivó Tigo Admin');
          $data['details'] = t('El usuario @userName fue desactivado',['@userName' => $userName]);
        }

        // Save audit log.
        $this->log->insertGenericLog($data);
      }
      else {
        drupal_set_message(t('Usuario %name no %state correctamente, por favor intente de nuevo', ['%name' => $userName, '%state' => $message]), 'status');
      }
    }
    catch (\Exception $e) {
      $message = UtilMessage::getMessage($e);
      drupal_set_message($message['message'], 'error');
      return $message;
    }

    return $response;

  }

  /**
   * Implements filterUserTigo().
   *
   * @param $params
   * @return array
   */
  public function filterUserTigo($params) {
    $response = [];

    // Get limit.
    $numberPages = isset($params['config_pager']['number_pages']) ? $params['config_pager']['number_pages'] : 0;
    $numberRowPages = isset($params['config_pager']['number_rows_pages']) ? $params['config_pager']['number_rows_pages'] : 0;
    $maxQuery = $numberPages * $numberRowPages;
    // Get data.
    try {
      $data = $this->repository_account->getTigoAdminUsers($maxQuery, $params['filters_data']);
      if (count($data) > 0) {
        foreach ($data as $key => $value) {
          $response[$key] = json_decode(\GuzzleHttp\json_encode($value), TRUE);
          // Add number of enterprises at final response.
          if (isset($params['fields']['companies'])) {
            $numCompany[$key] = $this->repository_account->getNumEnterprisesByTigoAdmin($response[$key]['uid']);
            $response[$key]['companies'] = $numCompany[$key];
          }
        }
      }
    }
    catch (\Exception $e) {
      return UtilMessage::getMessage($e);
    }

    return $response;
  }

  /**
   * Implements getTigoAdminUsers.
   *
   * @param $params
   *   Data for function.
   *
   * @return array
   */
  public function getTigoAdminUsers($params) {
    // Get limit.
    $numberPages = isset($params['config_pager']['number_pages']) ? $params['config_pager']['number_pages'] : 0;
    $numberRowPages = isset($params['config_pager']['number_rows_pages']) ? $params['config_pager']['number_rows_pages'] : 0;
    $maxQuery = $numberPages * $numberRowPages;

    // Get data.
    try {
      $results = $this->repository_account->getTigoAdminUsers($maxQuery);
    }
    catch (\Exception $e) {
      return UtilMessage::getMessage($e);
    }

    $numCompany = $responseIds = [];
    foreach ($results as $key => $value) {
      $results[$key] = json_decode(\GuzzleHttp\json_encode($value), TRUE);
      // Add number of enterprises at final response.
      if (isset($params['fields']['companies'])) {
        $numCompany[$key] = $this->repository_account->getNumEnterprisesByTigoAdmin($results[$key]['uid']);
        $results[$key]['companies'] = $numCompany[$key];
      }

      // Add assign_enterprise
      if (isset($params['fields']['assign_enterprise']) && isset($params['fields']['status'])) {
        if ($results[$key]['status'] == 1) {
          $results[$key]['assign_enterprise'] = t('Reasignar Empresa');
        }
        else {
          $results[$key]['assign_enterprise'] = ' ';
        }
      }
    }

    // Save Audit log.
    $this->log->loadName();
    $name = $this->log->getName();
    // Create array data[].
    $data = [
      'event_type' => 'Cuenta',
      'description' => t('Consulta listado de usuarios Tigo Admin'),
      'details' => t('Usuario @userName consulta el listado de Tigo Admin', ['@userName' => $name]),
    ];

    // Save audit log.
    $this->log->insertGenericLog($data);

    return $results;
  }

}
