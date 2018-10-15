<?php

namespace Drupal\tbo_services\Plugin\Block;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_services\Plugin\Config\Block\QueryPqrsBlockClass;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'QueryPqrsBlockClass' block.
 *
 * @Block(
 *  id = "query_pqrs_block",
 *  admin_label = @Translation("Search of PQRs"),
 * )
 */
class QueryPqrsBlock extends CardBlockBase implements ContainerFactoryPluginInterface {

  /**
   * Configuration instance.
   *
   * @var \Drupal\tbo_services\Plugin\Config\Block\QueryPqrsBlockClass
   */
  protected $configurationInstance;

  /**
   * Init Block.
   *
   * @param array $configuration
   *   The configuration array.
   * @param string $plugin_id
   *   The block plugin_id.
   * @param mixed $plugin_definition
   *   The block plugin_definition.
   * @param \Drupal\tbo_services\Plugin\Config\Block\QueryPqrsBlockClass $configurationInstance
   *   The configuration instance class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryPqrsBlockClass $configurationInstance) {
    // Store our dependency.
    $this->configurationInstance = $configurationInstance;

    // Call parent construct method.
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Set init config.
    $this->configurationInstance->setConfig($this, $this->configuration);
  }

  /**
   * Implements function create().
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Get dependency container.
   * @param array $configuration
   *   The configuration array.
   * @param string $plugin_id
   *   The block plugin_definition.
   * @param mixed $plugin_definition
   *   The block plugin_definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tbo_services.manage_pqrs_query_logic_block')
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
      $this->configurationInstance->blockSubmit($form, $form_state, $this->configuration);
    }
    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'blockValidate')) {
      return $this->configurationInstance->blockValidate($form, $form_state);
    }
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
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    if (method_exists($this->configurationInstance, 'getMachineNameSuggestion')) {
      return $this->configurationInstance->getMachineNameSuggestion();
    }
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
    parent::setTransliteration($transliteration);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'submitConfigurationForm')) {
      return $this->configurationInstance->submitConfigurationForm($form, $form_state);
    }
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance, 'validateConfigurationForm')) {
      return $this->configurationInstance->validateConfigurationForm($form, $form_state);
    }
    parent::validateConfigurationForm($form, $form_state);
  }

}
