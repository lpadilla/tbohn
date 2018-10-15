<?php

namespace Drupal\tbo_account_hn\Plugin\Block;

use Drupal\tbo_account\Plugin\Block\CreateUserBlock;
use Drupal\tbo_account;

/**
 * Provides a 'CreateUsersHnBlock' block.
 *
 * @Block(
 *  id = "Create_users_hn_block",
 *  admin_label = @Translation("Create users block HN"),
 * )
 */

class CreateUsersHnBlock extends CreateUserBlock{

  /**
   * {@inheritdoc}
   */

   public function build(){

		$form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account_hn\Form\CreateUsersHnForm');

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'create_user_hn',
      'library' => 'tbo_account_hn/create-user-hn',
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
        'href' => 'FormUserModalHn',
        'label' => t('Nuevo Usuario'),
      ],
      '#title' => $title,
      '#margin' => $this->configuration['others']['config']['show_margin'],
    ];

    $filters = [
      0 => 'empty',
    ];

    // Set filters empty to render form.
    $this->setValue('filters', $filters);

    $this->cardBuildVarBuild($parameters, $others);

    return $this->getValue('build');

  }


}
