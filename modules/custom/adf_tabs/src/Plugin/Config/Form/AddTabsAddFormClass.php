<?php

namespace Drupal\adf_tabs\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\adf_tabs\Form\AdfTabsAddBlockForm;

use Drupal\Core\Form\FormState;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Manage config a 'CreateEnterpriseFormClass' block.
 */
class AddTabsAddFormClass {

  /**
   * The plugin being configured.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected $block;

  /**
   * @var
   */
  protected $instance;

  /**
   * @var
   */
  protected $entity_id;

  /**
   * @var
   */
  protected $row_id;

  /**
   * @var
   */
  protected $block_id;

  /**
   * The block manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs a new VariantPluginFormBase.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(PluginManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adf_tabs_add_block_form';
  }

  /**
   *
   */
  public function createInstance(AdfTabsAddBlockForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareBlock($plugin_id, $config = []) {
    if (!empty($config)) {
      $block = $this->blockManager->createInstance($plugin_id, $config);
    }
    else {
      $block = $this->blockManager->createInstance($plugin_id);
    }

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state, $entity_id = NULL, $block_id = NULL, $row_id = NULL) {
    $this->entity_id = $entity_id;
    $this->row_id = $row_id;
    $this->block_id = $block_id;
    if (isset($_SESSION['block_edit'][$entity_id][$block_id])) {
      $config = $_SESSION['all_config_menu'][$entity_id][$row_id];
      if (!empty($config)) {
        $settings = (new FormState())->setValues($config);
        // Update the original form values.
        $form_state->setValue('settings', $settings->getValues());
        // Call the plugin submit handler.
        $this->block = $this->prepareBlock($block_id, $config);
        // $form = $this->block->blockForm($form, $form_state);.
        unset($_SESSION['block_edit'][$entity_id][$block_id]);
      }
      else {
        $this->block = $this->prepareBlock($block_id);
      }
    }
    else {
      $this->block = $this->prepareBlock($block_id);
    }
    $form_state->set('variant_id', $block_id . '_' . 1);
    $form_state->set('block_id', $this->block->getConfiguration()['uuid']);
    $form['#tree'] = TRUE;
    $form['settings'] = $this->block->buildConfigurationForm([], $form_state);
    $form['settings']['id'] = [
      '#type' => 'value',
      '#value' => $this->block->getPluginId(),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add block'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {
    // The page might have been serialized, resulting in a new variant.
    $settings = (new FormState())->setValues($form_state->getValue('settings'));
    // Call the plugin validate handler.
    $this->block->validateConfigurationForm($form, $settings);
    // Update the original form values.
    $form_state->setValue('settings', $settings->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state) {
    $config = $form_state->getValue('settings');
    $_SESSION['adf_menu_config'][$this->entity_id][$this->row_id]['label'] = $config['label'];
    $_SESSION['adf_menu_config'][$this->entity_id][$this->row_id]['config'] = $config;
    $_SESSION['adf_menu_rebuild_form'] = TRUE;
    if ($this->entity_id == 1000) {
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . 'admin/structure/menu_tab_entity/add'));
    }
    else {
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . 'admin/structure/menu_tab_entity/' . $this->entity_id . '/edit'));
    }
  }

}
