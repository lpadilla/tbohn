<?php

namespace Drupal\tbo_permissions\Plugin\Config\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_permissions\Form\TboPermissionsSettingsForm;

/**
 * Manage config a 'TboPermissionsSettingsFormClass' block.
 */
class TboPermissionsSettingsFormClass {

  protected $instance;

  /**
   * TboPermissionsSettingsFormClass constructor.
   */
  public function __construct() {
    $this->configStore = \Drupal::config('tbo_permissions.tbopermissionssettings');
  }

  /**
   * Create form instance.
   *
   * @param \Drupal\tbo_permissions\Form\TboPermissionsSettingsForm $form
   *   Form instance.
   */
  public function createInstance(TboPermissionsSettingsForm &$form) {
    $this->instance = &$form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_permissions_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state) {
    $form = [];

    $form['#prefix'] = '<div class="formselect">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    $form['check_cards_access'] = [
      '#type' => 'radios',
      '#title' => t('Verificar los permisos de acceso a las funcionalidades según la empresa?'),
      '#description' => t('Podemos activar si se verifica o no, los accesos a las funcionalidades.'),
      '#options' => [
        'true' => t('Verificar permisos de acceso.'),
        'false' => t('No verificar permisos de acceso.'),
      ],
      '#default_value' => $this->configStore->get('check_cards_access'),
    ];

    $form['button-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-wrapper-button', 'col', 'input-field', 's12'],
      ],
    ];

    $form['button-wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Guardar'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface &$form_state) {
    // Set $vars.
    $checkCardsAccess = $form_state->getValue('check_cards_access');

    // Save the new value.
    $configStore = \Drupal::service('config.factory')
      ->getEditable('tbo_permissions.tbopermissionssettings');
    $configStore->set('check_cards_access', $checkCardsAccess)
      ->save();

    drupal_set_message(t('Se han guardado los cambios en la configuración.'), 'status');
  }

}
