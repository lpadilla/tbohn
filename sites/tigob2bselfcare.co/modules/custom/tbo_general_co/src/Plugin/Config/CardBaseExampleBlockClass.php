<?php

namespace Drupal\tbo_general_co\Plugin\Config;

use Drupal\tbo_general\Plugin\Block\CardBaseExampleBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\tbo_general\Plugin\Config\CardBaseExampleBlockClass as CardBaseExampleBlockClassBase;

/**
 * Manage config a 'CurrentInvoiceBlock' block.
 */
class CardBaseExampleBlockClass extends CardBaseExampleBlockClassBase {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_general\Plugin\Block\CardBaseExampleBlock $instance
   * @param $config
   */
  public function setConfig(CardBaseExampleBlock &$instance, &$config) {
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
        'table_fields' => [
          'id' => [
            'title' => t('Id'),
            'label' => t('Id'),
            'service_field' => 'id',
            'show' => 1,
            'weight' => 1,
            'class' => '4-columns',
          ],
          'company' => [
            'title' => t('Nombre de la Compañia'),
            'label' => t('Compañia'),
            'service_field' => 'company',
            'show' => 1,
            'weight' => 2,
            'class' => '4-columns',
          ],
          'document_type'  => [
            'title' => t('Tipo de documento'),
            'label' => t('Tipo de documento'),
            'service_field' => 'document_type',
            'show' => 1,
            'weight' => 3,
            'class' => '4-columns',
          ],
          'city'  => [
            'title' => t('Ciudad'),
            'label' => t('Ciudad'),
            'service_field' => 'city',
            'show' => 1,
            'weight' => 4,
            'class' => '4-columns',
          ],
        ],
      ],
      'others' => [
        'config' => [
          'paginate' => [
            'number_pages' => 10,
            'number_rows_pages' => 10,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, filters_fields, table_fields.
    $this->instance->cardBuildHeader(FALSE);
    $this->instance->setValue('config_name', 'CardBaseExampleBlock');
    $this->instance->setValue('directive', 'data-ng-card-base-example');

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'card_base_example',
      'library' =>
      'tbo_general/card-base-example',
    ];

    $this->instance->cardBuildVarBuild($parameters);

    // Add config drupal object js.
    $other_config = [
      'environment' => $_SESSION['environment'],
    ];

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->instance->cardBuildConfigBlock('/tbo_general/rest/base-example-card?_format=json', $other_config);

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->instance->cardBuildAddConfigDirective($config_block, $this->instance->getValue('config_name'));

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

    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
