<?php

namespace Drupal\tbo_user\Plugin\Config\Block;

use Drupal\tbo_user\Plugin\Block\EmulateAdminCompanySession;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'EmulateAdminCompanySessionBlockClass' block.
 */
class EmulateAdminCompanySessionBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_user\Plugin\Block\EmulateAdminCompanySession $instance
   * @param $config
   */
  public function setConfig(EmulateAdminCompanySession &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [
          'name' => ['title' => t('Empresa'), 'label' => 'Empresa', 'service_field' => 'company', 'show' => 1, 'weight' => 1, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => 200],
          'full_name' => ['title' => t('Nombre Admin empresa'), 'label' => 'Nombre Admin empresa', 'service_field' => 'admin_company', 'show' => 1, 'weight' => 2, 'input_type' => 'text', 'class' => '3-columns', 'validate_length' => 300],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'name' => ['title' => t('Empresa'), 'label' => 'Empresa', 'type' => 'company', 'service_field' => 'name', 'show' => 1, 'weight' => 1],
          'full_name' => ['title' => t('Nombre Admin empresa'), 'label' => 'Nombre Admin empresa', 'type' => 'user', 'service_field' => 'full_name', 'show' => 1, 'weight' => 2],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [
            'number_pages' => 10,
            'number_rows_pages' => 10,
          ],
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
        ],
      ],
      'not_show_class' => [
        'columns' => 1,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = $this->instance->cardBlockForm();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmulateAdminCompanySession &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('config_name', 'emulateAdminCompanySessionBlock');
    $this->instance->setValue('directive', 'data-ng-emulate-session');
    $this->instance->setValue('class', 'wrapper-emulate block-emulate-admin-company-session');

    // Set session var.
    $this->instance->cardBuildSession(TRUE, [], 'tbo_user');

    // Set title.
    $title = FALSE;
    if ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    // Parameter additional.
    $others = [
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'emulate_session',
      'library' => 'tbo_user/emulate-session',
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/emulate/session?_format=json');

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'emulateAdminCompanySessionBlock');

    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();
    if (in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }

}
