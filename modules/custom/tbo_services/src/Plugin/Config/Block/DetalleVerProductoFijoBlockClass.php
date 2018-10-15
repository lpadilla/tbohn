<?php

namespace Drupal\tbo_services\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_services\Plugin\Block\DetalleVerProductoFijoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'DetalleVerProductoFijoBlock' block.
 */
class DetalleVerProductoFijoBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Implements setConfig().
   *
   * @param \Drupal\tbo_services\Plugin\Block\DetalleVerProductoFijoBlock $instance
   *   The instance name.
   * @param array $config
   *   The instance config.
   */
  public function setConfig(DetalleVerProductoFijoBlock &$instance, array &$config) {
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
          'category' => [
            'title' => t('Categoría'),
            'label' => 'Categoría',
            'show' => 1,
            'service_field' => 'category',
            'weight' => 1,
          ],
          'status' => [
            'title' => t('Estado'),
            'label' => 'Estado',
            'show' => 1,
            'service_field' => 'status',
            'weight' => 2,
          ],
          'plan' => [
            'title' => t('Plan'),
            'label' => 'Plan',
            'show' => 1,
            'service_field' => 'plan',
            'weight' => 3,
          ],
          'address' => [
            'title' => t('Dirección'),
            'label' => 'Dirección',
            'show' => 1,
            'service_field' => 'address',
            'weight' => 4,
          ],
        ],
      ],
      'filters_options' => [
        'filters_fields' => [
          'plan' => [
            'title' => t('Plan'),
            'label' => 'Plan',
            'show' => 1,
            'service_field' => 'plan',
            'weight' => 1,
          ],
          'id' => [
            'title' => t('Id'),
            'label' => 'Id',
            'show' => 1,
            'service_field' => 'id',
            'weight' => 2,
          ],
          'serial' => [
            'title' => t('Serial'),
            'label' => 'Serial',
            'show' => 1,
            'service_field' => 'serial',
            'weight' => 3,
          ],
          'equipo' => [
            'title' => t('Equipo'),
            'label' => 'Equipo',
            'show' => 1,
            'service_field' => 'equipo',
            'weight' => 4,
          ],
          'date' => [
            'title' => t('Fecha de activación'),
            'label' => 'Fecha de activación',
            'show' => 1,
            'service_field' => 'date',
            'weight' => 5,
          ],
          'contract' => [
            'title' => t('Contrato'),
            'label' => 'Contrato',
            'show' => 1,
            'service_field' => 'contract',
            'weight' => 6,
          ],
          'address' => [
            'title' => t('Dirección'),
            'label' => 'Dirección',
            'show' => 1,
            'service_field' => 'address',
            'weight' => 7,
          ],
        ],
      ],
      'others' => [
        'config' => [
          'fixed_consumption' => [
            'show' => 1,
            'label' => 'Ver consumos',
            'new_page' => 1,
            'url' => '#',
            'label_mobile' => 'Ver consumos',
            'url_mobile' => '#',
          ],
        ],
      ],
      'buttons' => [
        'table_fields' => [
          'view_details' => [
            'title' => t('Ver detalle'),
            'label' => 'Detalle',
            'update_label' => 1,
            'service_field' => 'view_detail',
            'show' => 1,
            'active' => 1,
          ],
          'close' => [
            'title' => t('Cerrar'),
            'label' => 'Cerrar',
            'update_label' => 1,
            'service_field' => 'close',
            'show' => 1,
            'active' => 1,
          ],
        ],
      ],
      'icon' => [
        'icon_service' => [
          'title' => t('Icono del servicio'),
          'service_field' => 'icon',
          'show' => 1,
        ],
      ],
      'others_display' => [
        'table_fields' => [
          'modal_title' => [
            'title' => t('Título'),
            'label' => 'Detalles del contrato',
            'show' => 1,
            'service_field' => 'modal_title',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['others']['fixed_consumption'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones del botón ver consumos fijos'),
      '#open' => TRUE,
    ];

    $form['others']['fixed_consumption']['show'] = [
      '#type' => 'checkbox',
      '#title' => t("Ver botón de 'Ver consumos' de servicios fijos"),
      '#default_value' => $this->configuration['others']['config']['fixed_consumption']['show'],
    ];

    $form['others']['fixed_consumption']['url'] = [
      '#type' => 'url',
      '#title' => t("Url botón de 'Ver consumos' de servicios fijos"),
      '#default_value' => $this->configuration['others']['config']['fixed_consumption']['url'],
    ];

    $form['others']['fixed_consumption']['label'] = [
      '#type' => 'textfield',
      '#title' => t("Label botón de 'Ver consumos' de servicios fijos"),
      '#default_value' => $this->configuration['others']['config']['fixed_consumption']['label'],
    ];

    $form['others']['fixed_consumption']['url_mobile'] = [
      '#type' => 'textfield',
      '#title' => t("Url botón de 'Ver consumos' de servicios mobiles"),
      '#default_value' => $this->configuration['others']['config']['fixed_consumption']['url_mobile'],
    ];

    $form['others']['fixed_consumption']['label_mobile'] = [
      '#type' => 'textfield',
      '#title' => t("Label botón de 'Ver consumos' de servicios mobiles"),
      '#default_value' => $this->configuration['others']['config']['fixed_consumption']['label_mobile'],
    ];

    $form['others']['fixed_consumption']['new_page'] = [
      '#type' => 'checkbox',
      '#title' => t('Desplegar en una nueva página'),
      '#default_value' => $this->configuration['others']['config']['fixed_consumption']['new_page'],
      '#description' => t("Activo: despliegue en nueva página / Inactivo: despliegue en página actual"),
    ];
    $form = $this->instance->cardBlockForm($form['others']);

    // Modify filters configurations.
    $form['filters_options']['#title'] = t('Configuraciones de Detalles');
    // Modify table options configurations.
    $form['table_options']['#title'] = t('Configuraciones del Card');

    $form['icon_cont'] = [
      '#type' => 'details',
      '#title' => t('Configuraciones del icono'),
      '#open' => TRUE,
    ];

    $form['icon_cont']['icon_table'] = [
      '#type' => 'table',
      '#header' => [t('Field'), t('Show')],
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ],
      ],
    ];

    $form['icon_cont']['icon_table']['icon_service'] = [
      'title' => [
        '#plain_text' => $this->configuration['icon']['icon_service']['title'],
      ],
      'show' => [
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['icon']['icon_service']['show'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(DetalleVerProductoFijoBlock &$instance, &$configuration) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$configuration;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader($filters = TRUE, $columns = TRUE);
    $this->instance->setValue('config_name', 'detalleVerProductoFijoBlock');
    $this->instance->setValue('directive', 'data-ng-detalle-ver-producto-fijo');
    $this->instance->setValue('class', 'detalle-servicios block-portfolio block-detalle-ver-producto-fijo');

    // Get filters values.
    $filters = $configuration['filters_fields']['filters_fields'];

    // Set value of label configuration.
    foreach ($filters as $key => $filter) {
      if ($filter['show'] == 1) {
        $this->filters[$key]['label'] = $filter['label'];
      }
    }

    $parameters = [
      'theme' => 'detalle_ver_producto_fijo',
      'library' => 'tbo_services/detalle_ver_producto_fijo',
    ];

    $others = [
      '#buttons' => $configuration['buttons']['table_fields'],
      '#icon' => $configuration['icon'],
      '#modal_title' => $configuration['others_display']['table_fields']['modal_title'],
      '#block_title' => t('detalle de servicio'),
      '#enviroment' => (isset($_SESSION['serviceDetail']['serviceType']) ? $_SESSION['serviceDetail']['serviceType'] : 'unknown'),
      '#fixed_consumption' => $configuration['others']['config']['fixed_consumption'],
      '#productId' => $_SESSION['serviceDetail']['productId'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    $product_type = ($_SESSION['serviceDetail']['productId'] != -1) ? 'fijo' : 'movil';
    $others = [
      'product_id' => $product_type,
    ];

    $config_block = $this->instance->cardBuildConfigBlock('/tbo-services/rest/detalle-ver-producto-fijo?_format=json', $others);

    $this->instance->cardBuildAddConfigDirective($config_block);

    return $instance->getValue('build');
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
