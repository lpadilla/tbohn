<?php

namespace Drupal\tbo_general\Services\Controller;

use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Class CompanySelectorControllerClass.
 *
 * @package Drupal\tbo_general\Services\Controller
 */
class CompanySelectorControllerClass {

  /**
   * Companyselector.
   */
  public function companySelector($uid) {
    $val_enter_service = \Drupal::service('tbo_account.create_companies_service');
    // If $uid == 0 render the selector company block.
    if ($uid == 0) {
      $user = \Drupal::currentUser();
      $roles = $user->getRoles();
  
      if (in_array('tigo_admin', $roles) || in_array('super_admin', $roles)) {
        $result = [
          'type' => 'url',
          'data' => Url::fromUri('internal:/<front>')->toString(),
        ];
        return $result;
      }
      
      $companies = $this->getCompaniesUser();
      if (empty($companies)) {
        unset($_SESSION['company']);
        if (isset($_SESSION['user_login_in'])) {
          // Save log.
          $this->saveInitSesionLog();
          unset($_SESSION['user_login_in']);
        }

        // Save segment track to first login.
        if (isset($_SESSION['first_login'])) {
          unset($_SESSION['first_login']);
        }

        $result = [
          'type' => 'url',
          'data' => Url::fromUri('internal:/<front>')->toString(),
        ];
        return $result;
      }
      if (count($companies) == 1) {
        // Set sesion var company.
        $company = [];
        foreach ($companies as $data) {
          $company['id'] = $data->company_id;
          $company['name'] = $data->name;
          $company['nit'] = $data->nit;
          $company['docType'] = $data->document_type;
          $names = $val_enter_service->_validateCompanyInServices($company['docType'], $company['nit']);

          if (isset($names['name_fixed']) && !isset($names['name_mobile'])) {
            $company['environment'] = 'fijo';
          }
          if (!isset($names['name_fixed']) && isset($names['name_mobile'])) {
            $company['environment'] = 'movil';
          }
          if (isset($names['name_fixed']) && isset($names['name_mobile'])) {
            $company['environment'] = 'both';
          }
        }
        $_SESSION['company'] = $company;
        $_SESSION['atp_services'] = '';
        $_SESSION['adf_segment']['user']['others']['custom'] = [
          'nit' => $company['docType'],
          'enterpriseID' => $company['nit'],
          'enterpriseName' => $company['name'],
          'businessUnit' => $company['environment'],
        ];
        $_SESSION['adf_segment']['send_services'] = 0;
        if (isset($_SESSION['user_login_in'])) {
          // Save log.
          $this->saveInitSesionLog($company['name'], $company['nit']);
          unset($_SESSION['user_login_in']);
        }

        // Save segment track to first login.
        if (isset($_SESSION['first_login'])) {
          $environment = $company['environment'];
          if ($environment == 'both') {
            $environment = 'fijo - movil';
          }
          $event = 'TBO - Activación de Cuenta - Tx';
          $category = 'Ingreso Usuario Nuevo';
          $label = 'Exitoso - ' . $environment;
          \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
          unset($_SESSION['first_login']);
        }

        // Redirect.
        $result = [
          'type' => 'url',
          'data' => Url::fromUri('internal:/<front>')->toString(),
        ];
        return $result;
      }
      // Create var sesion for validate selector
      // Load Service twig.
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
          'type' => 'other',
          'data' => [
            '#type' => 'markup',
            '#markup' => $this->t('No posee los permisos necesarios para acceder a este bloque'),
          ],
        ];
      }

      $render = $plugin_block->build();

      return [
        'type' => 'other',
        'data' => $render,
      ];
    }
    else {
      // drupal_flush_all_caches();
      $query = \Drupal::database()
        ->select('company_entity_field_data', 'company');
      $query->addField('company', 'name');
      $query->addField('company', 'document_number');
      $query->addField('company', 'document_type');
      $query->addField('company', 'segment');
      $query->addField('company', 'fixed');
      $query->addField('company', 'mobile');
      $query->condition('company.id', $uid);
      $companies = $query->execute()->fetchAll();
      $company = [];
      $company['id'] = $uid;
      $company['name'] = $companies[0]->name;
      $company['nit'] = $companies[0]->document_number;
      $company['docType'] = $companies[0]->document_type;
      $company['segment'] = $companies[0]->segment;
      $names = $val_enter_service->_validateCompanyInServices($company['docType'], $company['nit']);

      if (isset($names['name_fixed']) && !isset($names['name_mobile'])) {
        $company['environment'] = 'fijo';
      }
      elseif (!isset($names['name_fixed']) && isset($names['name_mobile'])) {
        $company['environment'] = 'movil';
      }
      elseif (isset($names['name_fixed']) && isset($names['name_mobile'])) {
        $company['environment'] = 'both';
      }

      $_SESSION['company'] = $company;
      $_SESSION['atp_services'] = '';
      $_SESSION['adf_segment']['user']['others']['custom'] = [
        'enpterpriseTypeID' => $company['docType'],
        'enterpriseID' => $company['nit'],
        'enterpriseName' => $company['name'],
        'businessUnit' => $company['environment'],
      ];
      $_SESSION['adf_segment']['send_services'] = 0;
      // Remove var sesion selector.
      unset($_SESSION['show_selector']);
      // Load Service twig.
      $twig = \Drupal::service('twig');
      $twig->addGlobal('show_selector', FALSE);
      // Save audit log.
      if (isset($_SESSION['user_login_in'])) {
        // Save log.
        $this->saveInitSesionLog($company['name'], $company['nit']);
        // unset($_SESSION['user_login_in']);.
      }

      // Unset variable de servicios.
      unset($_SESSION['serviceDetail']);

      // Save segment track to first login.
      if (isset($_SESSION['first_login'])) {
        $environment = $company['environment'];
        if ($environment == 'both') {
          $environment = 'fijo - movil';
        }
        $event = 'TBO - Activación de Cuenta - Tx';
        $category = 'Ingreso Usuario Nuevo';
        $label = 'Exitoso - ' . $environment;
        \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
        unset($_SESSION['first_login']);
      }

      // Return to home.
      return [
        'type' => 'url',
        'data' => Url::fromUri('internal:/<front>')
          ->toString(),
      ];
    }
  }

  /**
   * Query to return companies.
   */
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
    $query->addField('company', 'name');
    $query->addField('userCompany', 'company_id');
    $query->addField('user', 'mail');
    $query->addField('company', 'document_number', 'nit');
    $query->addField('company', 'document_type');
    $query->addField('company', 'fixed');
    $query->addField('company', 'mobile');
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
    $detalles = 'Usuario ' . $service->getName() . ' ha iniciado sesión.';
    // Validate Admin empresa.
    if ($this->hasRole('admin_company')) {
      $detalles = 'Usuario ' . $service->getName() . ' a iniciado sesión administrando la empresa ' . $company_name;
    }
    // Create array data[].
    $data = [
      'companyName' => isset($company_name) ? $company_name : '',
      'companyDocument' => isset($company_nit) ? $company_nit : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Usuario',
      'description' => 'Sesión abierta para el usuario ' . $service->getName(),
      'details' => $detalles,
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
    ];
    // Save audit log.
    $service->insertGenericLog($data);
  }

  /**
   * Validate role.
   */
  public function hasRole($rid) {
    return in_array($rid, \Drupal::currentUser()->getRoles());
  }

}
