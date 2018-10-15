<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\tbo_services\Plugin\Block\BlockSimBlock;

/**
 * Manage config a 'BlockSimBlockClass' block.
 */
class BlockSimBlockClass {
  protected $api;
  protected $instance;
  protected $configuration;

  /**
   * Implement of setConfig.
   *
   * @param \Drupal\tbo_services\Plugin\Block\BlockSimBlock $instance
   *   Instance.
   * @param array|object $config
   *   Config.
   */
  public function setConfig(BlockSimBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
    $this->api = \Drupal::service('tbo_api.client');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'others_display' => [
        'table_fields' => [
          'block_title' => [
            'input_type' => 'title',
            'title' => t('Título'),
            'label' => t('Bloqueo de SIM card'),
            'service_field' => 'block_title',
            'show' => TRUE,
            'weight' => 1,
          ],
          'description' => [
            'input_type' => 'label',
            'title' => t('Descripción'),
            'label' => t('Seleccione la razón por la cual se realizará el bloqueo de su SIM Card:'),
            'service_field' => 'description',
            'show' => TRUE,
            'weight' => 2,
          ],
          'rdb_lost' => [
            'title' => t('Radio Button Perdida'),
            'label' => 'Perdida',
            'service_field' => 'rdb_lost',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => TRUE,
            'weight' => 3,
          ],
          'rdb_stole' => [
            'title' => t('Radio Button Robo'),
            'label' => 'Robo',
            'service_field' => 'rdb_stole',
            'update_label' => TRUE,
            'show' => FALSE,
            'active' => FALSE,
            'weight' => 4,
          ],
        ],
      ],
      'others_buttons' => [
        'table_fields' => [
          'terms' => [
            'title' => t('Términos y condiciones - escritorio'),
            'label' => t('Términos y condiciones'),
            'description' => t('Al presionar ACEPTAR esta aceptando los'),
            'service_field' => 'link_terms',
            'url' => '',
            'node_id' => 1,
            'open_modal' => TRUE,
            'target' => '_blank',
            'show' => TRUE,
            'active' => 1,
          ],
          'termsMovile' => [
            'title' => t('Términos y condiciones - movil'),
            'label' => t('Términos y condiciones'),
            'description' => t('Al presionar ACEPTAR esta aceptando los'),
            'service_field' => 'link_terms',
            'url' => '',
            'node_id' => 1,
            'open_modal' => TRUE,
            'target' => '_blank',
            'show' => TRUE,
            'active' => 1,
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'change' => [
            'title' => t('Botón bloquear SIM Card'),
            'label' => t('Aceptar'),
            'service_field' => 'action_card_accept',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => 0,
          ],
          'cancel' => [
            'title' => t('Botón cancelar'),
            'label' => t('Cancelar'),
            'service_field' => 'action_card_cancel',
            'update_label' => TRUE,
            'show' => TRUE,
            'active' => 1,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'reason' => 'lost',
        ],
      ],
      'table_options' => [
        'table_fields' => [
          'enterprise' => [
            'title' => t('Empresa'),
            'label' => 'Empresa',
            'service_field' => 'enterprise',
            'weight' => 1,
            'show' => TRUE,
          ],
          'date_change' => [
            'title' => t('Fecha'),
            'label' => 'Fecha',
            'service_field' => 'date_change',
            'weight' => 2,
            'show' => TRUE,
          ],
          'hour' => [
            'title' => t('Hora'),
            'label' => 'Hora',
            'service_field' => 'hour',
            'weight' => 3,
            'show' => TRUE,
          ],
          'document' => [
            'title' => t('Documento'),
            'label' => 'Documento',
            'service_field' => 'document',
            'weight' => 4,
            'show' => TRUE,
          ],
          'user' => [
            'title' => t('Usuario'),
            'label' => 'Usuario',
            'service_field' => 'user',
            'weight' => 5,
            'show' => TRUE,
          ],
          'description' => [
            'title' => t('Descripción'),
            'label' => 'Descripción',
            'service_field' => 'description',
            'weight' => 6,
            'show' => TRUE,
          ],
          'line_number' => [
            'title' => t('Número de línea'),
            'label' => 'Número de línea',
            'service_field' => 'line_number',
            'weight' => 7,
            'show' => TRUE,
          ],
          'detail' => [
            'title' => t('Detalle'),
            'label' => 'Detalle',
            'service_field' => 'detail',
            'weight' => 8,
            'show' => TRUE,
          ],
        ],
      ],
      'label_display' => '0',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();

    // Rebuild table headers.
    $form['others_display']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Title'),
        t('Label'),
        t('Show'),
        t('Active'),
        t('Weight'),
      ],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ],
    ];

    $tableFields = $this->configuration['others_display']['table_fields'];

    //Se ordenan la tabla según lo establecido en la configuración
    uasort($tableFields, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));


    //Se crean todas las columnas de la tabla que mostrara la información
    foreach ($tableFields as $id => $entity) {
      $form['others_display']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['others_display']['table_fields'][$id]['#weight'] = $entity['weight'];
      $form['others_display']['table_fields'][$id]['title'] = array(
        '#plain_text' => $entity['title'],
      );

      $form['others_display']['table_fields'][$id]['label'] = array(
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      );

      $form['others_display']['table_fields'][$id]['show'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      );

      $form['others_display']['table_fields'][$id]['active'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity['active'],
      );

      // TableDrag: Weight column element.
      $form['others_display']['table_fields'][$id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight for @title', array('@title' => $entity['title'])),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => array('class' => array('fields-order-weight')),
      );
    }

    // Others_buttons.
    $buttons = $this->configuration['others_buttons']['table_fields'];

    // others_buttons: fieldset que contiene todas las columnas de la tabla.
    $form['others_buttons'] = [
      '#type' => 'details',
      '#title' => t('Configurar otros links'),
      '#open' => TRUE,
    ];
    $form['others_buttons']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Title'),
        t('Label'),
        t('Description'),
        t('Url'),
        t('Node Id'),
        t('Modal'),
        t('Ventana'),
        t('Show'),
        t('Active'),
      ],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    foreach ($buttons as $id => $entity) {
      // Some table columns containing raw markup.
      $form['others_buttons']['table_fields'][$id]['title'] = [
        '#plain_text' => $entity['title'],
      ];

      // Some table columns containing raw markup.
      $form['others_buttons']['table_fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 20,
      ];

      $form['others_buttons']['table_fields'][$id]['description'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['description'],
        '#size' => 20,
      ];

      $form['others_buttons']['table_fields'][$id]['url'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['url'],
        '#size' => 20,
      ];

      $form['others_buttons']['table_fields'][$id]['node_id'] = [
        '#type' => 'number',
        '#default_value' => $entity['node_id'],
        '#size' => 10,
        '#maxlength' => 5,
      ];

      $form['others_buttons']['table_fields'][$id]['open_modal'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['open_modal'],
      ];

      $form['others_buttons']['table_fields'][$id]['target'] = [
        '#type' => 'select',
        '#options' => [
          '_blank' => t('Nueva'),
          '_parent' => t('Actual'),
        ],
        '#default_value' => $entity['target'],
      ];

      $form['others_buttons']['table_fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['others_buttons']['table_fields'][$id]['active'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['active'],
      ];

      $form['others_buttons']['table_fields'][$id]['service_field'] = [
        '#type' => 'hidden',
        '#value' => $entity['service_field'],
      ];
    }

    $others = $this->configuration['others']['config'];

    $form['others']['config']['reason'] = [
      '#type' => 'radios',
      '#title' => t('Razón por la cual se realizará el bloqueo '),
      '#options' => [
        'lost' => t('Perdida'),
        'stole' => t('Robo'),
      ],
      '#default_value' => $others['reason'],
      '#required' => TRUE,
    ];

    // table_options: fieldset que contiene todas las columnas de a usar en noticacion.
    $form['table_options'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones de campos del Pop-up'),
      '#open' => TRUE,
    ];
    $form['table_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Title'),
        t('Label'),
        t('Show'),
        t('Weight'),
      ],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ],
    ];

    $tableOptions = $this->configuration['table_options']['table_fields'];

    //Se ordenan la tabla según lo establecido en la configuración
    uasort($tableOptions, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));


    //Se crean todas las columnas de la tabla que mostrara la información
    foreach ($tableOptions as $id => $entity) {
      $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['table_options']['table_fields'][$id]['#weight'] = $entity['weight'];
      $form['table_options']['table_fields'][$id]['title'] = array(
        '#plain_text' => $entity['title'],
      );

      $form['table_options']['table_fields'][$id]['label'] = array(
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      );

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
        '#attributes' => array('class' => array('fields-order-weight')),
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(BlockSimBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, TRUE);
    $this->instance->setValue('config_name', 'BlockSimBlock');
    $this->instance->setValue('directive', 'data-ng-block-sim');
    $this->instance->setValue('class', 'block-BlockSimBlock');

    $parameters = [
      'theme' => 'block_sim',
      'library' => 'tbo_services/block_sim',
    ];

    // Load node modal.
    $render = [];
    $terms = $config['others_buttons']['table_fields']['terms'];
    $nodeId = (int) $terms['node_id'];

    if ($terms['open_modal'] && $nodeId != 0) {
      $node = Node::load($nodeId);

      if (isset($node)) {
        $render = \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node);
      }
    }

    // Load node modal.
    $renderMovile = [];
    $termsMovile = $config['others_buttons']['table_fields']['termsMovile'];
    $nodeId = (int) $termsMovile['node_id'];

    if ($termsMovile['open_modal'] && $nodeId != 0) {
      $node = Node::load($nodeId);

      if (isset($node)) {
        $renderMovile = \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node);
      }
    }

    $othersDisplay = $this->configuration['others_display']['table_fields'];
    $tableOptions = $this->configuration['table_options']['table_fields'];

    //ordering table desktop
    uasort($othersDisplay, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));

    uasort($tableOptions, array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));

    $others = [
      '#fields' => $othersDisplay,
      '#buttons' => $config['buttons']['table_fields'],
      '#terms' => $terms,
      '#termsMovile' => $termsMovile,
      '#render' => $render,
      '#renderMovile' => $renderMovile,
      '#disabled' => FALSE,
      '#reason' => $config['others']['config']['reason'],
      '#msisdn' => $_SESSION['serviceDetail']['address'],
      '#pop_up_fields' => $tableOptions,
    ];

    // Call GetLineDetailsbyDocumentId.
    try {
      $quantity = 20;
      $params['query'] = [
        'id' => $_SESSION['company']['nit'],
        'idType' => $_SESSION['company']['docType'],
        'businessUnit' => 'B2B',
        'offset' => 1,
        'limit' => $quantity,
      ];

      while ($quantity == $params['query']['limit']) {
        $quantity = 0;
        $lines = [];

        $lines = $this->api->GetLineDetailsbyDocumentId($params);

        if (isset($lines->lineCollection)) {
          foreach ($lines->lineCollection as $line) {

            // Select current line.
            if ($line->msisdn == $_SESSION['serviceDetail']['address']) {
              $others['#fields']['description']['imsi'] = $line->imsi;

              // Disable card option if line is suspend or is line father.
              if (strpos($line->suspensionLayers->siebelLayer, 'SST') !== false || strpos($line->suspensionLayers->siebelLayer, 'SSCB') !== false || $line->plan->partNum == 'PLCON0221' || $line->plan->partNum == 'PLPOS0421') {
                $others['#disabled'] = TRUE;
              }

              $quantity = 0;
            }

            $quantity++;
          }
        }

        $params['query']['offset'] = $params['query']['offset'] + $quantity;
      }
    }
    catch (\Exception $error) {
      $token = ['@serviceName' => 'GetLineDetailsbyDocumentId'];
      drupal_set_message(t('Ha ocurrido un error.<br>Se ha presentado una falla en la comunicación con el servicio @serviceName, por favor intente más tarde.', $token), 'error');
      \Drupal::logger('Block Sim Card')->error($error->getMessage() . '<br>' . $error->getTraceAsString());
    }

    $roles = \Drupal::currentUser()->getRoles();
    if (in_array('tigo_admin', $roles)) {
      $others['#disabled'] = TRUE;
    }

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Set JavaScript data.
    $otherParams = [
      'pop_fields' => $this->configuration['table_options']['table_fields'],
    ];

    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/block-sim?_format=json', $otherParams);

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
