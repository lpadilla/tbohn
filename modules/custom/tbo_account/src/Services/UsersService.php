<?php

namespace Drupal\tbo_account\Services;

use Drupal\user\Entity\User;
use Drupal\Core\Database\Query\Condition;

/**
 * Class UsersService.
 *
 * @package Drupal\tbo_account\Services
 */
class UsersService {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   *
   */
  public function getAllUsers() {
    return 0;
  }

  /**
   * @param $params
   * @return mixed
   */
  public function getUsersByFilter($params) {

    // Get filters.
    if (isset($params['filters'])) {
      $filters = $params['filters'];
      unset($params['filters']);
    }
    else {
      $filters = NULL;
    }

    // Get table columns.
    $fields = $params['fields'];
    unset($params['fields']);

    // Get pager.
    $pager = $params['pager'];
    unset($params['pager']);
    unset($params);

    $database = \Drupal::database();
    $query = $database->select('company_user_relations_field_data', 'compUserRole');
    $query->distinct();
    $query->innerJoin('company_entity_field_data', 'company', 'compUserRole.company_id = company.id');
    $query->innerJoin('users_field_data', 'user', 'user.uid = compUserRole.users');

    /**
     * filter logs by user role
     */
    $account = \Drupal::currentUser();

    $role = '';
    if ($account->getRoles(TRUE)) {
      $roles = $account->getRoles();
      if (in_array('administrator', $roles) || in_array('super_admin', $roles)) {

      }
      elseif (in_array('admin_company', $roles) || in_array('tigo_admin', $roles)) {
        $role = ['admin_company'];
        if (in_array('tigo_admin', $roles)) {
          $role = ['tigo_admin'];
        }

        /**
         * filter logs by current user company
         */
        // Current user could have many companies.
        $companies = $this->getCompaniesByEntities($account->id(), $role);

        if (!empty($companies)) {
          $or = new Condition('OR');
          foreach ($companies as $key => $valor) {
            $or->condition('company.id', $key, '=');
          }
          $query->condition($or);

          if (in_array('admin_company', $role)) {
            $query->join('user__roles', 'roles', 'compUserRole.users = roles.entity_id');
            $query->condition('roles.roles_target_id', 'tigo_admin', '<>');
            $query->condition('roles.roles_target_id', 'administrator', '<>');
            $query->condition('roles.roles_target_id', 'super_admin', '<>');
          }
        }
        else {
          // Si no hay asociaciones de empresa para el rol retornar el query vacio.
          return "";
        }
      }
      else {
        return "";
      }
    }

    $getRoles = FALSE;
    $getCompanies = FALSE;
    // Add fields to query.
    if (count($fields) > 0) {
      foreach ($fields as $key => $data) {
        if ($data['type'] == 'role' || $data['type'] == 'company') {
          $query->addField('user', 'uid');
        }
        if ($data['type'] == 'role') {
          // Los roles se obtienen posteriormente.
          $getRoles = TRUE;
        }
        elseif ($data['type'] == 'company') {
          // Las empresas se obtienen posteriormente.
          $getCompanies = TRUE;
        }
        else {
          // TODO quitar el if-else cuando se traiga el grupo de la bd.
          if ($data['type'] != 'group') {
            $query->addField($data['type'], $data['service_field']);
          }
          else {
            $default_group = '';
          }
        }
      }
    }

    /**
     * filter logs by user's filters
     */
    if ($filters != NULL) {
      foreach ($filters as $condition => $value) {
        if (isset($condition) && isset($value)) {
          if ($condition == 'user_role' && $value) {
            $condition = 'roles_target_id';
            $query->innerJoin('user__roles', 'role', 'role.entity_id = user.uid');
          }

          if ($condition == 'document_number') {
            // Evita ambiguedad.
            $condition = 'user.document_number';
          }
          if ($condition == 'document_type') {
            // Evita ambiguedad.
            $condition = 'user.document_type';
          }
          $query->condition($condition, '%' . $value . '%', 'LIKE');
        }
      }
    }

    // Add limit to query.
    if (!empty($pager['pages']) && !empty($pager['page_elements'])) {
      $query->range(0, $pager['pages'] * $pager['page_elements']);
    }

    $result = $query->execute()->fetchAll();
    $users = [];
    $user = new \StdClass();

    // Se obtienen los tipos de documento de la base de datos.
    $documents = \Drupal::service('tbo_entities.entities_service');
    $options_service = $documents->getDocumentTypes();

    $documentTypes = [];
    foreach ($options_service as $key => $data) {
      $documentTypes[$data['id']] = $data['label'];
    }

    $roles = user_role_names(TRUE);

    /**
     * format to (key,value) to let angular iterate better
     */
    foreach ($result as $key => $value) {
      $usr_roles = NULL;
      $value = (array) $value;
      $usr_roles_names = [];
      foreach ($value as $key2 => $value2) {
        // Fix document_type.
        if ($key2 == 'document_type' && !empty($value2)) {
          $value2 = $documentTypes[$value2];
        }
        if ($getRoles && $key2 == 'uid') {
          $usuario = User::load($value2);
          $usr_roles = $usuario->getRoles(TRUE);
          foreach ($usr_roles as $urole) {
            $usr_roles_names[$urole] = $roles[$urole];
          }
          sort($usr_roles_names);
          $user->user_role = implode(', ', $usr_roles_names);
        }
        if ($getCompanies && $key2 == 'uid') {
          $usr_companies = $this->getCompaniesByEntities($value2, $usr_roles);

          sort($usr_companies);
          $user->company_name = $usr_companies;
        }
        else {
          if ($key2 == 'name') {
            $key2 = 'user_name';
          } // Facilita el ordenamiento de datos
          $user->$key2 = $value2;
        }
      }
      if (isset($default_group)) {
        $user->group_name = $default_group;
      }

      array_push($users, (array) $user);
    }

    // Sorting data of users by fields.
    $usersorted = [];
    foreach ($users as $key => $usuario) {
      $orden = [];
      foreach ($fields as $valor) {
        $orden[$valor['key']] = $usuario[$valor['key']];
      }
      array_push($usersorted, $orden);
    }
    $set_log = $this->saveQueryAuditLog();

    return $usersorted;
  }

  /**
   * @param $params
   * @return mixed
   */
  public function getUsersByFilterNew($params) {

    // Set vars.
    $filters = $pager = [];

    // Set filters.
    if (isset($params['filters'])) {
      $filters = $params['filters'];
    }

    // Set pager.
    if (isset($params['pager'])) {
      $pager = $params['pager'];
    }

    // Get table columns.
    $fields = $params['fields'];

    // Get company id.
    $company_id = isset($_SESSION['company']['id']) ? $_SESSION['company']['id'] : '';

    // Init vars.
    $getRoles = $getCompanies = $group = FALSE;

    $result = \Drupal::service('tbo_account.repository')->getUsersByCompanyWithFilterAndConditions($company_id, $fields, $filters, $pager, $getRoles, $getCompanies, $group);

    $users = [];
    $user = new \StdClass();

    // Se obtienen los tipos de documento de la base de datos.
    $documents = \Drupal::service('tbo_entities.entities_service');
    $options_service = $documents->getDocumentTypes();

    $documentTypes = [];
    foreach ($options_service as $key => $data) {
      $documentTypes[$data['id']] = $data['label'];
    }

    $roles = user_role_names(TRUE);

    /**
     * format to (key,value) to let angular iterate better
     */
    foreach ($result as $key => $value) {
      $usr_roles = NULL;
      $value = (array) $value;
      $usr_roles_names = [];
      foreach ($value as $key2 => $value2) {
        // Fix document_type.
        if ($key2 == 'document_type' && !empty($value2)) {
          $value2 = $documentTypes[$value2];
        }
        if ($getRoles && $key2 == 'uid') {
          $usuario = User::load($value2);
          $usr_roles = $usuario->getRoles(TRUE);
          foreach ($usr_roles as $urole) {
            $usr_roles_names[$urole] = $roles[$urole];
          }
          sort($usr_roles_names);
          $user->user_role = implode(', ', $usr_roles_names);
        }
        else {
          if ($key2 == 'name') {
            $key2 = 'user_name';
          } // Facilita el ordenamiento de datos
          $user->$key2 = $value2;
        }
      }
      if (isset($default_group)) {
        $user->group_name = $default_group;
      }

      array_push($users, (array) $user);
    }

    // Save audit log.
    $this->saveQueryAuditLog();

    return $users;
  }

  /**
   *
   */
  public function getCompaniesByEntities($uid = NULL, $roles = []) {
    if (!isset($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    $database = \Drupal::database();
    $query = $database->select('company_user_relations_field_data', 'relations');
    $query->join('company_entity_field_data', 'company', 'relations.company_id = company.id');

    $query->fields('relations', ['company_id']);
    $query->fields('company', ['company_name']);

    if (in_array('administrator', $roles) || in_array('super_admin', $roles)) {
      return [];
    }
    elseif (in_array('tigo_admin', $roles)) {
      $query->condition('relations.associated_id', $uid);
    }
    else {
      $query->condition('users', $uid);
    }
    $companies = $query->execute()->fetchAllKeyed();

    return $companies;
  }

  /**
   * @return bool
   */
  public function saveQueryAuditLog() {
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    // Save Audit log.
    $service_log->loadName();
    $name = $service_log->getName();

    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Cuenta',
      'description' => t('Consulta listado de usuarios'),
      'details' => 'Usuario ' . $name . ' consulta el listado de usuarios',
    ];

    // Save audit log.
    $service_log->insertGenericLog($data);
  }

}
