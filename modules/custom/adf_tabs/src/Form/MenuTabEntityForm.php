<?php

namespace Drupal\adf_tabs\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Form\AjaxFormTrait;
use Drupal\Core\Block\BlockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;

/**
 * Form controller for Menu tab entity edit forms.
 *
 * @ingroup adf_tabs
 */
class MenuTabEntityForm extends ContentEntityForm {
  use AjaxFormTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * @var
   */
  protected $entity_id;

  /**
   * @var
   */
  protected $adf_tab_repository;

  /**
   * @var
   */
  protected $itemsTab;

  /**
   *
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
    $this->adf_tab_repository = \Drupal::service('adf_tabs.repository');
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Validate is new entity or call ajax.
    $entity_id = $this->entity->id();
    if (isset($entity_id)) {
      $this->entity_id = (integer) $this->entity->id();
    }
    else {
      $this->entity_id = 1000;
    }

    // Validate if add block.
    if (isset($_SESSION['adf_menu_rebuild_form']) || isset($_SESSION['edit_config_form'])) {
      if (isset($_SESSION['form_state_menu_tag']) && isset($_SESSION['form_menu_tag'])) {
        $form_state->setValues($_SESSION['form_state_menu_tag']);
        $form['configuration_data_wrapper'] = $_SESSION['form_menu_tag'];
      }
      $this->entity->setName($form_state->getValue('name')[0]['value']);
      unset($_SESSION['edit_config_form']);
      unset($_SESSION['adf_menu_rebuild_form']);
    }

    // Reload in call ajax.
    if (isset($_SESSION['form_state_menu_tag']) && isset($_SESSION['form_menu_tag'])) {
      $form_state->setValues($_SESSION['form_state_menu_tag']);
      $form['configuration_data_wrapper'] = $_SESSION['form_menu_tag'];
    }

    $form = parent::buildForm($form, $form_state);

    if (!empty($form_state->getValue('configuration_data'))) {
      $itemsTab = $form_state->getValue('configuration_data');
    }
    elseif (isset($form['configuration_data_wrapper']) && !empty($form['configuration_data_wrapper']['configuration_data']['#value'])) {
      $itemsTab = $form['configuration_data_wrapper']['configuration_data']['#value'];
    }
    elseif ($form_state->has('entity_form_initialized')) {
      $columns = [
        'items' => [
          'id',
          'name',
          'block_id',
          'block_config',
          'category',
          'to_show',
          'status',
          'order_by',
        ],
      ];
      $conditions = [
        'items.id_menu' => $entity_id,
      ];
      $orderBy = [
        'key' => 'items.order_by',
        'order' => 'ASC',
      ];
      $data = $this->adf_tab_repository->getAllItemsMenu($columns, $conditions, $orderBy);
      $itemsTab = [];
      $allItems = [];
      if (!empty($data)) {
        foreach ($data as $key => $item) {
          array_push($itemsTab, (array) $item);
          array_push($allItems, $item->id);
        }
        $_SESSION['allItems'][$entity_id] = $allItems;
      }
    }

    if (empty($itemsTab)) {
      $itemsTab = [
        0 => [],
      ];
    }
    else {
      if (is_numeric($form_state->get('to_remove'))) {
        unset($itemsTab[$form_state->get('to_remove')]);
        unset($_SESSION['all_config_menu'][$this->entity_id][$form_state->get('to_remove')]);
        $form_state->set('to_remove', NULL);
        $form_state->set('num_tabs', $form_state->get('num_tabs') - 1);
      }

      // If the number of rows has been incremented
      // add another row.
      if ($form_state->get('num_tabs') > count($itemsTab)) {
        $itemsTab[] = [];
      }
    }
    $form['configuration_data_wrapper'] = [
      '#tree' => FALSE,
      '#weight' => '-9',
      '#prefix' => '<div id="configuration-data-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['configuration_data_wrapper']['configuration_data'] = $this->getConfigurationDataForm($itemsTab);
    $form['add_item'] = [
      '#name' => 'tabs_more',
      '#type' => 'submit',
      '#value' => t('Nuevo item'),
      '#add_row' => 1,
      '#attributes' => [
        'title' => t('Click para adicionar un nuevo item'),
      ],
      '#weight' => '-5',
      '#submit' => [[$this, 'ajaxFormSubmit']],
      '#ajax' => [
        'callback' => [$this, 'ajaxFormCallback'],
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   *
   */
  private function getConfigurationDataForm($itemsTab) {
    $configuration_data = [
      '#type' => 'table',
      '#header' => [
        t('Título'),
        t('Bloque'),
        t('Manage block'),
        t('Categoría'),
        t('Mostrar'),
        t('Operación'),
        t(''),
      ],
      '#empty' => t('There are no tabs yet'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mytable-order-weight',
        ],
      ],
    ];
    foreach ($itemsTab as $key => $item) {
      $configuration_data[$key] = $this->getRow($key, $item, $this->entity_id);
    }
    return $configuration_data;
  }

  /**
   *
   */
  private function getRow($row_number, $tab = NULL, $id_entity) {
    $service = \Drupal::service('adf_tabs.menu_tab_service');
    $optionCategories = $service->optionsCategories();

    if ($tab === NULL) {
      $tab = [];
    }
    $row = [];
    $row['#weight'] = isset($tab['order_by']) ? $tab['order_by'] : 0;
    $row['#attributes']['class'][] = 'draggable';
    $row['title'] = [
      '#type' => 'textfield',
      '#size' => '30',
      '#default_value' => isset($tab['name']) ? $tab['name'] : $tab['title'],
      '#requerid' => TRUE,
    ];

    // Build a table of all blocks used by this variant.
    if (isset($_SESSION['adf_menu_config'][$id_entity][$row_number])) {
      $row['block_label_row_' . $row_number] = [
        '#plain_text' => $_SESSION['adf_menu_config'][$id_entity][$row_number]['label'],
      ];
      $_SESSION['all_config_menu'][$id_entity][$row_number] = $_SESSION['adf_menu_config'][$id_entity][$row_number]['config'];
      $tab['block_id'] = $_SESSION['all_config_menu'][$id_entity][$row_number]['label'];
      unset($_SESSION['adf_menu_config'][$id_entity][$row_number]);
    }
    elseif (isset($tab['block_id']) || isset($tab['block_config_row_' . $row_number])) {
      $block_load_config = unserialize($tab['block_config']);
      $block_id = isset($_SESSION['all_config_menu'][$id_entity][$row_number]['label']) ? $_SESSION['all_config_menu'][$id_entity][$row_number]['label'] : $block_load_config['label'];
      $block_config = isset($_SESSION['all_config_menu'][$id_entity][$row_number]) ? $_SESSION['all_config_menu'][$id_entity][$row_number] : $block_load_config;
      $row['block_label_row_' . $row_number] = [
        '#plain_text' => $block_id,
      ];
      if (!isset($_SESSION['all_config_menu'][$id_entity][$row_number])) {
        $_SESSION['all_config_menu'][$id_entity][$row_number] = $block_config;
      }
    }
    else {
      $row['block_label_row_' . $row_number] = [
        '#plain_text' => '',
      ];
    }

    // Add btn edit config block.
    if (isset($tab['block_id']) || isset($tab['block_config_row_' . $row_number])) {
      $row['block_config_row_' . $row_number] = [
        '#type' => 'submit',
        '#name' => 'block_config_row_' . $row_number,
        '#value' => $this->t('change'),
        '#ajax' => [
          'callback' => [$this, 'addContext'],
          'event' => 'click',
        ],
        '#row_number' => $row_number,
        '#id_block' => $_SESSION['all_config_menu'][$id_entity][$row_number]['id'],
        '#config_block' => 1,
        '#add_row' => 1,
      ];
    }
    else {
      $row['block_config_row_' . $row_number] = [
        '#type' => 'submit',
        '#name' => 'block_config_row_' . $row_number,
        '#value' => $this->t('Add'),
        '#ajax' => [
          'callback' => [$this, 'addContext'],
          'event' => 'click',
        ],
        '#row_number' => $row_number,
        '#id_block' => -1,
        '#add_row' => 1,
      ];
    }

    $row['category'] = [
      '#type' => 'select',
      '#options' => $optionCategories,
      '#default_value' => isset($tab['category']) ? $tab['category'] : 'empty',
    ];

    $row['show'] = [
      '#type' => 'checkbox',
      '#default_value' => isset($tab['to_show']) ? $tab['to_show'] : 1,
    ];

    $row['operations_' . $row_number] = [
      '#row_number' => $row_number,
      '#name' => 'row-' . $row_number,
      // We need this - the call to getTriggeringElement when clicking the remove button won't work without it.
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#delete_row' => 1,
      '#attributes' => [
        'class' => ['delete-tab'],
        'title' => t('Click here to delete this tab.'),
      ],
      '#submit' => [[$this, 'ajaxFormSubmit']],
      '#ajax' => [
        'callback' => [$this, 'ajaxFormCallback'],
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
        'effect' => 'fade',
      ],
    ];
    $row['weight'] = [
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#title_display' => 'invisible',
      '#default_value' => isset($tab['order_by']) ? $tab['order_by'] : 10,
      // Classify the weight element for #tabledrag.
      '#attributes' => ['class' => ['mytable-order-weight']],
    ];

    $row['id_item'] = [
      '#type' => 'hidden',
      '#value' => isset($tab['id']) ? $tab['id'] : $tab['id_item'],
    ];

    $row['id_row'] = [
      '#type' => 'hidden',
      '#value' => $row_number,
    ];

    return $row;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function ajaxFormCallback(array &$form, FormStateInterface $form_state) {
    // Instantiate an AjaxResponse Object to return.
    $ajax_response = new AjaxResponse();
    $ajax_response->addCommand(new HtmlCommand('#configuration-data-wrapper', $form['configuration_data_wrapper']['configuration_data']));

    // Delete vars session.
    unset($_SESSION['form_state_menu_tag']);
    unset($_SESSION['form_menu_tag']);

    return $ajax_response;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function ajaxFormSubmit(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] === 'tabs_more') {
      $form_state->set('num_tabs', count($form_state->getValue('configuration_data')) + 1);
      $form_state->setRebuild(TRUE);
    }
    else {
      if (is_numeric($form_state->getTriggeringElement()['#row_number'])) {
        $form_state->set('to_remove', $form_state->getTriggeringElement()['#row_number']);
        $form_state->setRebuild(TRUE);
      }
    }

    // Delete vars session.
    unset($_SESSION['form_state_menu_tag']);
    unset($_SESSION['form_menu_tag']);
  }

  /**
   * Show blocks.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function addContext(array &$form, FormStateInterface $form_state) {
    // Save temporary form and form_state values.
    $_SESSION['form_state_menu_tag'] = $form_state->getValues();
    $_SESSION['form_menu_tag'] = $form['configuration_data_wrapper'];

    $number = $form_state->getTriggeringElement()['#row_number'];
    $id_block = $form_state->getTriggeringElement()['#id_block'];
    if ($id_block != -1) {
      $_SESSION['block_edit'][$this->entity_id][$id_block] = TRUE;
    }

    $content = $this->_selectBlock($this->entity_id, $number);
    $content['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($this->t('Add new block'), $content, ['width' => '700']));
    return $response;
  }

  /**
   * Presents a list of blocks to add to the variant.
   *
   * @param $block_display
   * @param $row_id
   *
   * @return array
   */
  public function _selectBlock($block_display, $row_id) {
    // Add a section containing the available blocks to be added to the variant.
    $build = [
      '#type' => 'container',
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];

    $block_config = isset($_SESSION['all_config_menu'][$block_display][$row_id]) ? $_SESSION['all_config_menu'][$block_display][$row_id] : [];

    $available_plugins = $this->blockManager->getDefinitions();
    // Order by category, and then by admin label.
    $available_plugins = $this->blockManager->getSortedDefinitions($available_plugins);
    foreach ($available_plugins as $plugin_id => $plugin_definition) {
      // Make a section for each region.
      $category = $plugin_definition['category'];
      $category_key = 'category-' . $category;
      if (!isset($build[$category_key])) {
        $build[$category_key] = [
          '#type' => 'fieldgroup',
          '#title' => $category,
          'content' => [
            '#theme' => 'links',
          ],
        ];
      }
      $attributes = $this->getAjaxAttributes();
      if (!empty($block_config)) {
        if ($block_config['id'] == $plugin_id) {
          array_push($attributes['class'], 'block-selected-menu-tag');
          $label = $block_config['label'];
        }
      }

      // Add a link for each available block within each region.
      $build[$category_key]['content']['#links'][$plugin_id] = [
        'title' => isset($label) ? $label : $plugin_definition['admin_label'],
        'url' => Url::fromRoute('adf_tabs.block_display_add_block', [
          'block_display' => $block_display,
          'row_id' => $row_id,
          'block_id' => $plugin_id,
          'region' => '',
          'destination' => '',
        ]),
        'attributes' => $attributes,
      ];

      unset($label);
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate menu items.
    if (!$form_state->getTriggeringElement()['#delete_row']) {
      if (!$form_state->getTriggeringElement()['#add_row']) {
        $items = $form_state->getValues()['configuration_data'];
        foreach ($items as $key => $item) {
          if (empty($item['title']) || !isset($_SESSION['all_config_menu'][$this->entity_id][$item['id_row']])) {
            $form_state->setErrorByName('configuration-data', t('El titulo y bloque son requeridos en cada item de menu.'));
          }
        }
      }
    }

    // TODO: Change the autogenerated stub.
    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;
    $name_menu = $form_state->getValue('name')[0]['value'];
    $entity->setName($name_menu);
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Menu tab entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Menu tab entity.', [
          '%label' => $entity->label(),
        ]));
    }

    $date = new \DateTime();
    $timestamp = $date->getTimestamp();
    $allItems = isset($_SESSION['allItems'][$this->entity_id]) ? $_SESSION['allItems'][$this->entity_id] : [];
    if ($status) {
      $items = $form_state->getValues()['configuration_data'];
      $counter = 1;
      foreach ($items as $key => $item) {
        if (empty($item['title']) || !isset($_SESSION['all_config_menu'][$this->entity_id][$key])) {
          $form_state->setErrorByName('name', t('El titulo y bloque son requeridos en cada item de menu.'));
        }
      }
      foreach ($items as $key => $item) {
        if (!empty($item['title']) || isset($_SESSION['all_config_menu'][$this->entity_id][$key])) {
          // Validate if item exist.
          $exist_item = $this->adf_tab_repository->getItemMenu($item['id_item']);
          // Data Item.
          $data_item = [
            'name' => $item['title'],
            'block_id' => $_SESSION['all_config_menu'][$this->entity_id][$key]['id'],
            'block_config' => serialize($_SESSION['all_config_menu'][$this->entity_id][$key]),
            'category' => $item['category'],
            'to_show' => (integer) $item['show'],
            'status' => 1,
            'created' => $timestamp,
            'id_menu' => $entity->id(),
            'order_by' => $item['weight'],
          ];

          // Update item.
          if ($exist_item) {
            unset($data_item['status']);
            unset($data_item['created']);
            unset($data_item['id_menu']);
            $this->adf_tab_repository->updateItem($exist_item, $data_item);
          }
          // Create item.
          else {
            $this->adf_tab_repository->insertItem($data_item);
          }

          $counter++;

          if (!empty($allItems)) {
            $clave = array_search($item['id_item'], $allItems);
            if (is_numeric($clave)) {
              unset($allItems[$clave]);
            }
          }
        }
      }

      // Delete items.
      if (!empty($allItems)) {
        foreach ($allItems as $value) {
          $this->adf_tab_repository->deleteItem($value);
        }
      }
      unset($_SESSION['all_config_menu']);
      unset($_SESSION['form_state_menu_tag']);
      unset($_SESSION['form_menu_tag']);
    }

    $form_state->setRedirect('entity.menu_tab_entity.collection', []);
  }

}
