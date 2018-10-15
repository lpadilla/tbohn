<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\tbo_lines\Plugin\Block\FixedConsumptionDailyBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'FixedConsumptionDailyBlockClass' block.
 */
class FixedConsumptionDailyBlockClass {
  protected $instance;
  protected $configuration;
  
  /**
   * @param FixedConsumptionDailyBlock $instance
   * @param $config
   */
  public function setConfig(FixedConsumptionDailyBlock &$instance, &$config) {
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
          'minutes_type' => [
            'title' => t('Tipo de minuto'),
            'label' => 'Tipo de minuto',
            'service_field' => 'minutes_type',
            'show' => 1,
            'weight' => 1,
            'class' => '',
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
          ],
          'hour' => [
            'title' => t('Hora'),
            'label' => 'Hora',
            'service_field' => 'hour',
            'show' => 1,
            'weight' => 2,
          ],
          'minutes' => [
            'title' => t('Minutos'),
            'label' => 'Minutos',
            'service_field' => 'minutes',
            'show' => 1,
            'weight' => 3,
          ],
          'origin' => [
            'title' => t('Origen'),
            'label' => 'Origen',
            'service_field' => 'origin',
            'show' => 1,
            'weight' => 3,
          ],
          'destination' => [
            'title' => t('Destino'),
            'label' => 'Destino',
            'service_field' => 'destination',
            'show' => 1,
            'weight' => 3,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'pager' => [
            'rows' => 7,
          ],
          'hour_format' => 'g:i a',
          'file_format' => 'txt',
          'download_label' => t('DESCARGAR REPORTE'),
          'informative_text' => t('Los datos presentados son una referencia de consumo. Pueden variar dependiendo de la hora de generación del reporte.'),
          'table_mobile' => [
            'table_fields' => [
              'hour' => [
                'title' => t('Hora'),
                'label' => 'Hora',
                'service_field' => 'hour',
                'show' => 1,
                'weight' => 2,
              ],
              'minutes' => [
                'title' => t('Minutos'),
                'label' => 'Minutos',
                'service_field' => 'minutes',
                'show' => 1,
                'weight' => 3,
              ],
              'origin' => [
                'title' => t('Origen'),
                'label' => 'Origen',
                'service_field' => 'origin',
                'show' => 1,
                'weight' => 3,
              ],
              'destination' => [
                'title' => t('Destino'),
                'label' => 'Destino',
                'service_field' => 'destination',
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
    //tabla custom
    $form['table_options'] = array(
      '#type' => 'details',
      '#title' => t('Configuraciones tabla'),
      '#open' => TRUE,
    );
    
    $form['table_options']['table_fields'] = array(
      '#type' => 'table',
      '#header' => array(t('Title'), t('Label'), t('Show'), t('Weight'), ''),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ),
    );
    
    $table_fields = $this->configuration['table_options']['table_fields'];
    
    //Se ordenan la tabla según lo establecido en la configuración
    uasort($table_fields, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    //Se crean todas las columnas de la tabla que mostrara la información
    foreach ($table_fields as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table_options']['table_fields']['#weight'] = $entity['weight'];
      // Some table columns containing raw markup.
      $form['table_options']['table_fields'][$id]['title'] = array(
        '#plain_text' => $entity['title'],
      );
      
      // Some table columns containing raw markup.
      if (isset($entity['label'])) {
        $form['table_options']['table_fields'][$id]['label'] = array(
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        );
      }
      else {
        $form['table_options']['table_fields'][$id]['label'] = array(
          '#type' => 'label',
          '#default_value' => '',
        );
      }
      
      $form['table_options']['table_fields'][$id]['show'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      );
      
      // TableDrag: Weight column element.
      $form['table_options']['table_fields'][$id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight for @title', array('@title' => $entity['title'])),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => array('class' => array('fields-order-weight')),
      );
    }
    
    //paginador
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
    
    //formato de hora
    $form['others']['hour_format'] = [
      '#type' => 'textfield',
      '#title' => t('Formato de hora'),
      '#description' => t('Usar formatos de hora <a href="http://php.net/manual/function.date.php">PHP</a>'),
      '#default_value' => $this->configuration['others']['config']['hour_format'],
    ];
  
    //formato de archivo
    $form['others']['file_format'] = [
      '#type' => 'select',
      '#title' => t('Formato de archivo'),
      '#options' => [
        'txt' => t('txt'),
        'csv' => t('csv'),
        'xlsx' => t('xlsx'),
      ],
      '#default_value' => $this->configuration['others']['config']['file_format'],
    ];
    
    //texto informativo
    $form['others']['informative_text'] = [
      '#type' => 'textarea',
      '#title' => t('Texto informativo'),
      '#default_value' => $this->configuration['others']['config']['informative_text'],
    ];
  
    $form['others']['download_label'] = [
      '#type' => 'textfield',
      '#title' => t('Label botón de descarga'),
      '#default_value' => $this->configuration['others']['config']['download_label'],
      '#description' => t('Cantidad recomendada de caracteres 18'),
      '#maxlength' => 18,
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
    
    
    $form = $this->instance->cardBlockForm($form['others'], $form['table_options']);
    
    //filtros custom
    /*$filters_fields = $this->configuration['filters_options']['filters_fields'];
    
    $form['filters_options'] = array(
      '#type' => 'details',
      '#title' => t('Opciones de los filtros'),
      '#open' => TRUE,
    );
    
    $form['filters_options']['filters_fields'] = array(
      '#type' => 'table',
      '#header' => array(t('Field'), t('Label'), t('Show'), t('Weight'), ''),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ),
    );
    
    foreach ($filters_fields as $id => $entity) {
      // TableDrag: Mark the table row as draggable.
      $form['filters_options']['filters_fields'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['filters_options']['filters_fields']['#weight'] = $entity['weight'];
      
      
      // Some table columns containing raw markup.
      if (isset($entity['label'])) {
        // Some table columns containing raw markup.
        $form['filters_options']['filters_fields'][$id]['title'] = array(
          '#plain_text' => $entity['title'],
        );
        
        $form['filters_options']['filters_fields'][$id]['label'] = array(
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        );
      }
      else {
        // Some table columns containing raw markup.
        $form['filters_options']['filters_fields'][$id]['label'] = array(
          '#plain_text' => $entity['title'],
        );
        
        $form['filters_options']['filters_fields'][$id]['none'] = array(
          '#type' => 'label',
          '#default_value' => '',
        );
      }
      
      $form['filters_options']['filters_fields'][$id]['show'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      );
      
      // TableDrag: Weight column element.
      $form['filters_options']['filters_fields'][$id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight for @title', array('@title' => $entity['title'])),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => array('class' => array('fields-order-weight')),
      );
      
      $form['filters_options']['filters_fields'][$id]['service_field'] = array(
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      );
    }*/
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function build(FixedConsumptionDailyBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
    
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'fixedConsumptionDailyBlock');
    $this->instance->setValue('directive', 'data-ng-fixed-consumption-daily');
    $this->instance->setValue('class', 'block-fixed-consumption-daily');
    $this->instance->ordering('table_options');
    $table = $this->configuration['table_options']['table_fields'];
    $table_mobile = $this->configuration['others']['config']['table_mobile']['table_fields'];
    
    //ordering table desktop
    uasort($table, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
  
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
    
    $classes = [
      'destacado' => 'col s12 m12',
      '1-columns' => 'col s12 m1',
      '2-columns' => 'col s12 m2',
      '3-columns' => 'col s12 m3',
      '4-columns' => 'col s12 m4',
      '5-columns' => 'col s12 m5',
      '6-columns' => 'col s12 m6',
      '7-columns' => 'col s12 m7',
      '8-columns' => 'col s12 m8',
      '9-columns' => 'col s12 m9',
      '10-columns' => 'col s12 m10',
      '11-columns' => 'col s12 m11',
      '12-columns' => 'col s12 m12',
    ];
    
    $this->configuration['filters_options']['filters_fields']['minutes_type']['class'] = $classes[$this->configuration['filters_options']['filters_fields']['minutes_type']['class']];
    
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_lines');
    $tempstore->set('fixed_consumption_daily', $table);
    $tempstore->set('fixed_consumption_daily_m', $table_mobile);
    $tempstore->set('fixed_consumption_daily_hour',$this->configuration['others']['config']['hour_format']);
    
    $environment = $_SESSION['serviceDetail']['serviceType'];
    $month_name = date('m', $_GET['month']);
    $month = $this->getMonthName($month_name);
    
    $build = array(
      '#theme' => 'fixed_consumption_daily',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#class' => $this->instance->getValue('class'),
      '#table' => $table,
      '#title' => t('Historial de consumos @month', ['@month' => $month]),
      '#filters' => $this->configuration['filters_options']['filters_fields'],
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#hour_format' => $this->configuration['others']['config']['hour_format'],
      '#environment' => $environment,
      '#informative_text' => $this->configuration['others']['config']['informative_text'],
      '#download_label' => $this->configuration['others']['config']['download_label'],
      '#more' => $this->configuration['others']['config'],
      '#productId' => $_SESSION['serviceDetail']['productId'],
      '#active_columns_mobile' => $active_columns_mobile,
      '#table_mobile' => $table_mobile,
      '#attached' => array(
        'library' => array(
          'tbo_lines/fixed_consumption_daily'
        ),
      ),
    );
    
    $build['#cache']['max-age'] = 0;
    //build
    $this->instance->setValue('build', $build);
    
    $other_config = [
      'table' => $table,
      'table_mobile' => $table_mobile,
      'paginate' => $this->configuration['others']['config']['pager']['rows'],
      'environment' => $environment,
      'file_format' => $this->configuration['others']['config']['file_format'],
      'month' => date('m-Y', $_GET['month']),
      'timestamp' => $_GET['month'],
      'parameterOpcional' => '?month='.$month,
    ];
    $histogram_url = '/tboapi/lines/detail-consumption/fixed?_format=json';
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($histogram_url, $other_config);
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'fixedConsumptionDailyBlock');
    
    //log auditoria
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $name = $service_log->getName();
    
    //Create array data[]
    $data = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'event_type' => t('Servicios'),
      'description' => t('Usuario consulta historial de consumo por día de servicio fijo'),
      'details' => t('Usuario @usuario consulta historial de consumo por día @date del contrato @contract de la dirección @address',
        [
          '@usuario' => $name,
          '@address' => isset ($_SESSION['serviceDetail']['address']) ? $_SESSION['serviceDetail']['address'] : 'No disponible',
          '@contract' => isset ($_SESSION['serviceDetail']['contractId']) ? $_SESSION['serviceDetail']['contractId'] : 'No disponible',
          '@date' => $month,
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
  
  public function getMonthName($monthNumber) {
    setlocale(LC_ALL, 'es_ES');
    $monthName = strftime('%B', mktime(0, 0, 0, $monthNumber));
    return ucfirst($monthName);
  }
}