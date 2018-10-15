<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\tbo_lines\Plugin\Block\ConsumptionDetailDataBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'ConsumptionDetailDataBlock' block.
 */
class ConsumptionDetailDataBlockClass {
  protected $instance;
  protected $configuration;
  
  /**
   * @param ConsumptionDetailDataBlock $instance
   * @param $config
   */
  public function setConfig(ConsumptionDetailDataBlock &$instance, &$config) {
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
          'start_date_data' => [
            'title' => t('Desde'),
            'label' => 'Desde',
            'service_field' => 'start_date_data',
            'identifier' => 'start_date_data',
            'show' => 1,
            'weight' => 1,
            'class' => '',
            'date_line' => 1,
          ],
          'end_date_data' => [
            'title' => t('Hasta'),
            'label' => 'Hasta',
            'service_field' => 'end_date_data',
            'identifier' => 'end_date_data',
            'show' => 1,
            'weight' => 1,
            'class' => '',
            'date_line' => 1,
          ],
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
          'hour' => [
            'title' => t('Hora'),
            'label' => 'Hora',
            'service_field' => 'hour',
            'show' => 1,
            'weight' => 2,
            'class' => '',
          ],
          'consumption' => [
            'title' => t('Consumo'),
            'label' => 'Consumo',
            'service_field' => 'consumption',
            'show' => 1,
            'weight' => 3,
            'class' => '',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'format_file' => 'txt',
          'informative_text' => t('Los datos presentados son una referencia de consumo. Pueden variar dependiendo de la hora de generación del reporte'),
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'pager' => [
            'rows' => 7,
          ],
          'table_mobile' => [
            'table_fields' => [
              'date_hour' => [
                'title' => t('Fecha/Hora'),
                'label' => 'Fecha/Hora',
                'service_field' => 'date_hour',
                'show' => 1,
                'weight' => 1,
              ],
              'consumption' => [
                'title' => t('Consumo'),
                'label' => 'Consumo',
                'service_field' => 'consumption',
                'show' => 1,
                'weight' => 3,
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
    $form['others']['informative_text'] = [
      '#type' => 'textarea',
      '#title' => t('Texto informativo'),
      '#default_value' => $this->configuration['others']['config']['informative_text'],
      '#description' => t('Cantidad recomendada de caracteres 250'),
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
        '#plain_text' => $entity['title'],
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
  public function build(ConsumptionDetailDataBlock &$instance, &$config) {
    $global_config = \Drupal::config('tbo_lines.consumptions_filters');
    $days_query = $global_config->get('days_query');
    $this->instance = &$instance;
    $this->configuration = &$config;
    
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'consumptionDetailDataBlock');
    $this->instance->setValue('directive', 'data-ng-consumption-detail-data');
    $this->instance->setValue('class', 'block-consumption-detail-data');
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
    
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_lines');
    $tempstore->set('data_table_mobile', $table_mobile);
    $environment = $_SESSION['serviceDetail']['serviceType'];
    
    $build = array(
      '#theme' => 'consumption_detail_data',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#class' => $this->instance->getValue('class'),
      '#filters' => $this->instance->getValue('filters_fields'),
      '#table' => $table,
      '#informative_text' => $this->configuration['others']['config']['informative_text'],
      '#informative_text_query' => t('El rango de fechas no debe ser superior a @days días', array('@days' => $days_query)),
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#environment' => $environment,
      '#more' => $this->configuration['others']['config'],
      '#table_mobile' => $table_mobile,
      '#format' => $this->configuration['others']['config']['format_file'],
      '#buttons' => [['show' => 1, 'class' => 'view-filter', 'label' => 'ver']],
      '#attached' => array(
        'library' => array(
          'tbo_lines/consumption-detail-data'
        ),
      ),
    );
    
    $twig = \Drupal::service('twig');
    $twig->addGlobal('informative_text_filters_lines', t("El rango de fechas no debe ser superior a $days_query días"));
    $build['#cache']['max-age'] = 0;
    //build
    $this->instance->setValue('build', $build);
    
    $aux_table_desktop = $this->instance->getValue('table_fields');
    $parameters_table_desktop = [];
    
    foreach ($aux_table_desktop as $item){
      if ($item['show'] == 1){
        $parameters_table_desktop[$item['service_field']] = $item['weight'];
      }
    }
    
    asort($parameters_table_desktop);
    $other_config = [
      'table' => array_keys($parameters_table_desktop),
      'table_mobile' => $table_mobile,
      'paginate' => $this->configuration['others']['config']['pager']['rows'],
      'format' => $this->configuration['others']['config']['format_file'],
      'environment' => $environment,
    ];
    $multiple_payment_url = '/tboapi/lines/detail/data?_format=json';
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($multiple_payment_url, $other_config);
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'consumptionDetailDataBlock');
    
    
    //log auditoria
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    
    //Create array data[]
    $data = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'event_type' => t('Servicios'),
      'description' => t('Usuario consulta detalle de consumo de datos'),
      'details' => t('Usuario @usuario consultó el detalle de consumo de datos de la línea @linea asociada al contrato @contrato',
        [
          '@usuario' => $name,
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
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }
}