<?php

namespace Drupal\tbo_simcard\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ChangeSimcardBlock' block.
 *
 * @Block(
 *  id = "change_simcard_block",
 *  admin_label = @Translation("Change simcard block"),
 * )
 */
class ChangeSimcardBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
         'show_simcard_owner' => 0,
        ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['show_simcard_owner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show simcard owner'),
      '#description' => $this->t('Mostrar el nombre del portador'),
      '#default_value' => $this->configuration['show_simcard_owner'],
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['show_simcard_owner'] = $form_state->getValue('show_simcard_owner');
     //Create array data[]
     $data = [
      'event_type' => 'Cuenta',
      'description' => 'Usuario consulto cambio de Sim Card',
      'details' => 'Usuario '. $user_names. ' consultó Cambio de Sim Card',
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\tbo_simcard\Form\ChangeSimcardForm',$this->configuration);
    
    // Data log.
    $service_log = \Drupal::service('tbo_core.audit_log_service');
    $service_log->loadName();
    $data_log = [];

    $data_log = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'event_type' => 'SimCard',
      'description' => 'Usuario ingresa a opción de cambio de SimCard',
      'details' => 'Usuario ingresa a opción de cambio de SimCard para el MISDN: ' . $_SESSION['sendDetail']['invoice']['msisdn'],
    ];
    
    //Save audit log
    $service_log->insertGenericLog($data_log);


    // Save segment track.
    $event = 'TBO - Cambio de SimCard';
    $category = 'Cambio de Simcard';
    $environment =  $_SESSION['sendDetail']['invoice']['msisdn'];
    $label = 'Cambio de Simcard para el MSISDN - ' . $environment;
    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);
    
    return array(
      '#theme' => 'tbo_simcard_block',
      '#form' => $form,
      '#formName' => 'change-simcard-form',
      '#attached' => array(
        'library' => array(
          'tbo_simcard/tbo_simcard',
        ),
      ),
    );

		

  }

}
