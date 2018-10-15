<?php

namespace Drupal\tbo_emulate_bo\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'LoadFormAddUser' block.
 *
 * @Block(
 *  id = "load_form_add_user",
 *  admin_label = @Translation("Load form add user"),
 * )
 */
class LoadFormAddUser extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['add_line_form'] = \Drupal::formBuilder()->getForm('\Drupal\tbo_emulate_bo\Form\ExampleForm');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
