<?php

namespace Drupal\tbo_account\Plugin\Config\Block;

use Drupal\tbo_account\Plugin\Block\DownloadContractBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'DownloadContractBlockClass' block.
 */
class DownloadContractBlockClass {
  protected $configuration;
  protected $instance;

  /**
   * @param \Drupal\tbo_account\Plugin\Block\DownloadContractBlock $instance
   * @param string $config
   */
  public function setConfig(DownloadContractBlock &$instance, &$config) {
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
          'title' => [
            'title' => t('Titulo'),
            'label' => t('titulo'),
            'service_field' => 'title',
            'show' => 1,
            'weight' => 1,
          ],
          'text_inform' => [
            'title' => t('Texto informativo'),
            'label' => t('Texto informativo'),
            'service_field' => 'text_inform',
            'show' => 1,
            'weight' => 2,
          ],
          'consultation' => [
            'title' => t('Nombre del botón'),
            'label' => t('Nombre del botón'),
            'service_field' => 'consultation',
            'show' => 1,
            'weight' => 3,
          ],
          'documentationTitle' => [
            'title' => t('Nombre del documento titulo'),
            'label' => t('Nombre del documento titulo'),
            'service_field' => 'documentationTitle',
            'show' => 1,
            'weight' => 4,
          ],
          'resource' => [
            'title' => t('Nombre del Recurso titulo'),
            'label' => t('Nombre del Recurso titulo'),
            'service_field' => 'resource',
            'show' => 1,
            'weight' => 5,
          ],
          'messageError' => [
            'title' => t('Texto mensaje de error del servicio'),
            'label' => t('Texto mensaje de error del servicio'),
            'service_field' => 'messageError',
            'show' => 1,
            'weight' => 6,
          ],
        ],
        'link_label' => 'Líneas',
      ],
      'buttons' => [
        'table_fields' => [
          'modal_cancel' => [
            'title' => t('Boton Cancelar'),
            'label' => t('Cancelar'),
            'service_field' => 'modal_cancel',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
          'modal_accept' => [
            'title' => t('Boton Aceptar'),
            'label' => t('Aceptar'),
            'service_field' => 'modal_accept',
            'show' => 1,
            'active' => 1,
            'update_label' => 1,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'order_select' => 'fixed',
          'select_check' => 'fixed',
          'titlePopup' => '',
          'description' => '',
          'service_param' => [
            'value' => 0,
            'show' => 1,
          ],
          'show_margin' => [
            'show_margin_card' => 1,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, &$form_state) {

    $field['order_select'] = [
      '#type' => 'select',
      '#title' => t('orden del selector'),
      '#options' => [
        'fixed' => t("Fijo"),
        'movile' => t("Móvil"),
      ],
      '#default_value' => $this->configuration['others']['config']['order_select'],
    ];
    $field['select_check'] = [
      '#type' => 'select',
      '#title' => t('Preseleccción'),
      '#options' => [
        'fixed' => t("Fijo"),
        'movile' => t("Móvil"),
      ],
      '#default_value' => $this->configuration['others']['config']['select_check'],
    ];
    $field['titlePopup'] = [
      '#type' => 'textfield',
      '#title' => t('Ingrese Titulo del popup'),
      '#label' => t('Ingrese Titulo del popup'),
      '#default_value' => $this->configuration['others']['config']['titlePopup'],
    ];
    $field['description'] = [
      '#type' => 'textarea',
      '#title' => t('Ingrese la descripción del popup'),
      '#label' => t('Ingrese la descripción del popup'),
      '#default_value' => $this->configuration['others']['config']['description'],
    ];

    $field['service_param'] = [
      '#title' => t("Configuración de parámetro"),
      '#type' => 'details',
      '#description' => t("Configuraciones para el envío del parametro 'document' al servicio."),
      '#open' => TRUE,
    ];

    $field['service_param']['show'] = [
      '#type' => 'checkbox',
      '#title' => t("Usar el parámetro"),
      '#default_value' => $this->configuration['others']['config']['service_param']['show'],
      '#description' => t("Seleccionar  si se desea incluir el siguiente valor del parámetro en la consulta."),
    ];

    $field['service_param']['value'] = [
      '#type' => 'number',
      '#title' => t("Valor del parámetro"),
      '#default_value' => $this->configuration['others']['config']['service_param']['value'],
      '#min' => 0,
    ];

    $form = $this->instance->cardBlockForm($field);

    $form['buttons']['#title'] = t('Configuraciones de los botones del modal');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(DownloadContractBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Log.
    $data_log = [];
    $segment = 'segmento';
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'DownloadContractBlock');
    $this->instance->setValue('directive', 'ng-download-contract');
    $this->instance->setValue('class', 'download-contract-delivery');

    $columns = [];
    // Ordering table_fields.
    $this->instance->ordering('table_fields', 'table_options');
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
          $columns[$key_field]['value'] = $this->configuration['others']['config']['informative_text'];
        }
        unset($classes);
      }
      else {
        unset($field[$key_field]);
      }
    }

    $this->instance->setValue('columns', $columns);

    // Variable $build con los datos que se necesitan en el tema.
    $title = FALSE;
    if ($this->configuration['table_options']['table_fields']['title']['show'] == 1) {
      $title = $this->configuration['table_options']['table_fields']['title']['label'];
    }

    // Set title.
    $textInform = FALSE;
    if ($this->configuration['table_options']['table_fields']['text_inform']['show'] == 1) {
      $textInform = $this->configuration['table_options']['table_fields']['text_inform']['label'];
    }
    $consultationBottom= FALSE;
    if($this->configuration['table_options']['table_fields']['consultation']['show'] == 1){
      $consultationBottom = $this->configuration['table_options']['table_fields']['consultation']['label'];
    }
    $titleDocument = FALSE;
    if($this->configuration['table_options']['table_fields']['documentationTitle']['show'] == 1){
      $titleDocument = $this->configuration['table_options']['table_fields']['documentationTitle']['label'];
    }
    $titleResource = FALSE;
    if($this->configuration['table_options']['table_fields']['resource']['show'] == 1){
      $titleResource = $this->configuration['table_options']['table_fields']['resource']['label'];
    }
    $validation = $this->configuration['others']['config']['select_check'];
    $select = $this->configuration['others']['config']['order_select'];
    $titlePopup = $this->configuration['others']['config']['titlePopup'];
    $description = $this->configuration['others']['config']['description'];

    $uuid = $this->instance->getValue('uuid');
    $uuid2 = str_replace("-","",$uuid);
  
    $placeHolder = $this->configuration['others']['config']['select_check'] == 'movile' ?
      t("Ingreso el Número de línea") : t("Ingreso el Número del contrato");
    
    $build = [
      '#theme' => 'download_contract_daily',
      '#uuid' => $this->instance->getValue('uuid'),
      '#uuid2' => $uuid2,
      '#directive' => $this->instance->getValue('directive'),
      '#fields' => $this->instance->getValue('columns'),
      '#filters' => $this->instance->getValue('filters'),
      '#title' => $title,
      '#validation' => $validation,
      '#select' => $select,
      '#titleDocument' => $titleDocument,
      '#titleResource' => $titleResource,
      '#description' => $description,
      '#titlePopup' => $titlePopup,
      '#textInform' => $textInform,
      '#buttons' => $consultationBottom,
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#place_holder' => $placeHolder,
      '#class' => $this->instance->getValue('class'),
      '#angular_class' => 'download_contract',
      '#attached' => [
        'library' => [
          'tbo_account/download-contract',
        ],
      ],
      '#buttons_modal' => $this->configuration['buttons']['table_fields'],
      '#plugin_id' => $this->instance->getPluginId(),
    ];

    // Create Audit log.
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Contratos',
      'description' => t('Usuario accede a la consulta de Documentos y contratos'),
      'details' => t('Usuario @userName accede a consulta de Documentos y contratos',
        [
          '@userName' => $name,
        ]
      ),
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $service_log->insertGenericLog($data_log);

    // Build.
    $this->instance->setValue('build', $build);

    // Setting angular js configuration vars.
    $other_config = [
      'config_columns' => $this->instance->getValue('uuid'),
      'order_select' => $this->configuration['others']['config']['order_select'],
      'select_check' => $this->configuration['others']['config']['select_check'],
      'document' => $this->configuration['others']['config']['service_param']['value'],
      'use_document' => $this->configuration['others']['config']['service_param']['show'],
      'uuid2' => $uuid2,
      'uuid' => $uuid,
    ];
    // The necessary data is loaded for the angular directive, the rest is sent.
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-account/download/contract?_format=json', $other_config);
    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'DownloadContractBlock');

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
