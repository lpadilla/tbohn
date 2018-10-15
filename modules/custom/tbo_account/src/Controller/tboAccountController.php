<?php

namespace Drupal\tbo_account\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 *
 */
class tboAccountController extends ControllerBase {
  
  protected $service_controller;
  
  /**
   * TboAccountController constructor.
   */
  public function __construct() {
    $this->user = \Drupal::currentUser();
    $this->service_controller = \Drupal::service('tbo_account.tbo_account_controller_service');
  }
  
  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function autocomple_enterprises(Request $request) {
    // $enterprises = new \stdClass();
    $saveDara = [];
    $query = db_select('company_entity_field_data')
      ->fields('company_entity_field_data', ['company_name'])
      ->condition('company_entity_field_data.user_id', $this->user->id())
      ->execute();
    foreach ($query as $key => $value) {
      $saveDara[$key] = $value->company_name;
    }
    // $enterprises = $saveDara;.
    return new JsonResponse($saveDara);
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
    return $this->service_controller->manageCompanyMessageConfirm($type, $clientId, $name, $pathname, $state, $confirm);
  }
  
  /**
   * Implements function enableDisableTigoUser for save log.
   *
   * @param $message
   * @param $status
   */
  public function enableDisableTigoUser($button, $type, $pathname, $url_config) {
    return $this->service_controller->enableDisableTigoUser($button, $type, $pathname, $url_config);
  }
  
  /**
   * Function to validated user invited.
   *
   * @param $token
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function invitedUser($token, $login) {
    // Validated exist user.
    if (\Drupal::currentUser()->id() || empty($token)) {
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    
    if ($login == TRUE) {
      // Set sesion var and redirect.
      $_SESSION['guest_user'] = $token;
      $account = $this->getDataAccountTemp($token);
      $_SESSION['mail_invited'] = $account['mail'];
      return new RedirectResponse(\Drupal::url('tigoid.login.handler'));
    }
    
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    $account = $this->getDataAccountTemp($token);
    if (empty($account)) {
      drupal_set_message('Esta Url de invitado ya ha sido utilizada o el usuario no existe.');
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    
    $data = [
      'type' => 'invited',
      'name' => strtoupper($account['name']),
      'name_company' => $account['company_name'],
      'token' => $token,
    ];
    
    // Add data to twig.
    $twig = \Drupal::service('twig');
    $twig->addGlobal('data_invited', $data);
    
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [
      'token' => $token,
    ];
    $plugin_block = $block_manager->createInstance('company_invited_block', $config);
    
    $render = $plugin_block->build();
    return $render;
  }
  
  /**
   *
   */
  public function getDataAccountTemp($token) {
    $database = \Drupal::database();
    $query = $database->select('invitation_access_entity_field_data', 'invitation');
    $query->leftJoin('company_entity_field_data', 'company', 'invitation.company_id = company.id');
    $query->addField('invitation', 'user_name', 'name');
    $query->addField('invitation', 'mail');
    $query->addField('company', 'name', 'company_name');
    $query->condition('invitation.token', $token);
    
    $response = $query->execute()->fetchAssoc();
    
    return $response;
  }
  
  /**
   *
   */
  public function verifiedUser() {
    if (!isset($_SESSION['email_verified'])) {
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    
    $account = $this->getDataAccountTempEmail($_SESSION['email_verified']['email']);
    drupal_set_message(t("Bienvenido @name, La cuenta de su empresa @company_name se ha creado con éxito", [
        '@company_name' => $account['company_name'],
        '@name' => $_SESSION['email_verified']['name'],
      ]
    ), 'status');
    $data = [
      'type' => 'send_verified',
      'email' => $_SESSION['email_verified']['email'],
    ];
    
    unset($_SESSION['email_verified']);
    
    // Add data to twig.
    $twig = \Drupal::service('twig');
    $twig->addGlobal('data_invited', $data);
    
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance('company_invited_block', $config);
    
    $render = $plugin_block->build();
    return $render;
  }
  
  /**
   *
   */
  public function getDataAccountTempEmail($email) {
    $database = \Drupal::database();
    $query = $database->select('invitation_access_entity_field_data', 'invitation');
    $query->leftJoin('company_entity_field_data', 'company', 'invitation.company_id = company.id');
    $query->addField('invitation', 'user_name', 'name');
    $query->addField('invitation', 'mail');
    $query->addField('company', 'name', 'company_name');
    $query->condition('invitation.mail', $email);
    
    $response = $query->execute()->fetchAssoc();
    
    return $response;
  }
  
}
