<?php

namespace Drupal\tbo_entities\Plugin\Config\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Use Drupal\Core\Entity\EntityInterface;.
 */
class TransacctionCategoryDeleteFormClass {

  /**
   * @param $entity
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null|string
   */
  public function getQuestion($entity) {
    return t('Are you sure you want to delete %name?', ['%name' => $entity->label()]);
  }

  /**
   * @return \Drupal\Core\Url
   */
  public function getCancelUrl() {
    return new Url('entity.transaction_category_entity.collection');
  }

  /**
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null|string
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array $form, FormStateInterface $form_state, $entity) {
    $entity->delete();

    drupal_set_message(
    t('content @type: deleted @label.',
                [
                  '@type' => $entity->bundle(),
                  '@label' => $entity->label(),
                ]
    )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
