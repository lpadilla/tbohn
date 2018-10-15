<?php

namespace Drupal\tbo_account\Plugin\Config\Block;

use Drupal\tbo_account\Plugin\Block\ManageCompanyBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'ManageCompanyBlockClass' block.
 */
class ManageCompanyBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_account\Plugin\Block\ManageCompanyBlock $instance
   * @param $config
   */
  public function setConfig(ManageCompanyBlock &$instance, &$config) {
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
          'document_number' => [
            'title' => t('Número de documento'),
            'label' => 'Número de documento',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 1,
            'class' => '3-columns',
            'validate_length' => 145,
          ],
          'name' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'name',
            'show' => 1,
            'weight' => 2,
            'class' => '3-columns',
            'validate_length' => 200,
          ],
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'document_number' => [
            'title' => t('Número de documento'),
            'label' => 'Número de documento',
            'service_field' => 'document_number',
            'show' => 1,
            'weight' => 1,
          ],
          'name' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'name',
            'show' => 1,
            'weight' => 3,
          ],
          'status' => [
            'title' => t('Activo'),
            'label' => 'Activo',
            'service_field' => 'status',
            'show' => 1,
            'weight' => 3,
          ],
          'delete' => [
            'title' => t('Eliminar'),
            'label' => 'Eliminar',
            'service_field' => 'delete',
            'show' => 1,
            'weight' => 4,
          ],
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
  public function build(ManageCompanyBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, filters_fields, table_fields.
    $this->instance->cardBuildHeader(TRUE, TRUE);
    $this->instance->setValue('directive', 'data-ng-companies-manage');
    $this->instance->setValue('config_name', 'companiesManageBlock');
    $this->instance->setValue('class', 'wrapper-create block-manage-companies');

    // Set session var.
    $this->instance->cardBuildSession();

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'manage_company',
      'library' => 'tbo_account/companies-manage',
    ];

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

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/account/manage?_format=json');

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'companiesManageBlock');

    // Se guarda el log de auditoria $event_type, $description, $details = NULL.
    $this->instance->cardSaveAuditLog('Cuenta', 'Consulta listado de empresas', 'consultó listado de empresas');

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
