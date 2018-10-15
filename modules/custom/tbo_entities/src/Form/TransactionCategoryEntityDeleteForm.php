<?php

namespace Drupal\tbo_entities\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to delete Transaction category entity entities.
 */
class TransactionCategoryEntityDeleteForm extends EntityConfirmFormBase {

  private $config_form;

  /**
   * TransactionCategoryEntityDeleteForm constructor.
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_entities.transaction_category_delete_form_class');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->config_form->getQuestion($this->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->config_form->getCancelUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->config_form->getConfirmText();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config_form->submitForm($form, $form_state, $this->entity);
  }

}
