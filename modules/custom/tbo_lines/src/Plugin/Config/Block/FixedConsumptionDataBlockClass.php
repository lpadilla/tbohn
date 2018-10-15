<?php

namespace Drupal\tbo_lines\Plugin\Config\Block;

use Drupal\tbo_lines\Plugin\Block\FixedConsumptionDataBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'FixedConsumptionDatalock' block.
 */
class FixedConsumptionDataBlockClass {
  protected $instance;
  protected $configuration;
  
  /**
   * @param FixedConsumptionDataBlock $instance
   * @param $config
   */
  public function setConfig(FixedConsumptionDataBlock &$instance, &$config) {
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
          'local_minutes' => [
            'title' => t('Minutos locales'),
            'label' => 'ML',
            'service_field' => 'local_minutes',
            'show' => 1,
            'weight' => 1,
            'color' => '#ffffff',
          ],
          'minutes_nal_UNE' => [
            'title' => t('Minutos nacionales UNE'),
            'label' => 'MNU',
            'service_field' => 'minutes_nal_UNE',
            'show' => 1,
            'weight' => 2,
            'color' => '#ffffff',
          ],
          'minutes_nal_others' => [
            'title' => t('Minutos nacionales otros'),
            'label' => 'MNO',
            'service_field' => 'minutes_nal_others',
            'show' => 1,
            'weight' => 3,
            'color' => '#ffffff',
          ],
          'minutes_internal' => [
            'title' => t('Minutos internacionales'),
            'label' => 'MI',
            'service_field' => 'minutes_internal',
            'show' => 1,
            'weight' => 3,
            'color' => '#ffffff',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_filter' => 1,
            'show_margin_card' => 1,
          ],
          'details_buttom' => [
            'url' => $GLOBALS['base_url'],
            'label' => t('ver detalles'),
          ],
        ],
      ],
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form['table_options'] = array(
      '#type' => 'details',
      '#title' => t('Configuraciones tabla'),
      '#open' => TRUE,
    );
    
    $form['table_options']['table_fields'] = array(
      '#type' => 'table',
      '#header' => array(
        t('Title'),
        t('Label'),
        t('Show'),
        t('Weight'),
        t('color'),
        ''
      ),
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
          '#description' => t('Cantidad máxima de caracteres 5'),
          '#maxlength' => 5,
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
      
      if (isset($entity['color'])) {
        $form['table_options']['table_fields'][$id]['color'] = [
          '#type' => 'color',
          '#default_value' => $entity['color'],
        ];
      }
    }
    
    
    $form ['others']['details_buttom'] = [
      '#type' => 'details',
      '#title' => t('bottom detalles'),
      '#open' => TRUE,
    
    ];
    $form ['others']['details_buttom']['url'] = [
      '#title' => t('URL'),
      '#type' => 'url',
      '#default_value' => $this->configuration['others']['config']['details_buttom']['url'],
    ];
    $form ['others']['details_buttom']['label'] = [
      '#type' => 'textfield',
      '#default_value' => $this->configuration['others']['config']['details_buttom']['label'],
    ];
    $form = $this->instance->cardBlockForm($form['others'], $form['table_options']);
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function build(FixedConsumptionDataBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
    
    //Set data uuid, generate filters_fields, generate table_fields
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'FixedConsumptionDataBlock');
    $this->instance->setValue('directive', 'data-ng-fixed-consumption-data');
    $this->instance->setValue('class', 'block--fixed-consumption-data');
    $this->instance->ordering('table_options');
    $table = $this->configuration['table_options']['table_fields'];
    
    //ordering table desktop
    uasort($table, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    
    $tempstore = \Drupal::service('user.private_tempstore')->get('tbo_lines');
    $tempstore->set('fixed_consumption_date', $table);
    $environment = $_SESSION['serviceDetail']['serviceType'];
    
    $build = array(
      '#theme' => 'fixed_consumption_data',
      '#uuid' => $this->instance->getValue('uuid'),
      '#directive' => $this->instance->getValue('directive'),
      '#config' => $this->configuration,
      '#class' => $this->instance->getValue('class'),
      '#table' => $table,
      '#buttom' => $this->configuration ['others']['config']['details_buttom'],
      '#margin' => $this->configuration['others']['config']['show_margin'],
      '#environment' => $environment,
      '#more' => $this->configuration['others']['config'],
      '#attached' => array(
        'library' => array(
          'tbo_lines/fixed_consumption_data'
        ),
      ),
    );
    
    $build['#cache']['max-age'] = 0;
    //build
    $this->instance->setValue('build', $build);
    
    $other_config = [
      'environment' => $environment,
    ];
    $histogram_url = '/tboapi/lines/detail-consumption/fixed?_format=json';
    //Se carga los datos necesarios para la directiva angular, se envia el rest
    $config_block = $this->instance->cardBuildConfigBlock($histogram_url, $other_config);
    //Se agrega la configuracion necesaria al objeto drupal.js
    $this->instance->cardBuildAddConfigDirective($config_block, 'FixedConsumptionDataBlock');
    
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
    if ((in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) && $_SESSION['serviceDetail']['productId'] == 13) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }
}
