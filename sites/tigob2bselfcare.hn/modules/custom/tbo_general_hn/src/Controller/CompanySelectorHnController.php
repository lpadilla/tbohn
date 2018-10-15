<?php
namespace Drupal\tbo_general_hn\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\user\Entity\User;

use Drupal\tbo_general\Controller\CompanySelectorController;

/**
 * Class CompanySelectorHnController.
 *
 * @package Drupal\tbo_general_hn\Controller
 */
class CompanySelectorHnController extends CompanySelectorController {
  /**
   * Companyselector.
   *
   * @return string
   *   Return Hello string.
   */
  public function companySelector($uid) {
  	
    \Drupal::service('page_cache_kill_switch')->trigger();
    //if $uid == 0 render the selector company block
    if ($uid == 0) {
      $companies = $this->getCompaniesUser();
      if (empty($companies)) {
        unset($_SESSION['company']);
        if (isset($_SESSION['user_login_in'])) {
          //Save log
          $this->saveInitSesionLog();
          unset($_SESSION['user_login_in']);
        }
        return new RedirectResponse(Url::fromUri('internal:/' . '<front>')
          ->toString());
      }
      if (count($companies) == 1) {
        //Set sesion var company
        $company = [];
        foreach ($companies as $data) {
          $company['id'] = $data->company_id;
          $company['name'] = $data->name;
          $company['nit'] = $data->nit;
          $company['docType'] = $data->document_type;
          $company ['company_code'] = $data->company_code;
          if ($data->fixed == 1 && $data->mobile == 0) {
            $company['environment'] = 'fijo';
          }
          if ($data->fixed == 0 && $data->mobile == 1) {
            $company['environment'] = 'movil';
          }
          if ($data->fixed == 1 && $data->mobile == 1) {
            $company['environment'] = 'both';
          }
          if($data->fixed == 0 && $data->mobile == 0){
            $company['environment'] = 'movil';
          }
        }
        $_SESSION['company'] = $company;
        $_SESSION['adf_segment']['user']['others']['custom'] = [
          'nit' => $company['docType'],
          'enterpriseID' => $company['nit'],
          'enterpriseName' => $company['name'],
          'businessUnit' => $company['environment'],
          'companyCode' => $company['company_code'], //added BLVE
        ];
        $_SESSION['adf_segment']['send_services'] = 0;
        if (isset($_SESSION['user_login_in'])) {
          //Save log
          $this->saveInitSesionLog($company['name'], $company['nit']);
          unset($_SESSION['user_login_in']);
        }
        //redirect
        return new RedirectResponse(Url::fromUri('internal:/' . '<front>')->toString());
      }
      //Create var sesion for validate selector
      //Load Service twig
      $twig = \Drupal::service('twig');
      $twig->addGlobal('show_selector', TRUE);
      $block_manager = \Drupal::service('plugin.manager.block');
      $config = [];
      $plugin_block = $block_manager->createInstance('company_selector_block', $config);
      // Some blocks might implement access check.
      $access_result = $plugin_block->access(\Drupal::currentUser());
      // Return empty render array if user doesn't have access.
      if (!$access_result) {
        return [
          '#type' => 'markup',
          '#markup' => $this->t('No posee los permisos necesarios para acceder a este bloque'),
        ];
      }
      $render = $plugin_block->build();
      return $render;
    }
    else {
      $query = \Drupal::database()->select('company_entity_field_data', 'company');
      $query->addField('company', 'name');
      $query->addField('company', 'document_number');
      $query->addField('company', 'document_type');
      $query->addField('company', 'segment');
      $query->addField('company', 'fixed');
      $query->addField('company', 'mobile');
      $query->addField('company', 'company_code');
      $query->condition('company.id', $uid);
      $companies = $query->execute()->fetchAll();
      $company = [];
      $company ['id'] = $uid;
      $company ['name'] = $companies[0]->name;
      $company ['nit'] = $companies[0]->document_number;
      $company ['docType'] = $companies[0]->document_type;
      $company ['segment'] = $companies[0]->segment;
      $company ['company_code'] = $companies[0]->company_code;

      if ($companies[0]->fixed == 1 && $companies[0]->mobile == 0) {
        $company['environment'] = 'fijo';
      }
      else if ($companies[0]->fixed == 0 && $companies[0]->mobile == 1) {
        $company['environment'] = 'movil';
      }
      else if ($companies[0]->fixed == 1 && $companies[0]->mobile == 1) {
        $company['environment'] = 'both';
      }else{
        $company['environment'] = 'movil';
      }
      $_SESSION['company'] = $company;
      $_SESSION['adf_segment']['user']['others']['custom'] = [
        'enpterpriseTypeID' => $company['docType'],
        'enterpriseID' => $company['nit'],
        'enterpriseName' => $company['name'],
        'businessUnit' => $company['environment'],
        'companyCode' => $company['company_code'], //added BLVE
      ];
      $_SESSION['adf_segment']['send_services'] = 0;
      //Remove var sesion selector
      unset($_SESSION['show_selector']);
      //Load Service twig
      $twig = \Drupal::service('twig');
      $twig->addGlobal('show_selector', FALSE);
      //Save audit log
      if (isset($_SESSION['user_login_in'])) {
        //Save log
        $this->saveInitSesionLog($company['name'], $company['nit']);
      }
      //Return to home
      return new RedirectResponse(Url::fromUri('internal:/' . '<front>')->toString());
    }
  }
  
  
  public function getCompaniesUser() {
    $service = \Drupal::service('masquerade');
    $uid = \Drupal::currentUser()->id();
    $query = \Drupal::database()
      ->select('company_user_relations_field_data', 'userCompany');
    $query->join('company_entity_field_data', 'company', 'userCompany.company_id = company.id');
    $query->join('users_field_data', 'user', 'userCompany.users = user.uid');
    if (isset($_SESSION['masquerading'])) {
      $account = User::load($_SESSION['old_user']);
      $roles = $account->getRoles();
      if (in_array('tigo_admin', $roles)) {
        $query->condition('userCompany.associated_id', $_SESSION['old_user']);
      }
    }
    $query->condition('userCompany.users', $uid);
    $query->addField('userCompany', 'name');
    $query->addField('userCompany', 'company_id');
    $query->addField('user', 'mail');
    $query->addField('company', 'document_number', 'nit');
    $query->addField('company', 'document_type');
    $query->addField('company', 'fixed');
    $query->addField('company', 'mobile');
    $query->addField('company', 'company_code');
    $companies = $query->execute()->fetchAll();
    return $companies;
  }
  
  
  /**
   * Implements function _saveManageCompanyLog for save log.
   *
   * @param string $company_name
   *   Company name.
   * @param string $company_nit
   *   Company document number.
   */
  public function saveInitSesionLog($company_name = '', $company_nit = '') {
  	$service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    $detalles = 'Usuario ' . $service->getName() . ' ha iniciado sesion.';
    // Validate Admin empresa.
    //if ($this->hasRole('admin_company')) {
    
    $isAC = false;
    $isAC = in_array('admin_company', \Drupal::currentUser()->getRoles());
    
    if ($isAC) {
      $detalles = 'Usuario ' . $service->getName() . ' a iniciado sesion administrando la empresa ' . $company_name;
    }
    // Create array data[].
    $data = [
      'companyName' => isset($company_name) ? $company_name : '',
      'companyDocument' => isset($company_nit) ? $company_nit : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Usuario',
      'description' => 'Sesion abierta para el usuario ' . $service->getName(),
      'details' => $detalles,
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];
    // Save audit log.
    //kint($data);
    $service->insertGenericLog($data);
  }
  
}