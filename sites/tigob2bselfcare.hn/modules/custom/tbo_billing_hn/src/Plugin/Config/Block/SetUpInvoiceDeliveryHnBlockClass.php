<?php

namespace Drupal\tbo_billing_hn\Plugin\Config\Block;

use Drupal\tbo_billing\Plugin\Block\SetUpInvoiceDeliveryBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'SetUpInvoiceDeliveryBlockClass' block.
 */
class SetUpInvoiceDeliveryHnBlockClass extends SetUpInvoiceDeliveryBlockClass {
  protected $configuration;
  protected $instance;

  /**
   * @param SetUpInvoiceDeliveryBlock $instance
   * @param $config
   */
  public function setConfig(SetUpInvoiceDeliveryBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'filters_fields' => [],
      'table_fields' => [
        'title' => [
          'title' => t('Titulo'),
          'label' => t('Datos de Facturación'),
          'service_field' => 'title', 'show' => 1, 'weight' => 1, 'class' => '4-columns',
        ],
        'show_invoice_billing' => [
          'title' => t('Facturación'),
          'label' => t('Facturación'),
          'service_field' => 'show_invoice_billing',
          'show' => 1, 'weight' => 2, 'class' => '4-columns'
        ],
        'show_invoice_email' => [
          'title' => t('Email'),
          'label' => t('Correo electrónico'),
          'service_field' => 'show_invoice_email',
          'show' => 1, 'weight' => 4, 'class' => '4-columns'
        ],
        'show_invoice_address' => [
          'title' => t('Dirección'),
          'label' => t('Dirección'),
          'service_field' => 'show_invoice_address',
          'show' => 1, 'weight' => 3, 'class' => '4-columns'
        ],
        'show_invoice_city' => [
          'title' => t('Ciudad'),
          'label' => t('Ciudad'),
          'service_field' => 'show_invoice_city',
          'show' => 1, 'weight' => 5, 'class' => '4-columns',
        ],
        'show_invoice_informative_text' => [
          'title' => t('Texto Informativo'),
          'label' => t('Texto Informativo'),
          'service_field' => 'show_invoice_informative_text',
          'show' => 1, 'weight' => 6, 'class' => '2-columns'
        ],
      ],
      'others_display' => [
        'modal_digital' => [
          'title' => t('Factura Digital'),
          'label' => t('Digital'),
          'service_field' => 'modal_digital', 'show' => 1, 'active' => 1,
        ],
        'modal_impresa' => [
          'title' => t('Factura Impresa'),
          'label' => t('Impresa'),
          'service_field' => 'modal_impresa', 'show' => 1, 'active' => 1,
        ],
        'modal_detail' => [
          'title' => t('Detalle'),
          'label' => t('Detalle de facturación'),
          'service_field' => 'modal_detail', 'show' => 1, 'active' => 1,
        ],
        'modal_title' => [
          'title' => t('Titulo'),
          'label' => t('Datos de facturación'),
          'service_field' => 'modal_title', 'show' => 1,
        ],
        'modal_label' => [
          'title' => t('Label'),
          'label' => t('Recibir está factura'),
          'service_field' => 'modal_label', 'show' => 1,
        ],
      ],
      'buttons' => [
        'action_card_update' => [
          'title' => t('Boton Modificar'),
          'label' => t('Modificar'),
          'description' => t('Solo se'),
          'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
        ],
        'action_card_get' => [
          'title' => t('Reciba su factura digital'),
          'label' => t('Reciba su factura digital'),
          'service_field' => 'action_card', 'show' => 1, 'active' => 1, 'update_label' => 1,
        ],
        'modal_cancel' => [
          'title' => t('Boton Cancelar'),
          'label' => t('Cancelar'),
          'service_field' => 'modal_cancel', 'show' => 1, 'active' => 1, 'update_label' => 1,
        ],
        'modal_accept' => [
          'title' => t('Boton Aceptar'),
          'label' => t('Aceptar'),
          'service_field' => 'modal_accept', 'show' => 1, 'active' => 1, 'update_label' => 1,
        ],
        'terms_condition' => [
          'title' => t('Enlace terminos y condiciones'),
          'label' => t(''),
          'url' => t('terms-conditions'),
          'url_description' => t('Ejemplo terms-conditions o http://www.tigoune.com/terms-conditions'),
          'service_field' => 'action_card', 'show' => 1,
        ],
      ],
      'others' => [
        'informative_text' => t('Requetereemplace su factura impresa por una versión electrónica y ayuda a conservar el medio ambiente'),
        'show_margin' => [
          'show_margin_card' => 1,
        ],
      ],
    );
  }

  /*
   * {@inheritdoc}
   */
  public function blockForm($form, &$form_state) {

    $field['informative_text'] = [
      '#type' => 'textfield',
      '#title' => t('Texto Informativo'),
      '#default_value' => $this->configuration['others']['informative_text'],
    ];

    $form = $this->instance->cardBlockForm($field);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(SetUpInvoiceDeliveryBlock &$instance, &$config) {
    //Set values for duplicate cards
    $this->instance = &$instance;
    $this->configuration = &$config;

    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'sepUpInvoiceDeliveryBlock');
    $this->instance->setValue('directive', 'data-ng-invoice-delivery');
    $this->instance->setValue('class', 'historical-invoice set-up-invoice-delivery');

    $columns = [];
    //Ordering table_fields
    $this->instance->ordering('table_fields');
    foreach ($this->instance->getValue('table_fields') as $key_field => $field) {
      if ($field['show'] == 1) {
        $columns[$key_field]['key'] = $key_field;
        $columns[$key_field]['title'] = $field['title'];
        $columns[$key_field]['label'] = $field['label'];
        $classes = ["field-" . $field['service_field'], $field['class']];
        $columns[$key_field]['class'] = implode(" ", $classes);
        $columns[$key_field]['service_field'] = $field['service_field'];
        $columns[$key_field]['position'] = $field['weight'];
        if ($field['service_field'] == 'show_invoice_informative_text') {
          $columns[$key_field]['value'] = $this->configuration['others']['informative_text'];
        }
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    $this->instance->setValue('columns', $columns);

    //Se construye la variable $build con los datos que se necesitan en el tema

    $modal = [
      'data' => $this->configuration['others_display'],
      'environment' => $_SESSION['environment'],
    ];

    $build = array(
      '#theme' => 'set_up_invoice_delivery_hn',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#fields' => $this->instance->getValue('columns'),
      '#filters' => $this->instance->getValue('filters'),
      '#modal' => $modal,
      '#buttons' => $this->configuration['buttons'],
      '#margin' => $this->configuration['others']['show_margin'],
      '#class' => $this->instance->getValue('class'),
      '#attached' => array(
        'library' => array(
          'tbo_billing/set-up-invoice-delivery',
          'tbo_general/messaging',
        ),
      ),
    );

    //build
    $this->instance->setValue('build', $build);

    $other_config = [
      'environment' => $_SESSION['environment'],
    ];

    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock('/tbo_billing/rest/invoice-delivery?_format=json', $other_config);

    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block);

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