<?php

namespace Drupal\adf_import_data\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Log import data entity edit forms.
 *
 * @ingroup adf_import_data
 */
class LogImportDataEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\adf_import_data\Entity\LogImportDataEntity */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Log import data entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Log import data entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.log_import_data_entity.canonical', ['log_import_data_entity' => $entity->id()]);
  }

}
