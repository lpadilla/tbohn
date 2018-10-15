<?php

namespace Drupal\tbo_general\Plugin\Config\Block;

use Drupal\tbo_general\Plugin\Block\CompanySelectorBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'CompanySelectorBlock' block.
 */
class CompanySelectorBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * Set config class.
   *
   * @param \Drupal\tbo_general\Plugin\Block\CompanySelectorBlock $instance
   *   Instance.
   * @param array $config
   *   Config data.
   */
  public function setConfig(CompanySelectorBlock &$instance, array &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'filters_options' => [
        'filters_fields' => [],
      ],
      'table_options' => [
        'table_fields' => [],
      ],
      'others' => [
        'config' => [],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(CompanySelectorBlock &$instance, &$config) {
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('config_name', 'companySelectorBlock');
    $this->instance->setValue('directive', 'company-selector-block');
    $this->instance->setValue('class', 'company-selector-block');

    // Set session var.
    $instance->cardBuildSession();

    $parameters = [
      'theme' => 'company_selector',
    ];

    $companies = \Drupal::service('tbo_general.repository')->getAlladminCompanyRelations();

    $config_form = \Drupal::config("tbo_general.companyselector");

    $build = [];

    $file = file_load($config_form->get('container')['avatar'][0]);
    $data['avatar'] = [
      'avatar' => $config_form->get('container')['visibility']['show_avatar'],
      'src' => file_create_url($file->getFileUri()),
    ];

    $data['company'] = [
      'name' => $config_form->get('container')['visibility']['show_name'],
      'mail' => $config_form->get('container')['visibility']['show_mail'],
    ];

    $data['companies'] = $companies;

    $data['load_more'] = [
      'url' => $config_form->get('container')['redirect_button']['url'],
      'label' => $config_form->get('container')['redirect_button']['label'],
    ];

    $others = [
      '#data' => $data,
      '#uuid' => $this->instance->getValue('uuid'),
      '#class' => $this->instance->getValue('class'),
    ];

    $other_config = [];
    $config_block = $instance->cardBuildConfigBlock('', $other_config);
    $instance->cardBuildVarBuild($parameters, $others);
    $instance->cardBuildAddConfigDirective($config_block);

    return $instance->getValue('build');

  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $roles = $account->getRoles();
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
