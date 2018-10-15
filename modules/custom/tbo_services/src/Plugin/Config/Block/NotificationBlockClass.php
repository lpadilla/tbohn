<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_services\Plugin\Block\NotificationBlock;
use Drupal\adf_core\Util\UtilFile;
use Drupal\tbo_entities\Entity\NotificationEntity;
use Drupal\file\Entity\File;

/**
 * Manage config a 'NotificationBlockClass' block.
 */
class NotificationBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Set instance configuration block.
   *
   * @param \Drupal\tbo_services\Plugin\Block\NotificationBlock $instance
   *   Instance NotificationBlock block.
   * @param array $config
   *   Instance config block.
   */
  public function setConfig(NotificationBlock &$instance, array &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [
        ],
      ],
      'others_display' => [
        'table_fields' => [
        ],
      ],
      'others' => [
        'config' => [
          'image_alert' => '',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['others']['image_alert'] = [
      '#type' => 'managed_file',
      '#title' => t('Imagen Para las alertas'),
      '#default_value' => $this->configuration['others']['config']['image_alert'],
      '#description' => t('Por favor ingrese una imagen de formato PNG, JPEG y medidas recomendadas minimas 50x50px y maximas 200x200px'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg'],
        'file_validate_image_resolution' => ['200x200', '50x50'],
      ],
    ];

    $form = $this->instance->cardBlockForm($form['others']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface &$form_state, &$config) {
    $this->configuration['table_options'] = $form_state->getValue(['table_options']);
    $this->configuration['filters_options'] = $form_state->getValue(['filters_options']);
    $this->configuration['others'] = $form_state->getValue(['others']);

    $fid = $form_state->getValue('others')['config']['image_alert'];
    // Save file permanently.
    if ($fid) {
      UtilFile::setPermanentFile($fid, 'tbo_services');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(NotificationBlock &$instance, $configuration) {
    // Set data uuid, generate filters_fields, generate table_fields.
    $instance->cardBuildHeader(FALSE, FALSE);
    $instance->setValue('config_name', 'notificationBlock');
    $instance->setValue('directive', 'data-ng-notification');
    $instance->setValue('class', 'block-notification');

    // Build columns table.
    // Ordering table_fields_left.
    $notifications = $allow_notifications = [];
    $send_config_modals = $open_modal_verified = 0;
    $notification_id_update = 0;
    $notification_id_verified = 0;
    $repository = \Drupal::service('tbo_services.tbo_services_repository');
    $tempStore = \Drupal::service('user.private_tempstore')->get('tbo_notifications');
    $roles = \Drupal::currentUser()->getRoles();
    $uid = \Drupal::currentUser()->id();
    $active_notifications = $repository->getNotifications($roles);
    $current_path = \Drupal::service('path.current')->getPath();
    $is_home = FALSE;
    if ($current_path == '/home' || $current_path == '/inicio') {
      $is_home = TRUE;
    }

    // Load notifications.
    if (!empty($active_notifications)) {
      foreach ($active_notifications as $key_notification => $value_notification) {
        // Validate if verified user.
        // 0 => 'Verificar cuenta', 1 => 'Actualización de datos', 2 => 'Otro'.
        $notification_type = (int) $value_notification->notification_type;
        $notification_id = (int) $value_notification->id;
        $notification_id_update = 0;
        $if_verified_account = FALSE;
        if ($notification_type == 0) {
          $if_verified_account = TRUE;

          // Validate if user validated your account.
          if (!isset($_SESSION['user_not_verified'])) {
            $exist = $repository->getNotificationDetail($notification_id, (int) $uid, TRUE);
            if (!empty($exist)) {
              // Save audit log.
              $service = \Drupal::service('tbo_core.audit_log_service');
              $service->loadName();
              // Create array data_log.
              $data_log = [
                'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
                'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
                'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
                'event_type' => 'Cuenta',
                'description' => t('Usuario verifica cuenta de TigoID'),
                'details' => t('Usuario @userName con @mail ha verificado la cuenta de TigoID',
                  [
                    '@userName' => $service->getName(),
                    '@mail' => \Drupal::currentUser()->getEmail(),
                  ]
                ),
                'old_value' => 'No disponible',
                'new_value' => 'No disponible',
              ];
              // Save audit log.
              $service->insertGenericLog($data_log);

              // Update pending.
              $update = $repository->updatePendingNotification($exist[0]->id);
            }
          }

          // Validate if verified already send.
          $already_verified = $_SESSION['notification_verified']['tbo_notifications_verified_send_' . $uid];
          if (isset($already_verified) || !isset($_SESSION['user_not_verified'])) {
            continue;
          }

          $notification_id_update = 0;
        }
        elseif ($notification_type == 1) {
          $notification_id_update = $notification_id;
          $_SESSION['notification_verified']['tbo_notification_update_' . $uid] = $notification_id_update;
        }

        // Validate relation user and notification detail.
        $exist = $repository->getNotificationDetail($notification_id, (int) $uid);
        $add = FALSE;

        if (empty($exist)) {
          $add = TRUE;

          // Add relation to first view.
          if ($if_verified_account && $is_home) {
            // Get tool service.
            $notification_service = \Drupal::service('tbo_services.tools_notifications');
            // Create relation in notification.
            $create_relation = $notification_service->createRelationNotification($uid, $notification_id, TRUE);
            // Open modal to load page.
            $open_modal_verified = 1;
          }
        }
        elseif (!empty($exist) && $if_verified_account) {
          $add = TRUE;
        }

        if ($if_verified_account) {
          $send_config_modals = 1;
          $notification_id_verified = $notification_id;
        }

        if ($add) {
          $type_notification = (int) $value_notification->type_user;
          // 0 => 'Nuevos', 1 => 'Antiguos', 2 => 'Todos'.
          if ($type_notification != 2) {
            $last_user = (int) $value_notification->id_last_user;
            if ($type_notification == 0 && $uid < $last_user) {
              continue;
            }
            if ($type_notification == 1 && $uid > $last_user) {
              continue;
            }
          }

          // Load notification.
          $notification = NotificationEntity::load($notification_id);
          $detail_notification = [
            'text' => $notification->get('text_notification')->getValue()[0]['value'],
            'button' => [
              'text' => $notification->get('button_text')->getValue()[0]['value'],
              'url' => $notification->get('button_url')->getValue()[0]['value'],
              'target_blank' => (int) $notification->get('button_target')->getValue()[0]['value'],
              'show' => (int) $notification->get('button_show')->getValue()[0]['value'],
              'click_remove' => (int) $notification->get('button_validate')->getValue()[0]['value'],
            ],
            'notification_id' => $notification_id,
            'verified_account' => $if_verified_account,
            'update_data' => $notification_id_update,
          ];
          array_push($notifications, $detail_notification);

          $allow_notifications[$notification_id] = $notification_id;
        }
      }
    }

    // Set columns.
    $instance->setValue('columns', $notifications);

    // Building var $build.
    $parameters = [
      'theme' => 'notification',
      'library' => 'tbo_services/notification',
    ];

    // Set title.
    $title = FALSE;
    if ($configuration['label_display'] == 'visible') {
      $title = $configuration['label'];
    }

    $src = '';
    $file = File::load(reset($this->configuration['others']['config']['image_alert']));
    if ($file) {
      $src = file_create_url($file->getFileUri());
    }

    $others = [
      '#title' => $title,
      '#image' => $src,
      '#margin' => $configuration['others']['config']['image_alert'],
      '#class' => $instance->getValue('class'),
    ];

    if ($send_config_modals) {
      $config_modals = \Drupal::config('tbo_services.notification');
      $config_init_modal = $config_modals->get('modal_for_the_initial_verification');
      // Get image.
      $file_init_modal = File::load(reset($config_init_modal['image']));
      if ($file_init_modal) {
        $config_init_modal['image'] = file_create_url($file_init_modal->getFileUri());
      }
      $config_init_modal['notification_id'] = $notification_id_verified;

      $config_modal_last_send_email = $config_modals->get('modal_for_the_last_verification');
      $file_last_modal = File::load(reset($config_modal_last_send_email['image']));
      if ($file_last_modal) {
        $config_modal_last_send_email['image'] = file_create_url($file_last_modal->getFileUri());
      }

      $email = \Drupal::currentUser()->getEmail();
      $replace_email = str_replace('@email_user', $email, $config_modal_last_send_email['informative_text']['value']);
      $config_modal_last_send_email['informative_text'] = $replace_email;

      $others['#config_init_modal'] = $config_init_modal;
      $others['#config_modal_last_send_email'] = $config_modal_last_send_email;
    }

    // Add environment.
    $environment = '';
    if (isset($_SESSION['company']['environment'])) {
      $environment = $_SESSION['company']['environment'];
      if ($environment == 'both') {
        $environment = 'fijo - movil';
      }
    }

    $others['#environment'] = $environment;

    $instance->cardBuildVarBuild($parameters, $others);

    // Set value default to radio buttons is rdb_query->label because
    // It's not implemented to rdb_settle.
    // Add other_config directive.
    $other_config = [
      'text_btn_detail_expanded' => t('VER MENOS'),
      'quantity_notification' => count($notifications),
      'open_modal_verified' => $open_modal_verified,
    ];

    // Set config_block.
    $config_block = $instance->cardBuildConfigBlock('/tbo_services/rest/notification?_format=json', $other_config);

    // Add configuration drupal.js object.
    $instance->cardBuildAddConfigDirective($config_block, $instance->getValue('config_name'));

    // Save allow notification by this uid.
    $tempStore->set('tbo_notifications_allowed_' . $uid, $allow_notifications);

    return $instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('authenticated', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
