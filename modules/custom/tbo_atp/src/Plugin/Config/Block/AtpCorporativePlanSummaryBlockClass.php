<?php

namespace Drupal\tbo_atp\Plugin\Config\Block;

use Drupal\tbo_atp\Plugin\Block\AtpCorporativePlanSummaryBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Manage config a 'AtpCorporativePlanSummaryBlockClass' block.
 */
class AtpCorporativePlanSummaryBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param AtpCorporativePlanSummaryBlock $instance
   * @param $config
   */
  public function setConfig(AtpCorporativePlanSummaryBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'table_options' => [
        'table_fields' => [
          'total_value' => [
            'title' => t('Valor Total'),
            'label' => t('Valor Total'),
            'service_field' => 'id',
            'show' => 1,
          ],
          'services_amount' => [
            'title' => t('Paquetes Servicios'),
            'label' => t('Paquetes Servicios'),
            'service_field' => 'paquetes_servicios',
            'show' => 1,
          ],
          'associated_lines'  => [
            'title' => t('Líneas Asociadas'),
            'label' => t('Líneas Asociadas'),
            'service_field' => 'lineas_asociadas',
            'show' => 1,
          ],
          'billing_cycle'  => [
            'title' => t('Ciclo de Facturación'),
            'label' => t('Ciclo de Facturación'),
            'service_field' => 'ciclo_facturacion',
            'show' => 1,
          ],
          'minimum_rank'  => [
            'title' => t('Rango Mínimo'),
            'label' => t('Rango Mínimo'),
            'service_field' => 'rango_minimo',
            'show' => 1,
          ],
          'maximum_rank'  => [
            'title' => t('Rango Máximo'),
            'label' => t('Rango Máximo'),
            'service_field' => 'rango_maximo',
            'show' => 1,
          ],
          'profiles'  => [
            'title' => t('Perfiles'),
            'label' => t('Perfiles'),
            'service_field' => 'perfiles',
            'show' => 1,
          ]
        ]
      ],
      'url_details' => '/',
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_card' => 1,
          ]
        ]
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();

    $form['others_display']['table_fields']['#header'] = [t('Field'), t('Label'), t('Show')];
    $form['url_details'] = [
      '#type' => 'textfield',
      '#title' => t('Url de redirección a los Detalles del Plan'),
      '#default_value' => $this->configuration['url_details']
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['url_details'] = $form_state->getValue('url_details');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, filters_fields, table_fields
    $this->instance->cardBuildHeader(FALSE);
    $this->instance->setValue('config_name', 'AtpCorporativePlanSummaryBlock');
    $this->instance->setValue('directive', 'data-ng-atp-corporative-plan-summary');
    $this->instance->setValue('class', 'block-atp-corporative-plan-summary');

    // Se construye la variable $build con los datos que se necesitan en el tema
    $parameters = [
      'theme' => 'atp_corporative_plan_summary',
      'library' =>
        'tbo_atp/atp-corporative-plan-summary',
    ];

    $val_atp = \Drupal::service('tbo_atp.general_service')->validateAtpServices();

    $otherConfig = [
      '#url_details' => $this->configuration['url_details'],
      '#val_atp' => $val_atp,
    ];
    $this->instance->cardBuildVarBuild($parameters, $otherConfig);

    // Add config drupal object js
    $other_config = [
      'environment' => $_SESSION['environment'],
      'message_error_404_default' => t('No se encontró información de la Cuenta Cliente ingresada'),
    ];

    // Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock('/tbo_atp/rest/atp-corporative-plan-summary?_format=json', $other_config);

    // Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, $this->instance->getValue('config_name'));

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

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }
}
