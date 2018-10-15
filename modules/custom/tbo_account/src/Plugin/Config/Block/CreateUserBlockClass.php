<?php

namespace Drupal\tbo_account\Plugin\Config\Block;

use Drupal\tbo_account\Plugin\Block\CreateUserBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage config a 'CreateUserBlockClass' block.
 */
class CreateUserBlockClass {
  protected $instance;
  protected $configuration;

  /**
   * @param \Drupal\tbo_account\Plugin\Block\CreateUserBlock $instance
   * @param $config
   */
  public function setConfig(CreateUserBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // Delete var session to validate create user.
    if (isset($_SESSION['render_user_create'])) {
      unset($_SESSION['render_user_create']);
    }

    return [
      'others' => [
        'config' => [
          'show_margin' => [
            'show_margin_card' => 1,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = $this->instance->cardBlockForm();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(CreateUserBlock &$instance, &$config) {
    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;

    // Set data uuid, generate filters_fields, generate table_fields.
    $this->instance->cardBuildHeader(FALSE, FALSE);
    $this->instance->setValue('class', 'wrapper-datausers');

    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account\Form\CreateUsersForm');

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'create_user',
      'library' => 'tbo_account/create-user',
    ];

    // Set title.
    $title = FALSE;

    if (isset($_SESSION['render_user_list'])) {
      $title = $_SESSION['render_user_list_title'];
      unset($_SESSION['render_user_list']);
      unset($_SESSION['render_user_list_title']);
    }
    elseif ($this->configuration['label_display'] == 'visible') {
      $title = $this->configuration['label'];
    }

    // Parameter additional.
    $others = [
      '#form' => $form,
      '#modal' => [
        'href' => 'FormUserModal',
        'label' => t('Nuevo Usuario'),
      ],
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $filters = [
      0 => 'empty',
    ];

    // Set filters empty to render form.
    $this->instance->setValue('filters', $filters);

    $this->instance->cardBuildVarBuild($parameters, $others);

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

    if ((in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles) || in_array('admin_company', $roles))) {
      // Set var session to validate create user.
      $_SESSION['render_user_create'] = TRUE;

      return AccessResult::allowed();
    }

    return AccessResult::forbidden();

  }

}
