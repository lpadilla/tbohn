<?php

namespace Drupal\tbo_entities\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Wifi security type entity edit forms.
 *
 * @ingroup tbo_entities
 */
class WifiSecurityTypeEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tbo_entities\Entity\WifiSecurityTypeEntity */
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
        drupal_set_message($this->t('Created the %label Wifi security type entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Wifi security type entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.wifi_security_type_entity.canonical', ['wifi_security_type_entity' => $entity->id()]);
  }

}
