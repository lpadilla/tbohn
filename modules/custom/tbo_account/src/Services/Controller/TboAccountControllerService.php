<?php

namespace Drupal\tbo_account\Services\Controller;

use Drupal\Core\Url;
use Masterminds\HTML5\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class TboAccountControllerService.
 *
 * @package Drupal\tbo_account\Services\Controller
 */
class TboAccountControllerService {

  protected $user;
  protected $name;
  protected $repository;

  /**
   *
   */
  public function __construct() {
    $this->repository = \Drupal::service('tbo_account.repository');
  }

  /**
   * Implements function manageCompanyMessageConfirm for generate type of message in modal.
   *
   * @param $type
   * @param $clientId
   * @param $name
   * @param $pathname
   * @param $state
   * @param $confirm
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function manageCompanyMessageConfirm($type, $clientId, $name, $pathname, $state, $confirm) {
    $this->name = str_replace('|', ' ', $name);
    if ($type == 'changeState') {
      $this->changeState($type, $clientId, $name, $pathname, $state, $confirm);
    }
    elseif ($type == 'deleteCompany') {
      $this->deleteCompany($type, $clientId, $name, $pathname, $confirm);
    }

    return new RedirectResponse(Url::fromUri('internal:/' . $pathname)->toString());
  }

  /**
   * Implements function changeState for active or inactive company.
   *
   * @param $type
   * @param $clientId
   * @param $name
   * @param $pathname
   * @param $state
   * @param $confirm
   *
   * @return bool
   *
   * @throws \Masterminds\HTML5\Exception
   */
  public function changeState($type, $clientId, $name, $pathname, $state, $confirm) {
    if ($confirm) {
      $response = $this->repository->changeStatusCompany($state, $clientId);
      $final_state = 'desactivada';
      $response_state = 'desactivar';
      $log_state = 'desactivó';
      if ($state == 1) {
        $final_state = 'activada';
        $response_state = 'activar';
        $log_state = 'activó';
      }

      if ($response) {
        // Clear cache.
        $this->clearCacheEntity($clientId);
        $this->_saveManageCompanyLog($log_state, $this->name);
        drupal_set_message('La empresa ' . $this->name . ' fue ' . $final_state);
      }
      else {
        drupal_set_message('No se ha podido ' . $response_state . ' la empresa ' . $this->name . ' Verifique con el administrador');
      }

      return TRUE;
    }

    $request = \Drupal::request();
    // If the form hasn't been sent via ajax, we redirect exception.
    if (!$request->isXmlHttpRequest()) {
      throw new Exception("Error de acceso");
    }

    // Prepare vars for twig.
    $message = t('¿Confirma la desactivación de la empresa?');
    $button = 'Desactivar';
    $state_change = 0;
    if ($state == 'true') {
      $message = t('¿Confirma la activación de la empresa?');
      $button = 'Activar';
      $state_change = 1;
    }

    // Load Service twig.
    $twig = \Drupal::service('twig');

    // Load template for change state.
    $template = $twig->loadTemplate(drupal_get_path('module', 'tbo_account') . '/templates/block--manage-account-message.html.twig');

    // Render template and variables.
    echo $template->render([
      'message' => $message,
      'clientId' => $clientId,
      'name' => $name,
      'pathname' => $pathname,
      'state_change' => $state_change,
      'button' => $button,
      'type' => $type,
    ]);

    // Closet load.
    die;
  }

  /**
   * @param $clientId
   */
  public function clearCacheEntity($clientId) {
    // Get id company.
    $cid = $this->repository->getCompanyToDocumentNumber($clientId);

    $deleteCompany = $this->repository->deleteCacheEntityCompany($cid);
  }

  /**
   * Implements function _saveManageCompanyLog for save log.
   *
   * @param $log_state
   */
  public function _saveManageCompanyLog($log_state, $name_company) {
    // Save Audit log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();

    // Create array data[].
    $data = [
      'companyName' => '',
      'companyDocument' => '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Cuenta',
      'description' => 'Usuario ' . $log_state . ' empresa',
      'details' => 'Usuario ' . $name . ' ' . $log_state . ' la empresa ' . $name_company,
    ];

    // Save audit log.
    $service_log->insertGenericLog($data);
  }

  /**
   * Implements function deleteCompany for delete company and users.
   *
   * @param $type
   * @param $clientId
   * @param $name
   * @param $pathname
   * @param $confirm
   *
   * @return bool
   *
   * @throws \Masterminds\HTML5\Exception
   */
  public function deleteCompany($type, $clientId, $name, $pathname, $confirm) {
    if ($confirm) {
      // Get id company.
      $cid = $this->repository->getCompanyToDocumentNumber($clientId);

      // Load Users without roles $roles.
      $roles = ['administrator', 'super_admin', 'tigo_admin', 'admin_company', 'admin_grupo'];
      // Load Users without roles $roles.
      $data = $this->repository->loadUserWithoutRolByCompany($cid, $roles);

      // $response = $this->repository->deleteEntityCompany($cid);
      $entityCompany = \Drupal::entityTypeManager()
        ->getStorage('company_entity')->load($cid);

      if ($entityCompany) {
        try {
          $entityCompany->delete();

          // Delete users without roles $roles.
          foreach ($data as $uid => $data) {
            $dataValidate = $this->repository->loadUserInAnotherCompany($data->users, $cid);
            if (empty($dataValidate)) {
              // Delete the user.
              user_delete($data->users);
            }
          }

          // Delete register from company_user_relations_field_data.
          $this->_deleteUsersRelationsCompany($cid);
          // Save audit log.
          $this->_saveManageCompanyLog('eliminó', $this->name);
          // Set message.
          drupal_set_message('La empresa ' . $this->name . ' fue eliminada');
        }
        catch (\Exception $e) {
          drupal_set_message('No se ha podido eliminar la empresa ' . $this->name . ' Verifique con el administrador', 'error');
        }
      }
      return TRUE;
    }
    $request = \Drupal::request();
    // If the form hasn't been sent via ajax, we redirect exception.
    if (!$request->isXmlHttpRequest()) {
      throw new Exception("Error de acceso");
    }

    // Prepare vars.
    $message = '¿Está seguro que desea eliminar empresa ' . $this->name . ' Esta acción no se puede deshacer';

    // Load Service twig.
    $twig = \Drupal::service('twig');

    // Load template for change state.
    $template = $twig->loadTemplate(drupal_get_path('module', 'tbo_account') . '/templates/block--manage-account-message.html.twig');

    // Render template and variables.
    echo $template->render([
      'message' => $message,
      'clientId' => $clientId,
      'name' => $this->name,
      'pathname' => $pathname,
      'state_change' => 0,
      'button' => t('Eliminar'),
      'type' => $type,
    ]);

    // Closet load.
    die;
  }

  /**
   * Implements function _deleteUsersCompany for delete company users.
   *
   * @param $cid
   */
  public function _deleteUsersRelationsCompany($cid) {
    // Load registers company in company_user_relations_field_data.
    $dataIds = $this->repository->getUsersRelationsCompany($cid);

    // Scroll through records and delete data.
    foreach ($dataIds as $cidd => $value) {
      // Delete registers to enterprise in table.
      $entityRelationsCompany = \Drupal::entityTypeManager()
        ->getStorage('company_user_relations')->load($value->id);
      $entityRelationsCompany->delete();
    }
  }

  /**
   * Implements function enableDisableTigoUser for save log.
   *
   * @param $message
   * @param $status
   */
  public function enableDisableTigoUser($button, $type, $pathname, $url_config) {

    $message = t('El usuario Tigo Admin que desea desactivar tiene empresas asociadas a él. Para desactivarlo debe reasignar las empresas a otro usuario Tigo Admin. ¿Desea reasignar las empresas a otro usuario Tigo admin?');
    $message = str_replace('-', ' ', $message);
    if ($type == 'showDisableMessage') {
      $this->showDisableMessage($message, $button, $url_config);
    }

    return new RedirectResponse('/' . $pathname);

  }

  /**
   * Implements function showDisableMessage for save log.
   *
   * @params $message
   * @params $button
   * @params $url_config
   */
  public function showDisableMessage($message, $button, $url_config) {
    $twig = \Drupal::service('twig');

    $template = $twig->loadTemplate(drupal_get_path('module', 'tbo_account') . '/templates/block--manage-tigo-account-message.html.twig');
    $url_config = str_replace('.', '/', $url_config);

    // Render template and variables.
    echo $template->render([
      'message' => $message,
      'button' => $button,
      'url_config' => $url_config,
    ]);

    die;
  }

}
