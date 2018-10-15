<?php

namespace Drupal\tbo_account\Services;

use Drupal\user\Entity\User;
use Drupal\Component\Utility\Crypt;
use Drupal\tbo_entities\Entity\InvitationAccessEntity;

/**
 * Declare MassiveImportEnterprisesService Class.
 */
class MassiveImportEnterprisesService {
  protected $serviceEnterprise;
  private $segment;

  /**
   * MassiveImportEnterprisesService constructor.
   */
  public function __construct() {
    $this->serviceEnterprise = \Drupal::service('tbo_account.create_companies_service');

    // Segment.
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $this->segment = $service_segment->getSegmentPhp();
  }

  /**
   * Implements pre_operations().
   *
   * @param array $context
   *   Get context data.
   */
  public function pre_operations(array &$context) {
    \Drupal::service('adf_import.import_service')->deleteLogTable('log_import_data_entity_field_data');

    // Get documents.
    $get_docs = \Drupal::service('tbo_entities.entities_service')->getDocumentTypes();
    foreach ($get_docs as $doc_key => $doc) {
      $documents[] = $doc['id'];
    }
    $context['results']['temporary']['documents'] = $documents;

  }

  /**
   * Implements import_init().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function import_init(array $data, array &$context) {
    $context['message'] = t('Inicio de la importación, validando información de la empresa...');
    $service = \Drupal::service('tbo_account.import_massive_enterprise');
    // Init batch, call process_batch to validate information and
    // set the results in $context for the next steps.
    foreach ($data as $key => $value) {
      $data_process[$key] = $service->validate_enterprise($value, $key, $context);
    }

    $context['results']['temporary']['data_enterprise'] = $data_process;
  }

  /**
   * Implements validate_userEnterprise().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function validate_userEnterprise(array $data, array &$context) {
    $context['message'] = t('Validando información del usuario...');

    $service = \Drupal::service('tbo_account.import_massive_enterprise');
    $enterprise_data = $context['results']['temporary']['data_enterprise'];
    // $key = 'undefined';.
    foreach ($data as $key => $value) {
      // Validate ent	erprise
      // $enterprise_data = $this->validate_enterprise($value, $key, $context);
      // Validate user if the enterprise exists.
      if ($enterprise_data[$key]['validate'] != FALSE) {
        $user_data[$key] = $service->validate_user($value, $key, $context);
      }
      else {
        $user_data[$key]['validate'] = FALSE;
      }
    }

    $context['results']['temporary']['user_data'] = $user_data;

  }

  /**
   * Implements validate_data().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function validate_data(array $data, array &$context) {
    $context['message'] = t('Verificando resultados...');

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');

    $data_available = [];
    $flag = TRUE;

    $data_enterprise = $context['results']['temporary']['data_enterprise'];
    $data_user = $context['results']['temporary']['user_data'];

    foreach ($data_enterprise as $key => $value) {
      if ($data_enterprise[$key]['validate'] == TRUE && $data_user[$key]['validate'] == TRUE) {
        if ($data_user[$key]['data']['user']['exists'] == TRUE && $data_enterprise[$key]['data']['enterprise']['exists'] == TRUE) {
          $company_uid = $data_enterprise[$key]['data']['enterprise']['id'];
          $user_uid = $data_user[$key]['data']['user']['uid'];
          $exists = $account_repository->getUsersRelationsCompanyAndUser($user_uid, $company_uid);

          if ($exists) {
            $flag = FALSE;
            $enterprise = $data_enterprise[$key]['data']['enterprise'];
            $user = $data_user[$key]['data']['user'];
            $tokens = [
              '@enterprise' => $enterprise['name'],
              '@user' => $user['user_name'],
              '@key' => $data_user[$key]['data']['key'],
            ];
            // Set log error.
            $context['results']['log_import'][$data_user[$key]['data']['key']]['fail'] = [
              'custom_id' => $enterprise['document_number'],
              'status_import' => 'Fallo',
              'description' => t('Error: La empresa @enterprise y el usuario @user ya existen y están relacionados, registro @key', $tokens),
            ];

            // Validate document_type and document_number user.
            if (isset($data_user[$key]['data']['user']['document_type_new']) || isset($data_user[$key]['data']['user']['document_number_new'])) {
              $user = User::load($user_uid);
              if (isset($data_user[$key]['data']['user']['document_type_new'])) {
                $user->set('document_type', $data_user[$key]['data']['user']['document_type_new']);
              }
              if (isset($data_user[$key]['data']['user']['document_number_new'])) {
                $user->set('document_number', $data_user[$key]['data']['user']['document_number_new']);
              }
              // Update user.
              $user->save();
            }
          }
          else {
            $flag = TRUE;
          }
        }
        else {
          $flag = TRUE;
        }

        // Save available data.
        if ($flag == TRUE) {
          $data_available[] = array_merge($data_enterprise[$key]['data'], $data_user[$key]['data']);
        }
      }
    }
    $context['results']['data'] = $data_available;
    unset($context['results']['temporary']['data_enterprise']);
    unset($context['results']['temporary']['user_data']);
  }

  /**
   * Implements validate_enterprise().
   *
   * @param array $data
   *   Get data.
   * @param $key
   *   The data key.
   * @param array $context
   *   Get context data.
   *
   * @return array
   *   The data to export.
   */
  protected function validate_enterprise(array $data, $key, array &$context) {
    $enterprise = [];

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');

    if (empty($data[0]) || empty($data[1])) {
      $tokens = [
        '@key' => $key,
      ];

      if (empty($data[0]) && !empty($data[1])) {
        $message = 'Error: El campo Tipo de documento de la empresa es requerido, registro @key';
      }
      elseif (!empty($data[0]) && empty($data[1])) {
        $message = 'Error: El campo número de documeto de la empresa es requerido, registro @key';
      }
      else {
        $message = 'Error: El campo tipo de documento y número de documento de la empresa son requeridos, registro @key';
      }

      $enterprise = [
        'validate' => FALSE,
        'key' => $key,
      ];

      $context['results']['log_import'][$key]['fail'] = [
        'custom_id' => $data[1],
        'status_import' => 'Fallo',
        'description' => t($message, $tokens),
      ];
      return $enterprise;
    }

    $documents = $context['results']['temporary']['documents'];
    // Validate document Type.
    if (!in_array(strtoupper($data[0]), $documents) && !in_array(strtolower($data[0]), $documents)) {
      if ($data[0] != 'nt' || $data[0] != 'NT') {
        $context['results']['log_import'][$key]['fail'] = [
          'custom_id' => $data[1],
          'status_import' => 'Fallo',
          'description' => t('Error: El tipo de documento @type no es válido, registro @key', ['@type' => $data[0], '@key' => $key]),
        ];
        $enterprise = [
          'validate' => FALSE,
          'key' => $key,
        ];

        return $enterprise;
      }
    }

    // Validate document number.
    if (strlen($data[1]) > 145) {
      $context['results']['log_import'][$key]['fail'] = [
        'custom_id' => t('Limite de caracteres superado'),
        'status_import' => 'Fallo',
        'description' => t('Error: El número de documento excede el límite permitido de 145 caracteres, registro @key', ['@type' => $data[0], '@key' => $key]),
      ];
      $enterprise = [
        'validate' => FALSE,
        'key' => $key,
      ];
      return $enterprise;
    }

    $enterprise = [];
    // Validate if enterprise exists in the system and return required data.
    $exists = $account_repository->getData('company_entity_field_data', [
      'id',
      'name',
      'document_number',
    ], 'document_number', $data[1]
    );
    if (!empty($exists)) {
      $enterprise = [
        'validate' => TRUE,
        'data' => [
          'key' => $key,
          'enterprise' => $exists,
        ],
      ];
      $enterprise['data']['enterprise']['document_type'] = $data[0];
      $context['results']['data'][$key]['enterprise_created'] = TRUE;
      $enterprise['data']['enterprise']['exists'] = TRUE;
    }
    else {
      // Validate spaces.
      $spaces = 0;
      $data[1] = str_replace(" ", "", $data[1], $spaces);
      if ($spaces > 0) {
        $names = '';
      }
      else {
        $names = \Drupal::service('tbo_account.create_companies_service')->_validateCompanyInServices($data[0], $data[1]);
      }

      if (empty($names)) {
        $enterprise = [
          'validate' => FALSE,
          'key' => $key,
        ];

        // Set log error.
        $context['results']['log_import'][$key]['fail'] = [
          'custom_id' => $data[1],
          'status_import' => 'Fallo',
          'description' => t('Error: la empresa con el número de documento @number y tipo de documento @type no existe, registro @key',
            [
              '@number' => $data[1],
              '@type' => $data[0],
              '@key' => $key,
            ]
          ),
        ];
      }
      else {
        if (isset($names['name_fixed'])) {
          if ($names['name_fixed']->customerInfo->lastName != NULL) {
            $enterprise_name = $names['name_fixed']->customerInfo->name . ' ' . $names['name_fixed']->customerInfo->lastName;
          }
          elseif (is_array($names['name_fixed']) && isset($names['name_fixed']['name'])) {
            $enterprise_name = $names['name_fixed']['name'];
          }
          else {
            $enterprise_name = $names['name_fixed']->customerInfo->name;
          }
        }
        elseif (isset($names['name_mobile'])) {
          $enterprise_name = $names['name_mobile']->clientName;
        }

        $enterprise = [
          'validate' => TRUE,
          'data' => [
            'key' => $key,
            'enterprise' => [
              'name' => trim($enterprise_name, " \t"),
              'document_type' => strtolower($data[0]),
              'document_number' => $data[1],
              'exists' => FALSE,
              'fixed' => isset($names['name_fixed']) ? TRUE : FALSE,
              'mobile' => isset($names['name_mobile']) ? TRUE : FALSE,
            ],
          ],
        ];
      }
    }

    return $enterprise;
  }

  /**
   * Implements validate_user().
   *
   * @param array $data
   *   Get data.
   * @param $key
   *   The data key.
   * @param array $context
   *   Get context data.
   *
   * @return array
   *   The data to export.
   */
  protected function validate_user(array $data, $key, array &$context) {
    $user_data = [];

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');

    // Validate if email field exists.
    if (array_key_exists(2, $data) && !empty($data[2])) {

      if (strlen($data[2]) > 200) {
        $data['key'] = $key;
        $user_data = [
          'validate' => FALSE,
          'data' => $data,
        ];

        $context['results']['log_import'][$key]['fail'] = [
          'custom_id' => $data[1],
          'status_import' => 'Fallo',
          'description' => t('Error: El correo electrónico excede el límite de 200 caracteres, registro @key', ['@key' => $key]),
        ];
        return $user_data;
      }

      // Validate email syntax.
      if (!preg_match("#^([a-z0-9])+([a-z0-9\._-])*@([a-z0-9_-])+([a-z0-9\._-]+)+([\.])+([a-z]+)+$#i", $data[2])) {
        // Set error log.
        $data['key'] = $key;
        $user_data = [
          'validate' => FALSE,
          'data' => $data,
        ];

        $context['results']['log_import'][$key]['fail'] = [
          'custom_id' => $data[1],
          'status_import' => 'Fallo',
          'description' => t('Error: El correo @mail no es válido, registro @key', ['@mail' => $data[2], '@key' => $key]),
        ];
        return $user_data;
      }

      // Validate if user exists.
      $exists = $account_repository->getData('users_field_data',
        [
          'uid',
          'full_name',
          'name',
          'document_type',
          'document_number',
        ],
        'mail', $data[2]);
      if ($exists) {
        $name = (!empty($data[3])) ? $data[3] : ((!empty($exists['full_name'])) ? $exists['full_name'] : $exists['name']);
        $user_data = [
          'validate' => TRUE,
          'data' => [
            'key' => $key,
            'user' => [
              'user_name' => $name,
              'mail' => $data[2],
              'phone_number' => $data[4],
              'uid' => $exists['uid'],
              'document_type' => $exists['document_type'],
              'document_number' => $exists['document_number'],
              'exists' => TRUE,
            ],
          ],
        ];

        // Update document_type and document_user.
        if ($exists['document_type'] == '' && $data[5] != '') {
          $documents = $context['results']['temporary']['documents'];
          // Validate document Type.
          if (in_array(strtolower($data[5]), $documents)) {
            $user_data['data']['user']['document_type_new'] = strtolower($data[5]);
          }
        }

        if ($exists['document_number'] == '' && $data[6] != '') {
          $user_data['data']['user']['document_number_new'] = $data[6];
        }

        return $user_data;
      }

      // Validate if admin phone and name exists.
      if ((!array_key_exists(3, $data) || empty($data[3])) || (!array_key_exists(4, $data) || empty($data[4]))) {
        // Set error log.
        $data['key'] = $key;
        $user_data = [
          'validate' => FALSE,
          'data' => $data,
        ];
        // Set error log.
        $context['results']['log_import'][$key]['fail'] = [
          'custom_id' => $data[1],
          'status_import' => 'Fallo',
        ];

        $messages_field = [
          'phone' => 'Se requiere el teléfono del administrador',
          'name' => 'Se requiere el nombre del administrador',
        ];

        if ((!array_key_exists(3, $data) || empty($data[3])) && (!array_key_exists(4, $data) || empty($data[4]))) {

          $message_field = $messages_field['phone'] . ' y ' . $messages_field['name'];
        }
        elseif (!array_key_exists(4, $data) || empty($data[4])) {
          $message_field = $messages_field['phone'];
        }
        else {
          $message_field = $messages_field['name'];
        }

        $context['results']['log_import'][$key]['fail']['description'] = t("Error:  $message_field, registro @key", ['@key' => $key]);
        return $user_data;

      }
      else {

        if (strlen($data[3]) > 300 || strlen($data[4]) > 20 || strlen($data[6]) > 40) {

          $messages = [
            'name' => 'El nombre del usuario (dato requerido*) excede el límite de 300 caracteres',
            'phone' => 'El número de teléfono (dato requerido*) excede el límite de 20 caracteres',
            'document_number' => 'El número de documeto excede el límite de 40 caracteres',
          ];

          if (strlen($data[3]) > 300 && strlen($data[4]) > 20 && strlen($data[6]) > 40) {
            $message = $messages['name'] . ', ' . $messages['phone'] . ' y ' . $messages['document_number'];
          }
          elseif (strlen($data[3]) > 300 && strlen($data[4]) > 20) {
            $message = $messages['name'] . ' y ' . $messages['phone'];
          }
          elseif (strlen($data[4]) > 20 && strlen($data[6]) > 40) {
            $message = $messages['phone'] . ' y ' . $messages['document_number'];
          }
          elseif (strlen($data[3]) > 300 && strlen($data[6]) > 40) {
            $message = $messages['name'] . ' y ' . $messages['document_number'];
          }
          elseif (strlen($data[3]) > 300) {
            $message = $messages['name'];
          }
          elseif (strlen($data[4]) > 20) {
            $message = $messages['phone'];
          }
          elseif (strlen($data[6]) > 40) {
            $message = $messages['document_number'];
          }

          if (strlen($data[3]) < 300 && strlen($data[4]) < 20 && strlen($data[6]) > 40) {
            $context['results']['log_import'][$key]['error'][] = [
              'custom_id' => $data[1],
              'status_import' => 'Error',
              'description' => t("Error: $message, registro @key", ['@key' => $key]),
            ];

            $data[6] = '';
          }
          else {
            // Set error log.
            $user_data = [
              'validate' => FALSE,
              'key' => $key,
            ];
            // Set error log.
            $context['results']['log_import'][$key]['fail'] = [
              'custom_id' => $data[1],
              'status_import' => 'Fallo',
              'description' => t("Error: $message, registro @key", ['@key' => $key]),
            ];

            return $user_data;
          }
        }

        $documents = $context['results']['temporary']['documents'];
        // Validate document type.
        $exist_document_type = array_key_exists(5, $data);
        if (!$exist_document_type) {
          // Set error log.
          $data['key'] = $key;
          $user_data = [
            'validate' => FALSE,
            'data' => $data,
          ];

          $context['results']['log_import'][$key]['fail'] = [
            'custom_id' => $data[1],
            'status_import' => 'Fallo',
            'description' => t('Error: El tipo de documento del administrador no esta definido, registro @key', ['@key' => $key]),
          ];
          return $user_data;
        }
        if (empty($data[5])) {
          // Set error log.
          $data['key'] = $key;
          $user_data = [
            'validate' => FALSE,
            'data' => $data,
          ];

          $context['results']['log_import'][$key]['fail'] = [
            'custom_id' => $data[1],
            'status_import' => 'Fallo',
            'description' => t('Error: El tipo de documento del administrador no puede estar vacio, registro @key', ['@key' => $key]),
          ];
          return $user_data;
        }

        if (!in_array(strtolower($data[5]), $documents) && !in_array(strtoupper($data[5]), $documents)) {
          $data['key'] = $key;
          $user_data = [
            'validate' => FALSE,
            'data' => $data,
          ];

          $context['results']['log_import'][$key]['fail'] = [
            'custom_id' => $data[1],
            'status_import' => 'Fallo',
            'description' => t('Error: El tipo de documento del administrador (@document) no es válido, registro @key', ['@document' => $data[5], '@key' => $key]),
          ];
          return $user_data;
        }

        // Validate document number.
        $exist_document_number = array_key_exists(6, $data);
        if (!$exist_document_number) {
          // Set error log.
          $data['key'] = $key;
          $user_data = [
            'validate' => FALSE,
            'data' => $data,
          ];

          $context['results']['log_import'][$key]['fail'] = [
            'custom_id' => $data[1],
            'status_import' => 'Fallo',
            'description' => t('Error: El numero de documento del administrador no esta definido, registro @key', ['@key' => $key]),
          ];
          return $user_data;
        }
        if (empty($data[6])) {
          // Set error log.
          $data['key'] = $key;
          $user_data = [
            'validate' => FALSE,
            'data' => $data,
          ];

          $context['results']['log_import'][$key]['fail'] = [
            'custom_id' => $data[1],
            'status_import' => 'Fallo',
            'description' => t('Error: El numero de documento del administrador no puede estar vacio, registro @key', ['@key' => $key]),
          ];
          return $user_data;
        }

        // Validate is number.
        $number = (int) $data[6];
        if ($number == 0) {
          $data['key'] = $key;
          $user_data = [
            'validate' => FALSE,
            'data' => $data,
          ];

          $context['results']['log_import'][$key]['fail'] = [
            'custom_id' => $data[1],
            'status_import' => 'Fallo',
            'description' => t('Error: El numero de documento del administrador (@document_number) no es válido, registro @key', ['@document_number' => $data[6], '@key' => $key]),
          ];
          return $user_data;
        }

        $user_data = [
          'validate' => TRUE,
          'data' => [
            'key' => $key,
            'user' => [
              'user_name' => $data[3],
              'mail' => $data[2],
              'phone_number' => $data[4],
              'exists' => FALSE,
            ],
          ],
        ];

        $user_data['data']['user']['document_type'] = strtolower($data[5]);
        $user_data['data']['user']['document_number'] = $data[6];

        return $user_data;
      }
    }
    else {
      // Set log error.
      $context['results']['log_import'][$key]['fail'] = [
        'custom_id' => $data[1],
        'status_import' => 'Fallo',
        'description' => t('Error: Se requiere el correo electronico, registro @key', ['@key' => $key]),
      ];
      $user_data = [
        'validate' => FALSE,
        'key' => $key,
      ];
    }

    return $user_data;
  }

  /**
   * Implements create_enterprise().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function create_enterprise(array $data, array &$context) {
    $context['message'] = t('Creando empresas...');

    $data_process = $context['results']['data'];
    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');

    foreach ($data_process as $key => $value) {
      if ($value['enterprise']['exists'] == FALSE && !in_array($value['enterprise']['document_number'], $context['results']['enterprises_create'])) {
        try {
          $exists = $account_repository->getData('company_entity_field_data', ['id'], 'document_number', $value['enterprise']['enterprise']['document_number']);
          if (empty($exists)) {
            $company_data = $value['enterprise'];
            // TODO temporary.
            $company_data['segment'] = 'segmento';
            $company_data['status'] = TRUE;
            $company_data['company_name'] = $company_data['name'];
            unset($company_data['exists']);

            // Save company.
            \Drupal::service('tbo_account.create_companies_service')->_createCompany($company_data);

            $context['results']['enterprises_create'][] = $company_data['document_number'];
            $context['results']['data'][$key]['enterprise_created'] = TRUE;
          }
        }
        catch (\Exception $e) {
          $context['results']['data'][$key]['enterprise_created'] = FALSE;
        }
      }
      else {
        $context['results']['data'][$key]['enterprise_created'] = TRUE;
        $context['results']['data'][$key]['enterprise']['exists'] = TRUE;
      }
    }
  }

  /**
   * Implements create_user().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function create_user(array $data, array &$context) {
    $context['message'] = t('Creando usuarios...');

    $data_process = $context['results']['data'];

    foreach ($data_process as $key => $value) {
      if ($value['user']['exists'] == FALSE && !in_array($value['user']['mail'], $context['results']['users_created'])) {
        try {
          $data_user = [
            'mail' => $value['user']['mail'],
            'username' => $value['user']['mail'],
            'phone_number' => $value['user']['phone_number'],
            'document_number' => $value['user']['document_number'],
            'document_type' => $value['user']['document_type'],
            'full_name' => $value['user']['user_name'],
          ];

          // Create user.
          \Drupal::service('tbo_account.create_companies_service')->_createUser($data_user, 'MassiveImportEnterprisesService_line_642_create_user');

          $context['results']['users_created'][] = $value['user']['mail'];
          $context['results']['data'][$key]['user_created'] = TRUE;
        }
        catch (\Exception $e) {
          $context['results']['data'][$key]['user_created'] = FALSE;
        }
      }
      else {
        $context['results']['data'][$key]['user_created'] = TRUE;
        $context['results']['data'][$key]['user']['exists'] = TRUE;
      }
    }
  }

  /**
   * Implements create_relation().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function create_relation(array $data, array &$context) {
    $context['message'] = t('Creando relación empresa/usuario...');

    $data_process = $context['results']['data'];

    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $relation = '';

    $associated = NULL;
    if (in_array('tigo_admin', $account->getRoles())) {
      $associated = $uid;
    }

    foreach ($data_process as $key => $value) {
      try {
        $company_uid = $account_repository->getData('company_entity_field_data', ['id'], 'document_number', $value['enterprise']['document_number'], TRUE);
        $user_uid = $account_repository->getData('users_field_data', ['uid'], 'mail', $value['user']['mail'], TRUE);

        // Validate if relation exists.
        if (!empty($user_uid)) {
          // Get relation by company and user.
          $relation = $account_repository->getUsersRelationsCompanyAndUser($user_uid, $company_uid);
        }

        if (!empty($user_uid) && empty($relation)) {
          // Create relations company-user.
          $data_relation = [
            'name' => $value['enterprise']['name'],
            'users' => $user_uid,
            'company_id' => $company_uid,
            'associated_id' => $associated,
            'status' => TRUE,
          ];

          \Drupal::service('tbo_account.create_companies_service')->_CreateCompanyUserRelation($data_relation);

          $context['results']['data'][$key]['relation_created'] = TRUE;
          $context['results']['data'][$key]['user']['uid'] = $user_uid;
          $context['results']['data'][$key]['enterprise']['uid'] = $company_uid;
        }
        else {
          $relation = '';
          $context['results']['data'][$key]['relation_created'] = FALSE;

          $tokens = [
            '@enterprise' => $value['enterprise']['name'],
            '@user' => $value['user']['user_name'],
            '@key' => $value['key'],
          ];
          // Set log error.
          $context['results']['log_import'][$value['key']]['fail'] = [
            'custom_id' => $value['enterprise']['document_number'],
            'status_import' => 'Fallo',
            'description' => t('Error: La empresa @enterprise y el usuario @user ya existen y están relacionados, registro @key', $tokens),
          ];
        }
      }
      catch (\Exception $e) {
        $context['results']['data'][$key]['relation_created'] = FALSE;
      }
    }
  }

  /**
   * Implements send_messages().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function send_messages(array $data, array &$context) {
    $context['message'] = t('Enviando correos electrónicos...');

    $data_process = $context['results']['data'];

    $service_email = \Drupal::service('tbo_mail.send');
    // Get repository.
    $account_repository = \Drupal::service('tbo_account.repository');
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $user = $current_user->getAccount();

    $name = (!empty($user->full_name)) ? $user->full_name : $current_user->getAccountName();

    $params = [];
    foreach ($data_process as $key => $value) {

      $params['to'] = $value['user']['phone_number'];

      if (($value['user_created'] == TRUE && $value['enterprise_created'] == TRUE && $value['relation_created'] == TRUE && $value['user']['exists'] == FALSE) || ($value['user_created'] == TRUE && $value['enterprise_created'] != TRUE && $value['relation_created'] == TRUE && $value['enterprise']['exists'] == TRUE)) {
        try {
          $time = $account_repository->getData('users_field_data', ['created'], 'uid', $value['user']['uid'], TRUE);
          $hash = Crypt::hmacBase64(($time . $value['user']['mail'] . $uid), 'admin_company');
          $current_uri = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];
          $url = $current_uri . '/invitado/' . $hash;

          $roles = 'admin empresa';

          $tokens_user = [
            'user' => $value['user']['user_name'],
            'admin' => $name,
            'enterprise' => $value['enterprise']['name'],
            'enterprise_num' => $value['enterprise']['document_number'],
            'document' => $value['enterprise']['document_type'],
            'admin_enterprise' => $value['user']['user_name'],
            'admin_mail' => $value['user']['mail'],
            'admin_phone' => $value['user']['phone_number'],
            'mail_to_send' => $value['user']['mail'],
            'link' => $url,
          ];

          // Set sms creation message.
          $sms_message = t('Hola @username, se ha creado una cuenta para usted, con los siguientes
           privilegios: @rolesasignados, puede iniciar session haciendo clic en @urlsite.',
            [
              '@username' => $tokens_user['user'],
              '@rolesasignados' => $roles,
              '@urlsite' => $tokens_user['link'],
            ]);
          $sms_message_thx = t('Gracias');

          \Drupal::service('tbo_account.create_companies_service')->_sendSms($value['user']['phone_number'], $sms_message . ' ' . $sms_message_thx, 'Creacion masiva de empresas', $tokens_user['user'], $value['enterprise']['name']);

          // Invitation Entity.
          $invitation = InvitationAccessEntity::create();
          $invitation->set('user_id', $value['user']['uid']);
          $invitation->set('user_name', $value['user']['user_name']);
          $invitation->set('company_id', $value['enterprise']['uid']);
          $invitation->set('mail', $value['user']['mail']);
          $invitation->set('token', $hash);
          $invitation->set('created', $time);
          $invitation->save();

          // Send email.
          try {
            $service_email->send_message($tokens_user, $template = 'new_enterprise');
            $context['results']['data'][$key]['send_messages'] = TRUE;
          }
          catch (\Exception $e) {
            $context['results']['data'][$key]['send_messages'] = FALSE;
            $service = \Drupal::service('tbo_core.audit_log_service');
            $service->loadName();
            // Create array data[].
            $data = [
              'event_type' => t('Cuenta'),
              'description' => t('Error en el envio del email'),
              'details' => 'Usuario ' . $service->getName() . ' presento error en Creacion masiva de empresas al enviar el mensaje al admin empresa ' . $value['user']['user_name'] . ' de la empresa ' . $value['enterprise']['name'] . ', con email ' . $value['user']['mail'],
            ];

            // Save audit log.
            $service->insertGenericLog($data);
          }
        }
        catch (\Exception $e) {
          // Logger.
          \Drupal::logger('createCompanyMassiveSendMessages')->error('Error: ' . $e->getMessage());

          $context['results']['data'][$key]['send_messages'] = FALSE;
        }

      }
      elseif (($value['relation_created'] == TRUE && $value['enterprise_created'] == TRUE && $value['user']['exists'] == TRUE) || ($value['relation_created'] == TRUE && $value['enterprise']['exists'] == TRUE && $value['user']['exists'] == TRUE)) {
        try {
          // Envio de mensaje de texto de confirmacion de creacion de empresa.
          $sms_message = 'Nuevo administrador de empresa ' . $value['enterprise']['name'];
          \Drupal::service('tbo_account.create_companies_service')->_sendSms($value['user']['phone_number'], $sms_message, 'Creacion masiva de empresas', $value['user']['user_name'], $value['enterprise']['name']);

          $tokens = [
            'user' => $value['user']['user_name'],
            'enterprise' => $value['enterprise']['name'],
            'mail_to_send' => $value['user']['mail'],
          ];

          // Send email.
          try {
            $service_email->send_message($tokens, 'assing_enterprise');
            $context['results']['data'][$key]['send_messages'] = TRUE;
          }
          catch (\Exception $e) {
            $context['results']['data'][$key]['send_messages'] = FALSE;
            $service = \Drupal::service('tbo_core.audit_log_service');
            $service->loadName();
            // Create array data[].
            $data = [
              'event_type' => t('Cuenta'),
              'description' => t('Error en el envio del email'),
              'details' => 'Usuario ' . $service->getName() . ' presento error en Creacion masiva de empresas al enviar el mensaje al admin empresa ' . $value['user']['user_name'] . ' de la empresa ' . $value['enterprise']['name'] . ', con email ' . $value['user']['mail'],
            ];

            // Save audit log.
            $service->insertGenericLog($data);
          }
        }
        catch (\Exception $e) {
          // Logger.
          \Drupal::logger('createCompanyMassiveSendMessages')->error('Error: ' . $e->getMessage());

          $context['results']['data'][$key]['send_messages'] = FALSE;
        }
      }
    }
  }

  /**
   * Implements send_messages().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function createLogs(array $data, array &$context) {
    $context['message'] = t('Organizando resultados de importación...');
    $data_process = $context['results']['data'];

    foreach ($data_process as $key => $value) {
      $enterprise = $value['enterprise'];
      $tokens = [
        '@enterprise' => $value['enterprise']['name'],
        '@number' => $value['enterprise']['document_number'],
        '@doctype' => $value['enterprise']['document_type'],
        '@user' => $value['user']['user_name'],
      ];

      if ($value['enterprise_created'] == TRUE && $value['user_created'] == TRUE && $value['relation_created'] == TRUE && $value['user']['exists'] == FALSE && $value['enterprise']['exists'] == FALSE) {
        $context['results']['log_import'][$value['key']]['success'] = [
          'custom_id' => $value['enterprise']['document_number'],
          'status_import' => 'Exitoso',
          'description' => t('Se ha creado la empresa @enterprise con el número de documento @number y tipo de documento @doctype, la empresa se ha vinculado con éxito al nuevo usuario @user', $tokens),
        ];

      }
      elseif ($value['enterprise_created'] == TRUE && $value['user_created'] == TRUE && $value['relation_created'] == TRUE && $value['user']['exists'] == FALSE && $value['enterprise']['exists'] == TRUE) {
        $context['results']['log_import'][$value['key']]['success'] = [
          'custom_id' => $value['enterprise']['document_number'],
          'status_import' => 'Exitoso',
          'description' => t('Se ha creado el usuario @user y se ha asociado a la empresa @enterprise identificada con el numero @number y tipo de documento @doctype', $tokens, ['langcode' => 'es']),
        ];

      }
      elseif ($value['enterprise_created'] == TRUE && $value['user_created'] == TRUE && $value['relation_created'] == TRUE && $value['user']['exists'] == TRUE && $value['enterprise']['exists'] == FALSE) {
        $context['results']['log_import'][$value['key']]['success'] = [
          'custom_id' => $value['enterprise']['document_number'],
          'status_import' => 'Exitoso',
          'description' => t('Se creado la empresa @enterprise con el número de documento @number y tipo de documento @doctype al usuario @user', $tokens, ['langcode' => 'es']),
        ];

      }
      elseif ($value['enterprise_created'] == TRUE && $value['user_created'] == TRUE && $value['relation_created'] == TRUE && $value['user']['exists'] == TRUE && $value['enterprise']['exists'] == TRUE) {
        $context['results']['log_import'][$value['key']]['success'] = [
          'custom_id' => $value['enterprise']['document_number'],
          'status_import' => 'Exitoso',
          'description' => t('Se ha asociado la empresa @enterprise con el número de documento @number y tipo de documento @doctype al usuario @user', $tokens, ['langcode' => 'es']),
        ];
      }
    }
  }

  /**
   * Implements send_messages().
   *
   * @param array $data
   *   Get data.
   * @param array $context
   *   Get context data.
   */
  public function set_import_log(array $data = NULL, array &$context) {
    $context['message'] = t('Guardando resultados de la importación...');

    $data_log = $context['results']['log_import'];
    $import_logs = \Drupal::service('adf_import.import_service');
    if (!empty($data_log) && isset($data_log)) {
      foreach ($data_log as $key => $value) {
        if (array_key_exists('success', $value) && !empty($value['success'])) {
          $context['results']['success_total'] = $context['results']['success_total'] + 1;
          $value['success']['description'] = htmlspecialchars_decode($value['success']['description']);
          $import_logs->insertDataLog($value['success']);
        }

        if (array_key_exists('fail', $value) && !empty($value['fail'])) {
          $context['results']['fail_total'] = $context['results']['fail_total'] + 1;
          $value['fail']['description'] = htmlspecialchars_decode($value['fail']['description']);
          $import_logs->insertDataLog($value['fail']);
        }

        if (array_key_exists('error', $value) && !empty($value['error'])) {
          $import_logs->insertMultipleDataLog($value['error']);
        }
      }

      if (array_key_exists('log_import', $context['results'])) {
        unset($context['results']['log_import']);
      }
    }
  }

  /**
   * Implements batch_finished().
   *
   * @param $success
   *   The success.
   * @param $results
   *   Get the results.
   * @param $operations
   *   Get operations status.
   */
  public function batch_finished($success, $results, $operations) {

    // Save on audit logs.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    // Segment.
    $service_segment = \Drupal::service('adf_segment');
    $service_segment->segmentPhpInit();
    $segment = $service_segment->getSegmentPhp();
    $config = \Drupal::config('tbo_account.autocreateformconfig');
    $method = $config->get('method');
    $detailFixe = ' La empresa se creó por el método ' . $method . '.';
    $params = [
      'description' => 'Usuario crea empresas masivamente',
      'details' => 'Usuario ' . $account->getAccountName() . ' crea empresas masivamente.' . $detailFixe,
      'event_type' => 'Cuenta',
      'old_value' => 'No aplica',
      'new_value' => 'No aplica',
      // TODO temporary value.
      'companySegment' => 'segment',
    ];

    $service->insertGenericLog($params);

    drupal_set_message(t('Importación finalizada correctamente.'), 'status');

    // Segment - Carga masiva.
    $segment->track([
      'event' => 'TBO - Carga Masiva',
      'userId' => $uid,
      'properties' => [
        'category' => 'Creación de Empresas',
        'label' => 'Creación exitosa',
        'value' => (is_null($results['success_total'])) ? 0 : $results['success_total'],
        'site' => 'NEW',
      ],
    ]);

    \Drupal::service('config.factory')->getEditable('adf_import_data.adfimportdataformconfig')
      ->set('import_finish', 1)
      ->set('import_success', $results['success_total'])
      ->set('import_fail', $results['fail_total'])
      ->save();
  }

}
