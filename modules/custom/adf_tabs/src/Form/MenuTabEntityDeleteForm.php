<?php

namespace Drupal\adf_tabs\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting Menu tab entity entities.
 *
 * @ingroup adf_tabs
 */
class MenuTabEntityDeleteForm extends ContentEntityDeleteForm {

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get id_entity.
    $id = $this->getEntity()->id();

    // Delete menu.
    parent::submitForm($form, $form_state);

    // Delete all items menu.
    \Drupal::service('adf_tabs.repository')->deleteAllItemsByMenu($id);
  }

}
