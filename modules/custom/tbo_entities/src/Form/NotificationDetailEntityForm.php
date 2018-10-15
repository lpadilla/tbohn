<?php

namespace Drupal\tbo_entities\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Notification detail entity edit forms.
 *
 * @ingroup tbo_entities
 */
class NotificationDetailEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tbo_entities\Entity\NotificationDetailEntity */
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
        drupal_set_message($this->t('Created the %label Notification detail entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Notification detail entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.notification_detail_entity.canonical', ['notification_detail_entity' => $entity->id()]);
  }

}
