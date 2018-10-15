<?php

namespace Drupal\tbo_account\Plugin\Config;

use Drupal\tbo_account\Plugin\Block\CategoryServicesListBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'CategoryServicesListBlock' block.
 */
class CategoryServicesListBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_account\Plugin\Block\CategoryServicesListBlock $instance
   * @param $config
   */
  public function setConfig(CategoryServicesListBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $categories = \Drupal::service('tbo_account.categories_services');
    $allCategories = $categories->getCategories();

    $table_fields = [];
    if (isset($allCategories) && is_array($allCategories)) {
      foreach ($allCategories as $key => $category) {
        if (isset($category['label'])) {
          $table_fields[$key] = [
            'title' => t($category['label']),
            'service_field' => $key,
            'show' => 0,
            'weight' => 1,
            'class' => '1-columns',
            // 'label' => ''.
          ];
        }
      }
    }

    return [
      'table_options' => [
        'table_fields' => $table_fields,
      ],
      'others' => [
        'config' => [
          'description' => '',
          'actions' => [
            'action_0' => [
              'show_details_link' => 0,
              'path_details_link' => '',
              'label' => 'Todos sus servicios',
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // $table_fields: variable que contiene la configuracion por defecto de las columnas de la tabla.
    $table_fields = $this->configuration['table_options']['table_fields'];

    $categories = \Drupal::service('tbo_account.categories_services');
    $current_categories = $categories->getCategories();

    if (!empty($table_fields)) {
      // table_options: fieldset que contiene todas las columnas de la tabla.
      $form['table_options'] = [
        '#type' => 'details',
        '#title' => t('Configuraciones tabla'),
        '#open' => TRUE,
      ];
      $form['table_options']['table_fields'] = [
        '#type' => 'table',
        '#header' => [t('Title'), t('Show'), t('Weight'), ''],
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
        if (isset($current_categories[$id])) {
          // TableDrag: Mark the table row as draggable.
          $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
          // TableDrag: Sort the table row according to its existing/configured weight.
          $form['table_options']['table_fields']['#weight'] = $entity['weight'];

          // Some table columns containing raw markup.
          $form['table_options']['table_fields'][$id]['title'] = [
            '#plain_text' => $current_categories[$id]['label'],
          ];

          $form['table_options']['table_fields'][$id]['show'] = [
            '#type' => 'checkbox',
            '#default_value' => $entity['show'],
          ];

          // TableDrag: Weight column element.
          $form['table_options']['table_fields'][$id]['weight'] = [
            '#type' => 'weight',
            '#title' => t('Weight for @title', ['@title' => $current_categories[$id]['label']]),
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
    }

    $others = $this->configuration['others']['config'];
    $form['others'] = [
      '#type' => 'details',
      '#title' => t('Otras configuraciones'),
      '#open' => TRUE,
    ];

    // Descripcion del bloque.
    $form['others']['config']['description'] = [
      '#type' => 'text_format',
      '#title' => t("Descripción"),
      '#default_value' => isset($others['description']['value']) ? t($others['description']['value']) : NULL,
    ];

    $form['others']['config']['actions'] = [
      '#type' => 'details',
      '#title' => t('Actions'),
      '#open' => TRUE,
      '#weight' => 1,
    ];

    $actions = $others['actions'];
    foreach ($actions as $key => $action) {
      $form['others']['config']['actions'][$key]['show_link'] = [
        '#type' => 'checkbox',
        '#title' => t('Mostrar enlace "' . $action['label'] . '"'),
        '#default_value' => isset($actions[$key]['show_link']) ? $actions[$key]['show_link'] : NULL,
      ];
      $form['others']['config']['actions'][$key]['path_link'] = [
        '#type' => 'textfield',
        '#title' => t('Url del enlace "' . $action['label'] . '"'),
        '#default_value' => isset($actions[$key]['path_link']) ? $actions[$key]['path_link'] : NULL,
        '#states' => [
          'visible' => [
            ':input[name="settings[others][config][actions][' . $key . '][show_link]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(CategoryServicesListBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('directive', 'data-ng-category-services');
    $this->instance->setValue('config_name', 'CategoryServicesBlock');
    $this->instance->setValue('class', 'wrapper-create block-category-services-message');

    // Set session var.
    $this->instance->cardBuildSession();

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'category_services',
      'library' => 'tbo_account/category-services',
    ];

    $icon_url = '';
    if (isset($this->configuration['others']['config']['icon'])) {
      $file = File::load(reset($this->configuration['others']['config']['icon']['class']));
      $style = ImageStyle::load('thumbnail');
      $icon_url = ($file) ? $style->buildUrl($file->getFileUri()) : '';
    }

    $uuid = $this->configuration['uuid'];

    // Parameter additional.
    $others = [
      '#config' => $this->configuration,
      '#fields' => $this->configuration['table_options']['table_fields'],
      '#actions' => $this->configuration['others']['config']['actions'],
      '#directive' => $this->instance->getValue('directive'),
      '#icon_url' => $icon_url,
      '#label' => $this->configuration['label'],
      '#description' => $this->configuration['others']['config']['description'],
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tboapi/category_services/list/category_services/' . $uuid . '?_format=json', ['table_fields' => $this->configuration['table_fields']]);

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, 'CategoryServicesBlock');

    // Se guarda el log de auditoria $event_type, $description, $details = NULL
    // $this->cardSaveAuditLog('Cuenta', 'Usuario accede a activacion de empresas', 'consulta activación de empresas');.
    $cid = 'config:block:' . $uuid;
    $data = $this->configuration;
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
    if (in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles) || in_array('admin_company', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }

}
