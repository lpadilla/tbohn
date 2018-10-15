<?php

namespace Drupal\tbo_billing_hn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CreateAccountForm.
 *
 * @package Drupal\tbo_billing_hn\Form
 */
class ChangeSimcardHnForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_billing_change_simcard_hn';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

      // Attach the library for pop-up dialogs/modals.
      $form['#attached']['library'][] = 'core/drupal.ajax';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $form['#attached']['library'][] = 'tbo_general/tools.tbo';
      $form['#attached']['library'][] = 'tbo_billing_hn/change-sim-hn';


      $form['sim_number'] = [
        '#type' => 'textfield',
        '#title' => t('Nuevo número de Simcard'),
        '#maxlength' => 145,
        '#required' => FALSE,
        '#attributes' => [
          'class' => [
            'num-simcard',
          ],
        ],
      ];


      $form['owner_name'] = [
        '#type' => 'textfield',
        '#title' => t('Nombre del portador'),
        '#maxlength' => 145,
        '#attributes' => [
          'disabled' => 'disabled',
          'inactive' => 'inactive',
          'class' => array('disabled','enterprice'),
        ],
        '#required' => FALSE,
      ];


      return $form;
  }
  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    die(print_r($form_state));
    parent::submitForm($form, $form_state);
    die(print_r($form_state));
  }
}
