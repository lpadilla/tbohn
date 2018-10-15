<?php

namespace Drupal\tbo_entities\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class TransacctionCategoryFormClass {

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  public function form(array $form, FormStateInterface $form_state, $entity) {

    $categories = \Drupal::service('tbo_general.transaction_category')->optionsSelectCategory();
    $transaction_category_entity = $entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre'),
      '#maxlength' => 30,
      '#default_value' => $transaction_category_entity->label(),
      '#description' => t("Nombre de la transacción de categoría."),
      '#required' => TRUE,
      '#id' => 'label',
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $transaction_category_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\tbo_entities\Entity\TransactionCategoryEntity::load',
      ],
      '#disabled' => !$transaction_category_entity->isNew(),
    ];
    $form['category'] = [
      '#type' => 'select',
      '#title' => t('Tipo de categoria de servicios'),
      '#options' => $categories,
      '#default_value' => $transaction_category_entity->get('category'),
      '#required' => TRUE,
    ];

    $form['card'] = [
      '#type' => 'textfield',
      '#title' => t('Card'),
      '#autocomplete_route_name' => 'tbo_general.autocomplete_card',
      '#autocomplete_route_parameters' => ['count' => 10],
      '#description' => t("Nombre del card asociado a la categoría."),
      '#default_value' => $transaction_category_entity->get('card'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function save(array $form, FormStateInterface $form_state, $entity) {

    $transaction_category_entity = $entity;
    $status = $transaction_category_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message(t('Created the %label Transaction category entity.', [
          '%label' => $transaction_category_entity->label(),
        ]));
        break;

      default:
        drupal_set_message(t('Saved the %label Transaction category entity.', [
          '%label' => $transaction_category_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($transaction_category_entity->toUrl('collection'));
  }

}
