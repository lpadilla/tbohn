<?php

namespace Drupal\adf_import_data\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 */
class GetImportLogicService {

  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(AccountProxyInterface $currentUser) {

    $this->currentUser = $currentUser;

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Get import results.
    $finish = \Drupal::config('adf_import_data.adfimportdataformconfig')->get('import_finish');
    $success_val = \Drupal::config('adf_import_data.adfimportdataformconfig')->get('import_success');
    $fail_val = \Drupal::config('adf_import_data.adfimportdataformconfig')->get('import_fail');

    $success = (empty($success_val)) ? '0 ' . t(' registros exitosos') : $success_val . ' ' . t('registros exitosos');
    $fail = (empty($fail_val)) ? '0 ' . t('registros fallidos') : $fail_val . ' ' . t('registros fallidos');
    $data = [
      'finish' => ($finish == NULL) ? 0 : $finish,
      'record_success' => $success,
      'record_fail' => $fail,
    ];

    return new ResourceResponse($data);
  }

}
