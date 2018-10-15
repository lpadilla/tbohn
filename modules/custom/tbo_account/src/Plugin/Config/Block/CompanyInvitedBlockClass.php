<?php

namespace Drupal\tbo_account\Plugin\Config\Block;

use Drupal\tbo_account\Plugin\Block\CompanyInvitedBlock;

/**
 * Manage config a 'CompanyInvitedBlock' block.
 */
class CompanyInvitedBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * Set Config and instance.
   *
   * @param \Drupal\tbo_account\Plugin\Block\CompanyInvitedBlock $instance
   *   CompanyInvitedBlock's instance.
   * @param array $config
   *   Configuration data.
   */
  public function setConfig(CompanyInvitedBlock &$instance, array &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function build(CompanyInvitedBlock &$instance, &$config) {
    // Set values for duplicates cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, filters_fields, table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);

    $this->instance->setValue('directive', 'company-invited-block');
    $this->instance->setValue('config_name', 'companyInviteBlock');
    $this->instance->setValue('class', 'company-invited-block');

    // Set session var.
    $this->instance->cardBuildSession();

    $url = \Drupal::config('openid_connect.settings.tigoid')->getRawData()['settings']['url_resend'];

    // Se construye la variable $build con
    // los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'account_invited',
    ];

    // Parameter additional.
    $others = [
      '#url_resend' => $url,
    ];

    $this->instance->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular,
    // se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('');

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block);

    return $this->instance->getValue('build');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
