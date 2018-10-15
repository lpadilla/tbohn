<?php

namespace Drupal\tbo_billing\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\tigoapiCo\TigoApiProcessCo;
use Drupal\tigoapiCo\TigoApiClientCo;
use Drupal\tigoapi\TigoApiProcess;
use Drupal\tigoapi\TigoApiClient;

/**
 * Class BillingSelectOptionsForm.
 *
 * @package Drupal\tbo_billing\Form
 */
class BillingSelectOptionsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billing_select_options_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $enviroment = 'fijo', $limit = 10) {
    $label = 'Seleccione el contrato a consultar';

    $form['invoice_history_environment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Mostrar'),
      '#default_value' => $enviroment,
      '#options' => [
        'fijo' => 'Fijo',
        'movil' => 'Movil',
      ],
      '#ajax' => [
        'callback' => [$this, '_reload_select'],
      ],
    ];

    $form['billing_select_enterprise'] = [
      '#type' => 'select',
      '#title' => $this->t('Seleccione la empresa'),
      '#defaul_value' => 'seleccione',
      '#options' => $this->_get_user_company(),
      '#ajax' => [
        'callback' => [$this, '_load_contracts'],
        'wrapper' => 'load-data-contracts',
      ],
    ];

    if ($form_state->getValue('invoice_history_environment') != NULL) {
      $enviroment = $form_state->getValue('invoice_history_environment');
    }
    if ($enviroment == 'movil') {
      $label = 'Seleccione la cuenta a consultar';
    }

    $form['billing_select_contract'] = [
      '#type' => 'select',
      '#title' => $this->t($label),
      '#defaul_value' => 'seleccione',
      '#options' => $this->_get_contracts_company($form_state->getValue('billing_select_enterprise'), $enviroment, $limit),
      '#prefix' => '<div id="load-data-contracts">',
      '#suffix' => '</div>',
    ];

    $form['send'] = [
      '#type' => 'submit',
      '',
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function _load_contracts(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#load-data-contracts', $form['billing_select_contract']));
    return $response;
  }

  /**
   *
   */
  public function _reload_select(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // $response->addCommand(new ReplaceCommand('#load-list-company', $form['billing_select_enterprise']));.
    $response->addCommand(new ReplaceCommand('#load-data-contracts', $form['billing_select_contract']));
    return $response;
  }

  /**
   * Function for get contracts for company.
   *
   * @param $clientId
   * @param $environment
   *
   * @return array|bool
   */
  public function _get_contracts_company($clientId, $environment, $limit) {
    $contracts = ['seleccione' => 'Seleccione'];
    if ($clientId == 0 || $clientId == NULL) {
      return $contracts;
    }

    if ($environment == 'movil') {
      $billing_accounts_ids = new TigoApiProcess(new TigoApiClient());
      $params = [
        'type' => 'mobile',
        'endDate' => date('d/m/Y', time()),
        'countInvoiceToReturn' => 6,
        'clientType' => 'NIT',
        'clientId' => $clientId,
      ];

      $billing_accounts = $billing_accounts_ids->getAccountNumbersEnterprise($params);
      if ($billing_accounts) {
        $billing_accounts = ['0' => 'Seleccione'] + $billing_accounts;
        return $billing_accounts;
      }

      return $contracts;
    }
    $company_contracts = new TigoApiProcessCo(new TigoApiClientCo());
    $params = [
      // 'clientId' => 14214757, //temporary value for test.
      'clientId' => $clientId,
      'limit' => $limit,
    ];
    $data = $company_contracts->getContractsForEnterprise($params);

    if ($data) {
      $contracts = [];
      foreach ($data as $contract) {
        $contracts[$contract->contractId] = $contract->contractId;
      }
      $contracts = ['0' => 'Seleccione'] + $contracts;
      return $contracts;
    }

    return $contracts;
  }

  /**
   * Function for get company for user->id.
   *
   * @return array
   */
  public function _get_user_company() {
    $config = \Drupal::config("tbo_account.pagerformconfig");
    $database = \Drupal::database();
    $query = $database->select('company_entity_field_data', 'company');
    $query->distinct();
    $query->innerJoin('company_user_relations_field_data', 'compUser', 'compUser.company_id = company.id');
    $query->fields('company', ['document_number', 'name']);
    $query->condition('compUser.associated_id', \Drupal::currentUser()->id());
    $data = $query->execute()->fetchAllKeyed();
    $data = ['0' => 'Seleccione'] + $data;
    return $data;
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
