<?php


namespace Drupal\tbo_billing_bo\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'HeaderOperationsBoBlock' block.
 *
 * @Block(
 *  id = "header_operations_bo_block",
 *  admin_label = @Translation("Header operations Bo block"),
 * )
 */
class HeaderOperationsBoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
   
    $build = array(
      '#theme' => 'billing_header_operations_bo',
    );

    return $build;
  }

}
