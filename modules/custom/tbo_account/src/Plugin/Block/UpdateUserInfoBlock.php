<?php

namespace Drupal\tbo_account\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\tbo_account\Plugin\Config\Block\UpdateUserInfoBlockClass;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'UpdateUserInfoBlock' block.
 *
 * @Block(
 *  id = "update_user_info_block",
 *  admin_label = @Translation("Actualizar información del usuario"),
 * )
 */
class UpdateUserInfoBlock extends CardBlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config class.
   *
   * @var \Drupal\tbo_account\Plugin\Config\Block\UpdateUserInfoBlockClass
   */
  protected $configurationInstance;

  /**
   * UpdateUserInfoBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\tbo_account\Plugin\Config\Block\UpdateUserInfoBlockClass $configurationInstance
   *   Config class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UpdateUserInfoBlockClass $configurationInstance) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configurationInstance = $configurationInstance;
    $this->configurationInstance->setConfig($this, $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tbo_account.update_user_info_block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    if (method_exists($this->configurationInstance, 'defaultConfiguration')) {
      return $this->configurationInstance->defaultConfiguration();
    }
    return parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if (method_exists($this->configurationInstance, 'blockAccess')) {
      return $this->configurationInstance->blockAccess($account);
    }
    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'blockForm')) {
      return $this->configurationInstance->blockForm($form, $form_state);
    }
    return parent::blockForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'blockSubmit')) {
      return $this->configurationInstance->blockSubmit($form, $form_state);
    }
    // TODO: Change the autogenerated stub.
    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'blockValidate')) {
      return $this->configurationInstance->blockValidate($form, $form_state);
    }
    // TODO: Change the autogenerated stub.
    parent::blockValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (method_exists($this->configurationInstance, 'build')) {
      return $this->configurationInstance->build($this, $this->configuration);
    }
    return parent::build();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'buildConfigurationForm')) {
      return $this->configurationInstance->buildConfigurationForm($form, $form_state);
    }
    // TODO: Change the autogenerated stub.
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    if (method_exists($this->configurationInstance, 'getMachineNameSuggestion')) {
      return $this->configurationInstance->getMachineNameSuggestion();
    }
    // TODO: Change the autogenerated stub.
    return parent::getMachineNameSuggestion();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    if (method_exists($this->configurationInstance, 'getCacheMaxAge')) {
      return $this->configurationInstance->getCacheMaxAge();
    }
    return parent::getCacheMaxAge();
  }

  /**
   * {@inheritdoc}
   */
  public function setTransliteration(TransliterationInterface $transliteration) {
    if (method_exists($this->configurationInstance, 'setTransliteration')) {
      return $this->configurationInstance->setTransliteration($transliteration);
    }
    // TODO: Change the autogenerated stub.
    parent::setTransliteration($transliteration);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'submitConfigurationForm')) {
      return $this->configurationInstance->submitConfigurationForm($form, $form_state);
    }
    // TODO: Change the autogenerated stub.
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'validateConfigurationForm')) {
      return $this->configurationInstance->validateConfigurationForm($form, $form_state);
    }
    // TODO: Change the autogenerated stub.
    parent::validateConfigurationForm($form, $form_state);
  }

}
