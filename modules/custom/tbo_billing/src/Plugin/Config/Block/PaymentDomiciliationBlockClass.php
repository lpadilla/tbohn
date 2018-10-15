<?php

namespace Drupal\tbo_billing\Plugin\Config\Block;

use Drupal\tbo_billing\Plugin\Block\PaymentDomiciliationBlock;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Services\AuditLogService;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'PaymentDomiciliationBlockClass' block.
 */
class PaymentDomiciliationBlockClass {
  protected $api;
  protected $configuration;
  protected $instance;
  protected $log;

  /**
   *
   */
  public function __construct(TboApiClientInterface $api, AuditLogService $log) {
    $this->api = $api;
    $this->log = $log;
  }

  /**
   * @param \Drupal\tbo_billing\Plugin\Block\PaymentDomiciliationBlock $instance
   * @param $config
   */
  public function setConfig(PaymentDomiciliationBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'others' => [
        'config' => [
          'show_add_card_button' => 0,
          'path_add_card_button' => '/hogar/pago_recurrente/mis_tarjetas/agregar',
          'show_add_programmer_payment_button' => 0,
          'path_add_programmer_payment_button' => '/hogar/pago_recurrente/programar_pago',
          'show_my_cards_link' => 0,
          'path_my_cards_link' => '/hogar/pago_recurrente/mis_tarjetas',
          'show_edit_programmer_payment_button' => 0,
          'path_edit_programmer_payment_button' => '/hogar/pago_recurrente/programar_pago',
          'description_block_configured_payment' => '',
          'description_block_payment_not_configured' => '',
          'description_block_payment_method_debit' => '',
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'show_card' => [
            'title' => t("Mostrar tarjeta de crédito"),
            'label' => t('Tarjeta de crédito'),
            'service_field' => 'card',
            'show' => 1,
            'weight' => 1,
            'class' => 'double-top-and-bottom-padding',
            'show_margin' => [
              'show_margin_card' => 1,
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, &$form_state) {

    // $table_fields: variable que contiene la configuracion por defecto de las columnas de la tabla.
    $table_fields = $this->configuration['table_options']['table_fields'];

    if (!empty($table_fields)) {
      // table_options: fieldset que contiene todas las columnas de la tabla.
      $form['table_options'] = [
        '#type' => 'details',
        '#title' => t('Configuraciones tabla'),
        '#open' => TRUE,
      ];
      $form['table_options']['table_fields'] = [
        '#type' => 'table',
        '#header' => [t('Title'), t('Label'), t('Show'), ''],
        '#empty' => t('There are no items yet. Add an item.'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'fields-order-weight',
          ],
        ],
      ];

      // Se ordenan los filtros segun lo establecido en la configuración.
      uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

      // Se crean todas las columnas de la tabla que mostrara la información.
      foreach ($table_fields as $id => $entity) {
        // TableDrag: Mark the table row as draggable.
        $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
        // TableDrag: Sort the table row according to its existing/configured weight.
        $form['table_options']['table_fields']['#weight'] = $entity['weight'];

        // Some table columns containing raw markup.
        $form['table_options']['table_fields'][$id]['title'] = [
          '#plain_text' => $entity['title'],
        ];

        // Some table columns containing raw markup.
        if (isset($entity['label'])) {
          $form['table_options']['table_fields'][$id]['label'] = [
            '#type' => 'textfield',
            '#default_value' => $entity['label'],
          ];
        }
        else {
          $form['table_options']['table_fields'][$id]['label'] = [
            '#type' => 'label',
            '#default_value' => '',
          ];
        }

        $form['table_options']['table_fields'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['show'],
        ];

        // TableDrag: Weight column element.
        $form['table_options']['table_fields'][$id]['weight'] = [
          '#type' => 'weight',
          '#title' => t('Weight for @title', ['@title' => $entity['title']]),
          '#title_display' => 'invisible',
          '#default_value' => $entity['weight'],
          // Classify the weight element for #tabledrag.
          '#attributes' => ['class' => ['fields-order-weight']],
        ];

        $form['table_options']['table_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }

    $others = $this->configuration['others']['config'];
    $form['others'] = [
      '#type' => 'details',
      '#title' => t('Otras configuraciones'),
      '#open' => TRUE,
    ];

    // Descripcion del bloque.
    $form['others']['config']['description_block_configured_payment'] = [
      '#type' => 'text_format',
      '#title' => t("Descripción cuando ya se ha programado el pago"),
      '#default_value' => isset($others['description_block_configured_payment']['value']) ? t($others['description_block_configured_payment']['value']) : NULL,
    ];

    // Descripcion del bloque.
    $form['others']['config']['description_block_payment_not_configured'] = [
      '#type' => 'text_format',
      '#title' => t("Descripción cuando no se ha programado el pago"),
      '#default_value' => isset($others['description_block_payment_not_configured']['value']) ? t($others['description_block_payment_not_configured']['value']) : NULL,
    ];

    // Descripcion del bloque.
    $form['others']['config']['description_block_payment_method_debit'] = [
      '#type' => 'text_format',
      '#title' => t("Descripción cuando el metodo de pago es Debito"),
      '#default_value' => isset($others['description_block_payment_method_debit']['value']) ? t($others['description_block_payment_method_debit']['value']) : NULL,
    ];

    $form['others']['config']['actions'] = [
      '#type' => 'details',
      '#title' => t('Botones'),
      '#open' => TRUE,
      '#weight' => 1,
    ];

    $form['others']['config']['actions']['show_add_card_button'] = [
      '#type' => 'checkbox',
      '#title' => t('Agregar Tarjeta'),
      '#default_value' => $others['actions']['show_add_card_button'],
    ];
    $form['others']['config']['actions']['path_add_card_button'] = [
      '#type' => 'textfield',
      '#title' => t('Url del boton ' . t('Agregar Tarjeta')),
      '#default_value' => $others['actions']['path_add_card_button'],
      '#states' => [
        'visible' => [
          ':input[name="settings[others][config][actions][show_add_card_button]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['others']['config']['actions']['show_add_programmer_payment_button'] = [
      '#type' => 'checkbox',
      '#title' => t('Programar pago'),
      '#default_value' => $others['actions']['show_add_programmer_payment_button'],
    ];

    $form['others']['config']['actions']['show_my_cards_link'] = [
      '#type' => 'checkbox',
      '#title' => t('Mis Tarjetas'),
      '#default_value' => $others['actions']['show_my_cards_link'],
    ];

    $form['others']['config']['actions']['show_edit_programmer_payment_button'] = [
      '#type' => 'checkbox',
      '#title' => t('Desprogramar'),
      '#default_value' => $others['actions']['show_edit_programmer_payment_button'],
    ];

    $form['others']['config']['show_margin'] = [
      '#type' => 'details',
      '#title' => t('Configurar Margenes del card'),
      '#open' => TRUE,
    ];

    $form['others']['config']['show_margin']['show_margin_card'] = [
      '#type' => 'checkbox',
      '#default_value' => $others['show_margin']['show_margin_card'],
      '#title' => t('Agregar margen al card de datos'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(PaymentDomiciliationBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'paymentDomiciliationBlock');
    $this->instance->setValue('directive', 'data-ng-payment-domiciliation');
    $this->instance->setValue('class', 'block-payment-domiciliation');

    // Set session var.
    $this->instance->cardBuildSession();

    $parameters = [
      'theme' => 'payment_domiciliation',
      'library' => 'tbo_billing/payment-domiciliation',
    ];

    $_SESSION['detail_invoice_url'] = $GLOBALS['base_url'] . \Drupal::request()->getRequestUri();

    // Set var session recurring.
    $_SESSION['recurring_info_payment'] = FALSE;

    $service = \Drupal::service('tbo_billing.payment_domiciliation');
    $cards = $service->getMyCards();
    $render = [];

    if (isset($_SESSION['popUp'])) {
      if ($cards) {
        $render = \Drupal::formBuilder()->getForm('Drupal\tbo_billing\Form\SchedulePaymentForm', $cards);
      }
      unset($_SESSION['popUp']);
    }

    $config_card = [];
    // --- Generate Manage Cards.
    $this->log->loadName();

    // Create array data_log[].
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturación',
      'description' => 'Usuario solicita administrar sus tarjetas de crédito',
      'details' => 'Usuario ' . $this->log->getName() . ' solicita administrar sus tarjetas de crédito',
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $this->log->insertGenericLog($data_log);

    // Parameters for service.
    $params['tokens'] = [
      'docType' => isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '',
      'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
    ];

    // No generate exception.
    $params['no_exception'] = TRUE;
    $result = [];
    if ($cards) {
      foreach ($cards as $item) {
        array_push($result, [
          'number' => $item->cardInfo,
          'brand' => $item->cardBrand,
          'token' => $item->cardToken,
        ]);
      }
    }

    $data_popup_cards = [
      'title' => t('Mis tarjetas'),
      'delete_way' => 'outBlock',
    ];
    // --- end Manage Cards.
    $others = [
      '#fields' => $this->configuration['table_options']['table_fields'],
      '#schedule_payment_form' => \Drupal::formBuilder()->getForm('Drupal\tbo_billing\Form\SchedulePaymentForm', $cards),
      '#config' => $this->configuration,
      '#pop_up' => $render,
      '#cards' => $cards,
      '#data_popup_cards' => $data_popup_cards,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    if (isset($_SESSION['sendDetail']['contractId'])) {
      $contract = $_SESSION['sendDetail']['contractId'];
    }
    else {
      $contract = FALSE;
    }

    if (isset($_SESSION['environment'])) {
      $type = $_SESSION['environment'];
    }
    else {
      $type = FALSE;
    }

    $other_config = [
      'contractId' => $contract,
      'type' => $type,
    ];

    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/billing/domiciliation/' . $this->instance->getValue('uuid') . '?_format=json', $other_config);
    $this->instance->cardBuildVarBuild($parameters, $others);
    $this->instance->cardBuildAddConfigDirective($config_block, 'paymentDomiciliationBlock');

    // Guardado log auditoria.
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();

    // 600006858393.
    $contractId = isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '';
    $invoiceId = isset($_SESSION['sendDetail']['invoiceId']) ? $_SESSION['sendDetail']['invoiceId'] : '';

    // Create array data[].
    $data = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturación',
      'description' => 'Usuario consulta pagos automáticos.',
      'details' => 'Usuario ' . $service->getName() . ' consulta pagos automáticos de la factura ' . $invoiceId . ' asociada al número de contrato ' . $contractId . '.',
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];

    // Save audit log.
    $service->insertGenericLog($data);

    $cid = 'config:block:' . $this->instance->getValue('uuid');
    $render = [
      'actions' => $this->configuration['others']['config']['actions'],
      'table_fields' => $this->configuration['table_options']['table_fields'],
      'card_tokens' => $result,
    ];
    $data = $render;
    \Drupal::cache()->set($cid, $data);

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
