<?php

namespace Drupal\tbo_entities\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TransactionCategoryEntityForm.
 */
class TransactionCategoryEntityForm extends EntityForm {

  private $config_form;

  /**
   *
   */
  public function __construct() {
    $this->config_form = \Drupal::service('tbo_entities.transacction_category_form_class');
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    return $this->config_form->form($form, $form_state, $this->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->config_form->save($form, $form_state, $this->entity);
  }

}
