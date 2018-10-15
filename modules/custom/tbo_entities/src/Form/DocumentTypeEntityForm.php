<?php

namespace Drupal\tbo_entities\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DocumentTypeEntityForm.
 *
 * @package Drupal\tbo_entities\Form
 */
class DocumentTypeEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $document_type_entity = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $document_type_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\tbo_entities\Entity\DocumentTypeEntity::load',
      ],
      '#disabled' => !$document_type_entity->isNew(),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del tipo de documento'),
      '#maxlength' => 30,
      '#default_value' => $document_type_entity->label(),
      '#description' => $this->t("Nombre del tipo de documento."),
      '#required' => TRUE,
    ];

    $form['abbreviated_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tipo de documento abreviado'),
      '#maxlength' => 30,
      '#default_value' => $document_type_entity->get('abb_doc_type'),
      '#description' => $this->t("Abreviación del nombre del tipo de documento."),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $document_type_entity = $this->entity;
    $status = $document_type_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tipo de documento.', [
          '%label' => $document_type_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tipo de documento.', [
          '%label' => $document_type_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($document_type_entity->toUrl('collection'));
  }

}
