<?php

namespace Drupal\tbo_account\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manage config a 'UpdateUserDataForm' form.
 */
class UpdateUserInfoFormClass {

  use StringTranslationTrait;

  /**
   * Class SendMessage.
   *
   * @var \Drupal\tbo_mail\SendMessage
   */
  protected $serviceMessage;

  /**
   * Config values.
   *
   * @var array
   */
  protected $config;

  /**
   * UpdateUserInfoFormClass constructor.
   */
  public function __construct() {
    $this->serviceMessage = \Drupal::service('tbo_mail.send');
    $this->config = \Drupal::config('tbo_account.update_user_info.settings')
      ->getRawData();
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $account = User::load(\Drupal::currentUser()->id());

    $form['name'] = [
      '#type' => 'textfield',
      '#id' => 'update-user-name',
      '#title' => $this->t('Nombres'),
      '#maxlength' => 300,
      '#attributes' => [
        'ng-model' => 'userName',
        'ng-change' => 'validateFormUser()',
        'ng-init' => 'userName = \'' . $account->get('full_name')->value . '\'',
        'class' => [
          'validate',
        ],
      ],
      '#size' => 64,
      '#required' => TRUE,
    ];

    $phone_number = $account->get('phone_number')->value;

    $b2b_config = \Drupal::service('tbo_general.tbo_config');
    $validate_first_number = substr($phone_number, 0, 1);
    if ($validate_first_number == "5") {
      $phone_number = $b2b_config->formatFixedPhone($phone_number);
    }
    else {
      $phone_number = $b2b_config->formatLine($phone_number);
    }

    $form['cel_number'] = [
      '#type' => 'textfield',
      '#id' => 'update-cel-number',
      '#title' => $this->t('Número de contacto'),
      '#maxlength' => 17,
      '#attributes' => [
        'ng-model' => 'phoneNumber',
        'ng-change' => 'formatPhone()',
        'ng-keydown' => 'checkKeyDownPhone($event)',
        'ng-init' => 'phoneNumber = \'' . $phone_number . '\'',
        'validate_with_text' => TRUE,
        'class' => [
          'validate',
        ],
      ],
      '#size' => 64,
      '#required' => TRUE,
    ];

    try {
      /** @var \Drupal\tbo_entities\Services\EntitiesService $documents */
      $documents = \Drupal::service('tbo_entities.entities_service');
      // Se obtienen los tipos de documento de la base de datos.
      $options_service = $documents->getAbreviatedDocumentTypes();
    }
    catch (\Exception $exception) {
      // En caso de fallar, se asignan valores por omisión.
      $options_service = [
        [
          'id' => 'cc',
          'label' => 'CC',
        ],
        [
          'id' => 'ce',
          'label' => 'CE',
        ],
        [
          'id' => 'nit',
          'label' => 'NIT',
        ],
      ];
    }
    $document_type = $account->get('document_type')->getString();
    $options = [];
    foreach ($options_service as $key => $data) {
      $options[$data['id']] = $data['label'];
    }
    $array = $options;
    reset($array);
    $first_key = key($array);
    if (isset($document_type)) {
      $first_key = $document_type;
    }

    $form['document_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#options' => $options,
      '#default_value' => $first_key,
      '#attributes' => [
        'ng-model' => 'documentType',
        'ng-change' => 'validateFormUser()',
        'ng-init' => 'documentType = \'' . $first_key . '\'',
      ],
      '#size' => 5,
    ];
    $form['document_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número de documento'),
      '#maxlength' => 40,
      '#attributes' => [
        'ng-model' => 'documentNumber',
        'ng-change' => 'validateFormUser()',
        'ng-init' => 'documentNumber = \'' . $account->get('document_number')->value . '\'',
        'class' => [
          'validate',
        ],
      ],
      '#size' => 64,
      '#description' => $this->t('Ingrese su número de cédula'),
      '#required' => TRUE,
    ];

    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo electrónico'),
      '#attributes' => [
        'disabled' => 'disabled',
        'class' => [
          'validate',
          'disabled',
        ],
      ],
      '#value' => $account->getEmail(),
      '#size' => 64,
    ];

    $form['terms_title'] = [
      '#markup' => $this->config['terms_title'],
    ];

    $form['title_instructions'] = [
      '#markup' => $this->config['title_instructions'],
    ];

    $form['instructions'] = [
      '#markup' => $this->config['instructions'],
    ];

    $nid = $this->config['terms_node'];
    $node = Node::load($nid);
    $render = [];
    $url = '#';
    if (isset($node)) {
      $render = \Drupal::entityTypeManager()
        ->getViewBuilder('node')
        ->view($node);
    }

    $url = $this->config['url'];
    if ($this->config['show_popup']) {
      $url = '#modal-terms';
    }

    $form['terms'] = [
      '#markup' => t($this->config['terms_text'], [
        '@link' => $url,
        '@target' => $this->config['target'],
      ]),
    ];

    $form['popup_terms'] = $render;

    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    $twig->addGlobal('show_popup_terms_popup', $this->config['show_popup']);

    $form['submit'] = [
      '#type' => 'submit',
      '#id' => 'submit-update-user',
      '#value' => $this->t('GUARDAR'),
      '#attributes' => [
        'class' => [
          'btn',
          'btn-primary',
          'disabled',
        ],
      ],
    ];

    $form['#theme'] = 'form_update_user_info';
    $form['#attached']['drupalSettings']['tbo_account']['updateUserInfo'] = [
      'show_popup' => $this->config['show_popup'],
      'message' => [
        'empty_phone_number' => $this->t("Ingrese un número celular o un número telefónico fijo de la siguiente manera: 57 + (indicativo de la zona) + (número teléfonico fijo)"),
        'minimum_length' => $this->t("Debe ingresar 10 dígitos"),
        'indicative_deparment' => $this->t("Ingrese el indicativo del departamento"),
        'fixed_number_invalid' => $this->t("El número fijo no es válido"),
        'indicative_city' => $this->t("El indicativo de la ciudad no es correcto"),
        'fixed_contact_number' => $this->t("Debe ingresar el número fijo de contacto"),
        'can_not_start_zero' => $this->t("El número fijo no puede empezar en cero"),
        'must_have_7_digits' => $this->t("El número fijo debe tener 7 dígitos"),
      ],
      'format' => [
        'mobile' => $b2b_config->getFormatPhone(),
      ],
    ];
    $form['#attached']['library'][] = 'tbo_account/update-user-info';

    /** @var \Drupal\tbo_core\Services\AuditLogService $service */
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => t('Cuenta'),
      'description' => t('Usuario accede a formulario de actualización datos de su cuenta'),
      'details' => t('Usuario @user con @mail accede a formulario de actualización de datos de su cuenta', [
        '@user' => $account->get('full_name')->value,
        '@mail' => $account->getEmail(),
      ]),
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    $service->insertGenericLog($data);

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $values = $form_state->getValues();
      $phone = $values['cel_number'];
      $phone = str_replace('(', '', $phone);
      $phone = str_replace(')', '', $phone);
      $phone = str_replace(' ', '', $phone);
      $phone = str_replace('-', '', $phone);
      $user = User::load(\Drupal::currentUser()->id());

      $user->set('document_type', $values['document_type']);
      $user->set('phone_number', $phone);
      $user->set('document_number', $values['document_number']);
      $user->set('full_name', $values['name']);
      $user->save();

      $_SESSION['update_user_info'] = TRUE;

      // Remove notification update.
      $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_notifications');
      $id = \Drupal::currentUser()->id();
      $notification_id = $_SESSION['notification_verified']['tbo_notification_update_' . $id];

      if (isset($notification_id)) {
        // Get repository.
        $repository = \Drupal::service('tbo_services.tbo_services_repository');
        // Validate relation and create.
        $user_id = \Drupal::currentUser()->id();
        $exist = $repository->getNotificationDetail($notification_id, $user_id);
        // Create relation in notification.
        if (empty($exist)) {
          // Create relation in notification.
          $notification_service = \Drupal::service('tbo_services.tools_notifications');
          $create_relation = $notification_service->createRelationNotification($user_id, $notification_id);
        }

        // Delete notification to session.
        $remove = $tempStore->delete('tbo_notification_update');
      }

      // Log de auditoría.
      /** @var \Drupal\tbo_core\Services\AuditLogService $service */
      $service = \Drupal::service('tbo_core.audit_log_service');
      $service->loadName();
      $data = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => t('Cuenta'),
        'description' => t('Usuario actualiza datos de su cuenta'),
        'details' => t('Usuario @user con @mail actualiza datos de su cuenta', [
          '@user' => $user->get('full_name')->value,
          '@mail' => $user->getEmail(),
        ]),
        'old_value' => 'No disponible',
        'new_value' => 'No disponible',
      ];
      $service->insertGenericLog($data);

      // Add segment track.
      $this->saveSegmentTrack('Exitoso');

      $tokens = [
        'user' => $user->getAccountName(),
        'admin_enterprise' => $user->getAccountName(),
        'admin_mail' => $user->getEmail(),
        'phone' => $phone,
        'admin_phone' => $phone,
        'phone_to_send' => $phone,
        'link' => $GLOBALS['base_url'],
        'mail_to_send' => $user->getEmail(),
      ];

      $this->serviceMessage->send_message($tokens, 'update_user_info');

      drupal_set_message(t('Tus datos se actualizaron con éxito.'));
    }
    catch (\Exception $e) {
      // Add segment track.
      $this->saveSegmentTrack('Fallido');
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * Implements segment track.
   *
   * @param string $action
   *   The status segment track.
   */
  public function saveSegmentTrack($action = '') {
    $event = 'TBO - Actualizacion de Datos - TX';
    $category = 'Dashboard';
    $environment = '';
    if (isset($_SESSION['company']['environment'])) {
      $environment = $_SESSION['company']['environment'];
      if ($environment == 'both') {
        $environment = 'fijo - movil';
      }
    }
    $label = $action . ' - ' . $environment;

    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
  }

}
