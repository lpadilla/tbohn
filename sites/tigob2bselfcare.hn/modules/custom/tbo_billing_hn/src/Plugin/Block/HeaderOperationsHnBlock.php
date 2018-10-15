<?php


namespace Drupal\tbo_billing_hn\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'HeaderOperationsHnBlock' block.
 *
 * @Block(
 *  id = "header_operations_hn_block",
 *  admin_label = @Translation("Header operations Hn block"),
 * )
 */
class HeaderOperationsHnBlock extends BlockBase {

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
      '#theme' => 'billing_header_operations_hn',
    );

    return $build;
  }

}
