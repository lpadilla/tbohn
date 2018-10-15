<?php

namespace Drupal\tbo_account_bo\Plugin\Block;

//use Drupal\Core\Block\BlockBase;
use Drupal\tbo_account\Plugin\Block\CreateUserBlock;

/**
 * Provides a 'CreateUsersBoBlock' block.
 *
 * @Block(
 *  id = "Create_users_bo_block",
 *  admin_label = @Translation("Create users block BO"),
 * )
 */

class CreateUsersBoBlock extends CreateUserBlock{

  /**
   * {@inheritdoc}
   */

  public function build(){




    $form = \Drupal::formBuilder()->getForm('\Drupal\tbo_account_bo\Form\CreateUsersBoForm');

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'create_user_bo',
      'library' => 'tbo_account_hn/create-user-bo',
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
        'href' => 'FormUserModalBo',
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
