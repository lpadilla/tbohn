<?php

namespace Drupal\tbo_wallets\Plugin\Config\Form;

use Behat\Mink\Exception\Exception;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tbo_wallets\Form\WalletsConfigForm;


/**
 * Manage config a 'WalletsConfigFormClass' block.
 */
class WalletsConfigFormClass {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wallets_config_form';
  }
  
  
  public function createInstance(WalletsConfigForm &$form) {
    $this->instance = &$form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface &$form_state, $config) {
    $form['#tree'] = TRUE;
    $config = $config;
    
    $wallet_aux =  $form_state->get('walletsConfig');
    $wallet_aux = isset($wallet_aux) ? $wallet_aux : $config->get('wallets')['table_fields'];
    
    $form_state->set('walletsConfig', $wallet_aux);
    
    $form['wallets'] = [
      '#type' => 'details',
      '#title' => t('CONFIGURACIÓN BILLETERAS GETBALLANCEINFO'),
      '#description' => t('Ingrese las formulas para calcular cada billetera'),
      '#open' => TRUE,
    ];
    
    //Se ordenan las billeteras segun lo establecido en la configuración
    uasort($form['wallets']['table_fields'], array(
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement'
    ));
    
    $form['wallets']['table_fields'] = array(
      '#type' => 'table',
      '#header' => array(
        t('TIPO DE CLIENTE'),
        t('ID'),
        t('ETIQUETA'),
        t('FÓRMULA'),
        t('UNIDAD'),
        t('FECHA DE EXPIRACIÓN'),
        '',
        t('Weight')
      ),
      '#empty' => t('There are no items yet. Add an item.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight',
        ),
      ),
      '#prefix' => '<div id="wallets-wrapper">',
      '#suffix' => '</div>',
    );
    
    foreach ($form_state->get('walletsConfig') as $key => $vars) {
      $form['wallets']['table_fields'][$key] = [
        '#attributes' => [
          'class' => 'draggable'
        ],
        'client_type' => [
          '#type' => 'select',
          '#options' => [
            '-' => '-',
            'default' => t('Default')
          ],
          '#default_value' => $vars['client_type'],
        ],
        'id' => [
          '#type' => 'textfield',
          '#size' => 10,
          '#default_value' => $vars['id'],
        ],
        'label' => [
          '#type' => 'textfield',
          '#size' => 22,
          '#default_value' => $vars['label'],
        ],
        'formula' => [
          '#type' => 'textfield',
          '#size' => 60,
          '#default_value' => $vars['formula'],
          '#maxlength' => 500,
        ],
        'unit' => [
          '#type' => 'textfield',
          '#size' => 6,
          '#default_value' => $vars['unit'],
        ],
        'expiration_date' => [
          '#type' => 'textfield',
          '#size' => 6,
          '#default_value' => $vars['expiration_date'],
        ],
        'actions' => [
          '#type' => 'submit',
          '#name' => 'delete-wallet-' . $key,
          '#value' => t('Eliminar'),
          '#submit' => array(array($this, 'removeRowCallback')),
          '#ajax' => [
            'callback' => array($this, 'removeWalletFunction'),
            'wrapper' => 'wallets-wrapper',
            'progress' => [
              'type' => 'throbber',
              'message' => t('Verifying entry...'),
            ],
          ],
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => $vars['weight'],
          '#attributes' => array('class' => array('fields-order-weight')),
        ],
      ];
    }
    
    $form['wallets']['add_row'] = [
      '#type' => 'submit',
      '#value' => t('Agregar billetera'),
      '#submit' => array(array($this, 'addWalletCallback')),
      '#ajax' => [
        'callback' => [$this, 'addWalletFunction'],
        'event' => 'click',
        'wrapper' => 'wallets-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying entry...'),
        ],
      ],
    ];
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $rows = $form_state->get('wallets')['table_fields'];
    
    foreach($rows as $key => $var){
      $errors = FALSE;
      
      if (empty($var['id'])) {
        $form_state->setErrorByName($form['wallets']['table_fields'][$key]['id'], t('id field is required'));
        $errors = TRUE;
      }
      if (empty($var['label'])) {
        $form_state->setErrorByName($form['wallets']['table_fields'][$key]['label'], t('id field is required'));
        $errors = TRUE;
      }
      if (empty($var['formula'])) {
        $form_state->setErrorByName($form['wallets']['table_fields'][$key]['formula'], t('id field is required'));
        $errors = TRUE;
      }
      if (empty($var['client_type'])) {
        $form_state->setErrorByName($form['wallets']['table_fields'][$key]['client_type'], t('id field is required'));
        $errors = TRUE;
      }
    };
  }
  
  public function addWalletFunction(array &$form, FormStateInterface &$form_state) {
    return $form['wallets']['table_fields'];
  }
  
  public function addWalletCallback(array &$form, FormStateInterface &$form_state) {
    $aux_wallets = $form_state->get('walletsConfig');
    $aux_wallets [] = array('clientType' => '', 'id' => '', 'label' => '', 'formula' => '', 'unit'=>'', 'expiration_date'=>'', 'actions' => '', 'weight'=>'');
    $form_state->set('walletsConfig',$aux_wallets);
    $form_state->setRebuild();
  }
  
  public function removeRowCallback(array &$form, FormStateInterface &$form_state) {
    $element = $form_state->getTriggeringElement();
    $value = intval(substr($element['#name'], 14, strlen($element['#name'])));
    $aux_wallets = $form_state->get('walletsConfig');
    unset ($aux_wallets[$value]);
    $form_state->set('walletsConfig',$aux_wallets);
    $form_state->setRebuild();
  }
  
  public function removeWalletFunction(array &$form, FormStateInterface &$form_state) {
    return $form['wallets']['table_fields'];
  }
}