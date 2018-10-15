<?php

namespace Drupal\tbo_account\Plugin\Config\Block;

use Drupal\tbo_account\Plugin\Block\UpdateUserInfoBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;

/**
 * Manage config a 'UpdateUserInfoBlockClass' block.
 */
class UpdateUserInfoBlockClass {

  use StringTranslationTrait;

  /**
   * Block instance.
   *
   * @var \Drupal\tbo_account\Plugin\Block\UpdateUserInfoBlock
   */
  protected $instance;

  /**
   * Config array.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Config key.
   *
   * @var string
   */
  private $configKey = 'tbo_account.update_user_info.settings';

  /**
   * Global config for messages.
   *
   * @var array
   */
  private $globalConfig;

  /**
   * The form array.
   *
   * @var array
   */
  private $form;

  /**
   * UpdateUserInfoBlockClass constructor.
   */
  public function __construct() {
    $this->globalConfig = \Drupal::config($this->configKey)
      ->getRawData();
    $this->form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account\Form\UpdateUserDataForm');
  }

  /**
   * Set instance and configuration.
   *
   * @param \Drupal\tbo_account\Plugin\Block\UpdateUserInfoBlock $instance
   *   Block instance.
   * @param array $config
   *   Config block array.
   */
  public function setConfig(UpdateUserInfoBlock &$instance, array &$config = []) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * Gets default configuration for the manage block.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  public function defaultConfiguration() {
    return [
      'override' => FALSE,
      'title_instructions' => $this->globalConfig['title_instructions'],
      'instructions' => $this->globalConfig['instructions'],
      'terms_text' => $this->globalConfig['terms_text'],
      'terms_node' => $this->globalConfig['terms_node'],
      'show_popup' => $this->globalConfig['show_popup'],
    ];
  }

  /**
   * Returns the configuration form elements specific to the block.
   *
   * @param array $form
   *   The form definition array for the block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The renderable form array representing the entire configuration form.
   */
  public function blockForm(array $form, FormStateInterface $form_state) {
    $form['override'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Modificar configuración'),
      '#default_value' => $this->configuration['override'],
      '#description' => $this->t('Reescribir la configuración global de los textos.'),
    ];
    $form['config'] = [
      '#type' => 'fieldset',
      '#open' => TRUE,
      '#states' => [
        'invisible' => [
          'input[name="settings[override]"]' => [
            'checked' => FALSE,
          ],
        ],
      ],
    ];
    $form['config']['title_instructions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título de las instrucciones'),
      '#default_value' => $this->globalConfig['title_instructions'],
      '#description' => $this->t('Título de las instrcciones para el Card'),
      '#required' => TRUE,
    ];
    $form['config']['instructions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Instrucciones'),
      '#default_value' => $this->globalConfig['instructions'],
      '#description' => $this->t('Instrucciones del Card.'),
      '#required' => TRUE,
    ];
    $form['config']['terms_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título de los términos y condiciones'),
      '#default_value' => $this->globalConfig['terms_title'],
      '#description' => $this->t('Título de los términos y condiciones en el popup'),
      '#required' => TRUE,
    ];
    $form['config']['terms_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Texto de los términos y condiciones.'),
      '#default_value' => $this->globalConfig['terms_text'],
      '#description' => $this->t('Texto de los términos y condiciones.'),
      '#required' => TRUE,
    ];
    $entity = Node::load($this->globalConfig['terms_node']);
    $form['config']['terms_node'] = [
      '#title' => $this->t('Pagina de términos y condiciones'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $entity,
      '#description' => $this->t('Puede escoger una página básica que contenga los términos y condiciones'),
      '#required' => TRUE,
    ];
    $form['config']['show_popup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Popup'),
      '#default_value' => $this->globalConfig['show_popup'],
      '#description' => $this->t('Si este campo esta activo no se abrira la url configurada y se abrira el nodo configurado.'),
    ];
    $form['config']['url'] = [
      '#type' => 'textfield',
      '#description' => t('Ejemplo /terminos-y-definiones o http://www.tigoune.com/terminos-y-definiciones.'),
      '#default_value' => $this->globalConfig['url'],
      '#size' => 20,
    ];
    $form['config']['target'] = [
      '#type' => 'select',
      '#options' => [
        '_blank' => t('Ventana nueva'),
        '_parent' => t('Ventana actual'),
      ],
      '#default_value' => $this->globalConfig['target'],
    ];

    return $form;
  }

  /**
   * Save config values.
   *
   * @param array $form
   *   The form definition array for the full block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function blockSubmit(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['override']) {
      $config_factory = \Drupal::configFactory();
      $config = $config_factory->getEditable('tbo_account.update_user_info.settings');
      $config->set("title_instructions", $values['config']['title_instructions']);
      $config->set("title_instructions", $values['config']['title_instructions']);
      $config->set("instructions", $values['config']['instructions']);
      $config->set("terms_title", $values['config']['terms_title']);
      $config->set("terms_text", $values['config']['terms_text']);
      $config->set("terms_node", $values['config']['terms_node']);
      $config->set("show_popup", $values['config']['show_popup']);
      $config->set("url", $values['config']['url']);
      $config->set("target", $values['config']['target']);
      $config->save(TRUE);
    }
    $this->configuration['override'] = $values['override'];
  }

  /**
   * Builds and returns the renderable array for the manage block.
   *
   * @param \Drupal\tbo_account\Plugin\Block\UpdateUserInfoBlock $instance
   *   Block instance.
   * @param array $config
   *   Config block array.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  public function build(UpdateUserInfoBlock &$instance, array &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;
    $build = [];
    $build['form'] = $this->form;
    return $build;
  }

  /**
   * Indicates whether the block should be shown.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }
    $roles = $account->getRoles();
    if ((in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles) || in_array('admin_company', $roles))) {
      $_SESSION['render_update_user_info'] = TRUE;
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
