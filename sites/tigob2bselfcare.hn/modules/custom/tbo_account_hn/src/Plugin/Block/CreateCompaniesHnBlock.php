<?php

namespace Drupal\tbo_account_hn\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\tbo_account_hn\Plugin\Config\Block\CreateCompaniesHnBlockClass;

/**
 * Provides a 'CreateCompaniesHnBlock' block.
 *
 * @Block(
 *  id = "companies_list_and_create_hn_block",
 *  admin_label = @Translation("Empresas HN"),
 * )
 */
class CreateCompaniesHnBlock extends CardBlockBase implements ContainerFactoryPluginInterface{

  /**
   * @var $configurationInstance CreateCompaniesHnBlockClass
   */
  protected $configurationInstance;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param CreateCompaniesHnBlockClass $configurationInstance
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CreateCompaniesHnBlockClass $configurationInstance) {
    // Store our dependency.
    $this->configurationInstance = $configurationInstance;

    // Call parent construct method.
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    //Set init config
    $this->configurationInstance->setConfig($this, $this->configuration);
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tbo_account_hn.create_companies_hn_block')
    );
  }

  
  public function defaultConfiguration() {
    if (method_exists($this->configurationInstance,'defaultConfiguration')) {
      return $this->configurationInstance->defaultConfiguration();
    }
    return parent::defaultConfiguration();
  }


  public function blockAccess(AccountInterface $account) {
    if (method_exists($this->configurationInstance,'blockAccess')) {
      return $this->configurationInstance->blockAccess($account);
    }
    return parent::blockAccess($account);
  }

  
  public function blockForm($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance,'blockForm')) {
      return $this->configurationInstance->blockForm($form, $form_state);
    }
    return parent::blockForm($form, $form_state);
  }

  
  public function blockSubmit($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance,'blockSubmit')) {
      return $this->configurationInstance->blockSubmit($form, $form_state);
    }
    parent::blockSubmit($form, $form_state); 
  }

  
  public function blockValidate($form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance,'blockValidate')) {
      return $this->configurationInstance->blockValidate($form, $form_state);
    }
    parent::blockValidate($form, $form_state); 
  }

  
  public function build() {
    if (method_exists($this->configurationInstance,'build')) {
      return $this->configurationInstance->build();
    }
    return parent::build();
  }

  
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance,'buildConfigurationForm')) {
      return $this->configurationInstance->buildConfigurationForm($form, $form_state);
    }
    return parent::buildConfigurationForm($form, $form_state); 
  }

  
  public function getMachineNameSuggestion() {
    if (method_exists($this->configurationInstance,'getMachineNameSuggestion')) {
      return $this->configurationInstance->getMachineNameSuggestion();
    }
    return parent::getMachineNameSuggestion(); 
  }

  
  public function getCacheMaxAge() {
    if (method_exists($this->configurationInstance,'getCacheMaxAge')) {
      return $this->configurationInstance->getCacheMaxAge();
    }
    return parent::getCacheMaxAge();
  }

  
  public function setTransliteration(TransliterationInterface $transliteration) {
    if (method_exists($this->configurationInstance,'setTransliteration')) {
      return $this->configurationInstance->setTransliteration($transliteration);
    }
    parent::setTransliteration($transliteration); 
  }

  
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance,'submitConfigurationForm')) {
      return $this->configurationInstance->submitConfigurationForm($form, $form_state);
    }
    parent::submitConfigurationForm($form, $form_state); 
  }

  
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (method_exists($this->configurationInstance,'validateConfigurationForm')) {
      return $this->configurationInstance->validateConfigurationForm($form, $form_state);
    }
    parent::validateConfigurationForm($form, $form_state); 
  }

}
