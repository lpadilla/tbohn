<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\tbo_lines\Plugin\Block\HistoricalConsumptionPerMonthBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'HistoricalConsumptionPerMonthBlock' block.
 */
class HistoricalConsumptionPerMonthBlockClass {
  protected $instance;
  protected $configuration;
  
  /**
   * @param HistoricalConsumptionPerMonthBlock $instance
   * @param $config
   */
  public function setConfig(HistoricalConsumptionPerMonthBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'filters_options' => [
        'filters_fields' => [
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'date' => [
            'title' => t('Fecha'),
            'label' => 'Fecha',
            'service_field' => 'date',
            'show' => 1,
            'weight' => 1,
            'class' => '',
          ],
          'm_locals' => [
            'title' => t('M Locales'),
            'label' => 'M Locales',
            'service_field' => 'm_locals',
            'show' => 1,
            'weight' => 2,
            'class' => '',
          ],
          'm_nacionales_une' => [
            'title' => t('M Nacionales Une'),
            'label' => 'M Nacionales Une',
            'service_field' => 'm_nacionales_une',
            'show' => 1,
            'weight' => 2,
            'class' => '',
          ],
          'm_nacionales_others' => [
            'title' => t('M Nacionales otros'),
            'label' => 'M Nacionales otros',
            'service_field' => 'm_nacionales_others',
            'show' => 1,
            'weight' => 2,
            'class' => '',
          ],
          'm_international' => [
            'title' => t('M Internacionales'),
            'label' => 'M Internacionales',
            'service_field' => 'm_international',
            'show' => 1,
            'weight' => 2,
            'class' => '',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'format_file' => 'txt',
          'date_format' => 'd-m-Y',
          'hour_format' => 'g:i a',
          'redirect_url' => '#',
          'informative_text' => t('Los datos presentados son una referencia de consumo. Pueden variar dependiendo de la hora de generación del reporte'),
          'informative_text_mobile' => 'ML: Minutos locales; MNU: Minutos Nacionales Une; MNO: Minutos nacionales otros; MI: Minutos internacionales',
          'download_label' => t('DESCARGAR REPORTE'),
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'pager' => [
            'rows' => 7,
          ],
          'table_mobile' => [
            'table_fields' => [
              'date' => [
                'title' => t('Fecha'),
                'label' => 'Fecha',
                'service_field' => 'date',
                'show' => 1,
                'weight' => 1,
                'class' => '',
              ],
              'm_locals' => [
                'title' => t('M Locales'),
                'label' => 'M Locales',
                'service_field' => 'm_locals',
                'show' => 1,
                'weight' => 2,
                'class' => '',
              ],
              'm_nacionales_une' => [
                'title' => t('M Nacionales Une'),
                'label' => 'M Nacionales Une',
                'service_field' => 'm_nacionales_une',
                'show' => 1,
                'weight' => 2,
                'class' => '',
              ],
              'm_nacionales_others' => [
                'title' => t('M Nacionales otros'),
                'label' => 'M Nacionales otros',
                'service_field' => 'm_nacionales_others',
                'show' => 1,
                'weight' => 2,
                'class' => '',
              ],
              'm_international' => [
                'title' => t('M Internacionales'),
                'label' => 'M Internacionales',
                'service_field' => 'm_international',
                'show' => 1,
                'weight' => 2,
                'class' => '',
              ],
            ],
          ],
        ],
      ],
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form['others'] = [
      '#type' => 'details',
      '#title' => t('Otras configuraciones'),
      '#open' => TRUE,
    ];
    $form['others']['redirect_url'] = [
      '#type' => 'url',
      '#title' => t('URL de redirección'),
      '#default_value' => $this->configuration['others']['config']['redirect_url'],
      '#description' => t('Url de redirección por fecha'),
    ];
    $form['others']['format_file'] = [
      '#type' => 'select',
      '#title' => t('Formato de reporte'),
      '#default_value' => $this->configuration['others']['config']['format_file'],
      '#options' => [
        'txt' => t('txt'),
        'csv' => t('csv'),
        'xlsx' => t('xlsx'),
      ],
    ];
    $form['others']['date_format'] = [
      '#type' => 'textfield',
      '#title' => t('formato de fecha'),
      '#default_value' => $this->configuration['others']['config']['date_format'],
      '#description' => t('Usar formatos de fecha <a href="http://php.net/manual/function.date.php">PHP</a>'),
    ];
    $form['others']['hour_format'] = [
      '#type' => 'textfield',
      '#title' => t('Formato de hora'),
      '#description' => t('Usar formatos de hora <a href="http://php.net/manual/function.date.php">PHP</a>'),
      '#default_value' => $this->configuration['others']['config']['hour_format'],
    ];
    $form['others']['informative_text'] = [
      '#type' => 'textarea',
      '#title' => t('Texto informativo'),
      '#default_value' => $this->configuration['others']['config']['informative_text'],
      '#description' => t('Cantidad recomendada de caracteres 250'),
    ];
    $form['others']['download_label'] = [
      '#type' => 'textfield',
      '#title' => t('Label botón de descarga'),
      '#default_value' => $this->configuration['others']['config']['download_label'],
      '#description' => t('Cantidad recomendada de caracteres 18'),
      '#maxlength' => 18,
    ];
    $form['others']['informative_text_mobile'] = [
      '#type' => 'text_format',
      '#title' => t('Texto informativo'),
      '#default_value' => $this->configuration['others']['config']['informative_text_mobile']['value'],
      '#description' => t('Texto informativo para la versión mobile.'),
    ];
    $form['others']['pager'] = [
      '#type' => 'details',
      '#title' => t('Paginador'),
      '#open' => TRUE,
    ];
    $form['others']['pager']['rows'] = [
      '#type' => 'number',
      '#title' => t('Número de filas'),
      '#default_value' => $this->configuration['others']['config']['pager']['rows'],
      '#attributes' => [
        'min' => '1'
      ],
    ];
    
    //tabla mobile
    $form['others']['table_mobile'] = array(
      '#type' => 'details',
      '#title' => t('Configuraciones tabla versión mobile'),
      '#open' => TRUE,
    );
    $form['others']['table_mobile']['table_fields'] = array(
      '#type' => 'table',
      '#header' => array(t('Field'), t('Show'), t('Weight'), ''),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ),
    );
    
    $table_m_fields = $this->configuration['others']['config']['table_mobile']['table_fields'];
    uasort($table_m_fields, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    
    foreach ($table_m_fields as $id => $entity) {
      // TableDrag: Mark the invoice row as draggable.
      $form['others']['table_mobile']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['others']['table_mobile']['table_fields']['#weight'] = $entity['weight'];
      // Some invoice columns containing raw markup.
      $form['others']['table_mobile']['table_fields'][$id]['label'] = array(
        '#type' => 'textfield',
        '#default_value' => $entity['title'],
      );
      $form['others']['table_mobile']['table_fields'][$id]['show'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      );
      // TableDrag: Weight column element.
      $form['others']['table_mobile']['table_fields'][$id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight for @title', array('@title' => $entity['title'])),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => array('class' => array('fields-order-weight')),
      );
      $form['others']['table_mobile']['table_fields'][$id]['service_field'] = array(
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      );
    }
    
    $form = $this->instance->cardBlockForm($form['others'], []);
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function build(HistoricalConsumptionPerMonthBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
    
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'historicalConsumptionPerMonthBlock');
    $this->instance->setValue('directive', 'data-ng-historical-consumption-per-month');
    $this->instance->setValue('class', 'block-historical-consumption-per-month');
    $this->instance->ordering('table_options');
    $table = $this->configuration['table_options']['table_fields'];
    $table_mobile = $this->configuration['others']['config']['table_mobile']['table_fields'];
    
    //ordering table desktop
    uasort($table, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    
    //ordering table mobile
    uasort($table_mobile, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    
    //get # active columns table mobile
    $active_columns_mobile = 0;
    
    foreach ($table_mobile as $row){
      if($row['show'] == 1){
        $active_columns_mobile++;
      }
    }
    
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_lines');
    $tempstore->set('data_fixed_month_table', $table);
    $tempstore->set('data_fixed_month_table_m', $table_mobile);
    $tempstore->set('data_fixed_month_date_format', $this->configuration['others']['config']['date_format']);
    $tempstore->set('data_fixed_month_hour_format', $this->configuration['others']['config']['hour_format']);
    $tempstore->set('data_fixed_month', $_GET['month']);
    $environment = $_SESSION['serviceDetail']['serviceType'];
    
    $build = array(
      '#theme' => 'historical_consumption_per_month',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#class' => $this->instance->getValue('class'),
      '#filters' => $this->instance->getValue('filters_fields'),
      '#table' => $table,
      '#title_card' => t('Historial de consumos @month', ['@month' => $_GET['month']]),
      '#informative_text' => $this->configuration['others']['config']['informative_text'],
      '#informative_text_mobile' => ['#type' => 'markup', '#markup' => $this->configuration['others']['config']['informative_text_mobile']['value']],
      '#download_label' => $this->configuration['others']['config']['download_label'],
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#environment' => $environment,
      '#more' => $this->configuration['others']['config'],
      '#table_mobile' => $table_mobile,
      '#active_columns_mobile' => $active_columns_mobile,
      '#format' => $this->configuration['others']['config']['format_file'],
      '#url_daily' => $this->configuration['others']['config']['redirect_url'],
      '#buttons' => [['show' => 1, 'class' => 'view-filter', 'label' => 'ver']],
      '#attached' => array(
        'library' => array(
          'tbo_lines/historical-consumption-per-month'
        ),
      ),
    );
    
    $build['#cache']['max-age'] = 0;
    //build
    $this->instance->setValue('build', $build);
    
    $other_config = [
      'paginate' => $this->configuration['others']['config']['pager']['rows'],
      'format' => $this->configuration['others']['config']['format_file'],
      'environment' => $environment,
      'month' => $_GET['month'],
    ];
    $multiple_payment_url = '/tboapi/lines/detail-consumption/fixed?_format=json';
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($multiple_payment_url, $other_config);
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'historicalConsumptionPerMonthBlock');
    
    
    //log auditoria
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    
    //Create array data[]
    $data = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'event_type' => t('Servicios'),
      'description' => t('Usuario consulta historial de consumo por mes de servicio fijo'),
      'details' => t('Usuario @usuario consulta historial de consumos del mes @month del contrato @contrato de la dirección @linea',
        [
          '@usuario' => $name,
          '@month' => $_GET['month'],
          '@linea' => isset ($_SESSION['serviceDetail']['address']) ? $_SESSION['serviceDetail']['address'] : 'No disponible',
          '@contrato' => isset ($_SESSION['serviceDetail']['contractId']) ? $_SESSION['serviceDetail']['contractId'] : 'No disponible',
        ]),
      'old_value' => t('No disponible'),
      'new_value' => t('No disponible'),
    ];
    
    //Save audit log
    $service_log->insertGenericLog($data);
    //return build
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
    if ((in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) && $_SESSION['serviceDetail']['productId'] == 13) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }
}