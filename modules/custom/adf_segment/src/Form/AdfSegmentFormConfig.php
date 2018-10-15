<?php

namespace Drupal\adf_segment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TboSegmentFormConfig.
 *
 * @package Drupal\tbo_segment\Form
 */
class AdfSegmentFormConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adf_segment.adf_segment_form_config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adf_segment_form_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adf_segment.adf_segment_form_config');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Segment Api Key'),
      '#maxlength' => 120,
      '#size' => 120,
      '#default_value' => $config->get('api_key'),
    ];

    /*
    $form["#tree"] = true;
    $form['bootstrap'] = [
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Segment Settings') . '</small></h2>',
    '#default_tab' => $config->get('active_tab'),
    ];

    $group = "blocks";

    $form[$group] = [
    '#type' => 'details',
    '#title' => $this->t('Bloques'),
    '#group' => 'bootstrap',
    '#description' => $this->t("Active o desactive los bloques que desee que tengan seguimiento en Segment")
    ];

    $block_manager = \Drupal::service('plugin.manager.block');

    foreach ($block_manager->getDefinitions() as $key => $block) {
    if ($block['provider'] == 'tol' || $block['provider'] == 'home' || $block['provider'] == 'selfcare_core' || $block['provider'] == 'openid_connect' ||  $block['provider'] == 'tboid' ||  $block['provider'] == 'tol_lines') {

    $block_id = $block['id'];
    if (method_exists($block['admin_label'], '__toString')) {
    $label = isset($block['admin_label']) ? $block['admin_label']->__toString() : null;

    $form[$group][$block['id']] = [
    '#type' => 'checkbox',
    '#title' => $this->t($label),
    '#default_value' => isset($config->get($group)[$block['id']]) ? $config->get($group)[$block['id']] : 0,
    ];
    $form[$group][$block['id'] . '_label'] = [
    '#type' => 'textfield',
    '#title' => 'Descripción para el evento en Segment',
    '#states' => array(
    'visible' => array(
    ':input[name="blocks[' . $block['id'] . ']"]' => array('checked' => TRUE),
    ),
    ),
    '#default_value' => isset($config->get($group)[$block['id'] . '_label']) ? $config->get($group)[$block['id'] . '_label'] : $this->t($label)
    ];

    $form[$group][$key . '_group'] = [
    '#type' => 'textfield',
    '#title' => 'Grupo',
    '#states' => array(
    'visible' => array(
    ':input[name="blocks[' . $block['id'] . ']"]' => array('checked' => TRUE),
    ),
    ),
    '#default_value' => isset($config->get($group)[$block['id'] . '_group']) ? $config->get($group)[$block['id'] . '_group'] : $block['group'],
    ];
    }
    }
    }

    $group = "forms";

    $form[$group] = [
    '#type' => 'details',
    '#title' => $this->t('Formularios'),
    '#group' => 'bootstrap',
    '#description' => $this->t("Active o desactive los formularios que desee que tengan seguimiento en Segment")
    ];

    $forms = [
    'payment_validate_form'             => ['name' => 'Payment form'],
    'change_password_wifi_form'         => ['name' => 'Change password wifi form'],
    'home_change_name_network_form'     => ['name' => 'Change name network form'],
    'home_change_invoice_delivery_form' => ['name' => 'Change invoice delivery form'],
    'schedule_payment_form'             => ['name' => 'Schedule payment form'],
    'home_add_card_form'                => ['name' => 'Add card form'],
    'home_delete_card_form'             => ['name' => 'Delete card form'],
    'events_form'                       => ['name' => 'Events form'],
    'roaming_manage_form'               => ['name' => 'Roaming manage form'],
    'roaming_status_form'               => ['name' => 'Roaming status form'],
    'tranfer_balance_form'              => ['name' => 'Transfer balance form'],
    'my_contract_form'                  => ['name' => 'My contract form'],
    'change_invoice_delivery_form'      => ['name' => 'Change invoice delivery form'],
    'add_line_form'                     => ['name' => 'Add line form'],
    'line_form'                         => ['name' => 'Line form'],
    'manage_lines_form'                 => ['name' => 'Manage lines form'],
    'validation_document_line'          => ['name' => 'Validation document line form'],
    ];

    foreach ($forms as $key => $kform) {
    $form[$group][$key] = [
    '#type' => 'checkbox',
    '#title' => $this->t($kform['name']),
    '#default_value' => isset($config->get($group)[$key]) ? $config->get($group)[$key] : 0,
    ];

    $form[$group][$key . '_label'] = [
    '#type' => 'value',
    '#value' => $this->t($kform['name']),
    ];

    //      $form[$group][$key . '_group'] = [
    //        '#type' => '',
    //        '#options' => array(
    //          'Factura' => 'Factura',
    //          'FacturaElectronica' => 'FacturaElectronica',
    //          'Servicio' => 'Servicio',
    //          'Producto' => 'Producto',
    //          'Cuenta' => 'Cuenta',
    //          'Saldo' => 'Saldo',
    //          'Linea' => 'Linea'
    //        ),
    //        '#title' => 'Grupo',
    //        '#states' => array(
    //          'visible' => array(
    //            ':input[name="forms[' . $key . ']"]' => array('checked' => TRUE),
    //          ),
    //        ),
    //        '#default_value' => isset($config->get($group)[$block['id'] . '_group']) ? $config->get($group)[$block['id'] . '_group'] : $this->t('Factura'),
    //      ];

    }
     */
    //    $group = "buttons";
    //
    //    $form[$group] = [
    //      '#type' => 'details',
    //      '#title' => $this->t('Buttons'),
    //      '#group' => 'bootstrap'
    //    ];.
    return parent::buildForm($form, $form_state);
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
    parent::submitForm($form, $form_state);

    $this->config('adf_segment.adf_segment_form_config')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('blocks', $form_state->getValue('blocks'))
      ->set('forms', $form_state->getValue('forms'))
      ->save();
  }

}
