<?php

namespace Drupal\tbo_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'CustomPageTitleBlock' block.
 *
 * @Block(
 *  id = "custom_page_title_block",
 *  admin_label = @Translation("Título de página"),
 * )
 */
class CustomPageTitleBlock extends BlockBase {

  /**
   *
   */
  public function blockForm($form, FormStateInterface $form_state) {
    return parent::blockForm($form, $form_state);
  }

  /**
   *
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build['#theme'] = 'page_title_custom';
    $build['#title'] = [
      'label' => $this->configuration['label'],
      'label_display' => $this->configuration['label_display'],
    ];

    return $build;
  }

}
