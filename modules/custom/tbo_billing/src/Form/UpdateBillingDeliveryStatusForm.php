<?php

namespace Drupal\tbo_billing\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UpdateBillingDeliveryStatusForm.
 *
 * @package Drupal\tbo_billing\Form
 */
class UpdateBillingDeliveryStatusForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_billing_delivery_status';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recibir esta factura:'),
    ];

    $form['options']['digital'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Digital'),
    ];

    $form['options']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo'),
    // TODO cambiar por info del user.
      '#default_value' => 'prueba@prueba.co',
      '#attributes' => ['readonly' => 'readonly'],
      '#states' => [
        'visible' => [
          'input[name="digital"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['options']['printed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Impresa'),
    ];

    $form['options']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dirección'),
    // TODO cambiar por info del user.
      '#default_value' => 'street 1 # 123 - 345',
      '#attributes' => ['readonly' => 'readonly'],
      '#states' => [
        'visible' => [
          'input[name="printed"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['options']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ciudad'),
    // TODO cambiar por info del user.
      '#default_value' => 'city',
      '#attributes' => ['readonly' => 'readonly'],
      '#states' => [
        'visible' => [
          'input[name="printed"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['billing_details'] = [
      '#type' => 'radios',
      '#title' => $this->t('Factura detallada'),
      '#default_value' => 'si',
      '#options' => [
        'si' => 'Si',
        'no' => 'No',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Aceptar'),
    ];

    $form['closet'] = [
      '#markup' => '<a href="#" class="modal-action modal-close waves-effect waves-red btn-flat button js-form-submit form-submit form-type-submit btn btn-default">Cancelar</a>',
      '#suffix' => '</div>',
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
