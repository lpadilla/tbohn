<?php

namespace Drupal\tbo_balance_bo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\tbo_api_bo\TboApiBoClient;
use Behat\Mink\Exception\Exception;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;

/**
 * Class CreateAccountForm.
 *
 * @package Drupal\tbo_balance_bo\Form
 */
class BalancesBoForm extends FormBase {
/**
   * Drupal\tbo_api_bo\TboApiBoClient definition.
   *
   * @var \Drupal\tbo_api_bo\TboApiBoClient
   */
  protected $api;
  protected $amounts_g;

  
  /**
   * AutoCreateAccountFormClass constructor.
  */
  public function __construct() {
    $this->api = \Drupal::service('tbo_api_bo.client');    
    $this->amounts_g = array();
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tbo_api_bo.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'balances_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config=null) {
    $form['#attached']['library'][] = 'tbo_balance_bo/balances';

    $a = $config['others']['config']['amount'];
    $amounts = explode(",",$a);

    $this->amounts_g = $amounts;

    $i=0;
    foreach ($amounts as $key) {
      $option_s[$i]=$this->t($key." Bs");
      $i++;
    }
    
    $form['amount'] = [
      '#type' => 'select',
      '#title' => $this->t('Monto a transferir'),
      '#default_value' => t('Monto'),
      '#options' => $option_s,      
      '#size' => 50,  
    ];

    $form['number'] = [
      '#type' => 'textfield',
      '#title' => t('Numero de destinatario'),
      '#placeholder' => t('Número'),
      '#maxlength' => 20,
      '#size' => 20, 
      '#prefix' => '<div class="left card-icon prefix icon-service transfer"><div class="icon-mobilephone-cyan"><span class="path1"></span><span class="path2"></span></div></div> ',
    ];

    $form['sender'] = array(
      '#type' => 'hidden',     
    );

    $form['amount_select'] = array(
      '#type' => 'hidden',     
    );

    if($config['others']['config']['commission']==0){
      $valor_commission = 0;
    }else{
      /*aqui se deberia hacer el llamado al servicio para saber
      cual es el monto de la comision de la transferencia
      aun ese servicio no esta listo*/
      $valor_commission = 5;
    }

    $form['commission'] = array(
      '#type' => 'hidden',     
      '#default_value' => $valor_commission,
    ); 
    return $form;
  }
  

  /**
   * {@inheritdoc}
  */
  public function validateForm(array &$form, FormStateInterface $form_state){
    $number = $form_state->getValue('number');
    $senders = $form_state->getValue('sender');
    // validaacion de los campos del form
    if($number==""){
      $form_state->setErrorByName('number', t('El monto a transferir está vacio'));
    }
    if(!is_numeric($number)) {
      $form_state->setErrorByName('number', t('El monto a transferir debe estar compuesto solo por numeros'));
    }

    $sender = trim($senders);
    if($sender==""){
      $form_state->setErrorByName('sender', t('El numero desde el que se va a transferir está vacio'));
    }
    if(!is_numeric($sender)) {
      $form_state->setErrorByName('sender', t('El numero desde el que se va a transferir debe estar compuesto solo por numeros'));
    }

    $this->submitForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $a = $form_state->getValue('amount_select');
    $number = $form_state->getValue('number');
    $sender = $form_state->getValue('sender');
    $commission = $form_state->getValue('commission');

    $config = \Drupal::config("tbo_billing_bo.bill_payment_settings");
    $group = "visualizacion";

    $total = $a + $commission;
    // se le quita el codigo del pais al numero para que el servicio de transferencia funcione
    $pos = strpos($sender, '591');
    if($pos === false){
        $sender_sc = $sender;
    }else
    {
      if($pos == 0){
        $sender_sc = substr($sender, 3);        
      }else{
        $sender_sc = $sender;
      }
    }

    $body = [
      'debtMsisdn' => $sender_sc,
      'amount' => $total,
    ];

    $jsonBody = json_encode($body);
    $params = [
      'tokens' => [
        'number' => $number
      ],
      'query' => [        
      ],
     
      'body' => $jsonBody,
    ];

    try{
      //llamada al servicio de transferencia
      $ws_response = $this->api->PostTransferBalance($params);           
      if (!empty($ws_response)) {      
        //respuesta positiva
        if ((($ws_response->tigoResponse->status == 200) & ($ws_response->tigoResponse->response->message == 'Transacción Exitosa' )) || $ws_response->state== 'OK' ){
          drupal_set_message(t('Transferencia de Saldo'), 'status');
          drupal_set_message(t('La Transferencia ha sido aplicada correctamente'), 'status'); 
          if ($this->configuration['others']['config']['segment']) {     
           # Save segment track.
            $event = 'TBO - Transferencia de Saldo';
            $category = 'Saldos';    
            $label = 'Transferencia de Saldo al numero- ' . $number;
            \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label);          
          }
        } else {//respuesta negativa del servicio
          drupal_set_message('Transferencia de Saldo', 'error');
          drupal_set_message('Ha ocurrido un problema al intentar aplicar la Transferencia. Por favor, inténtelo nuevamente.', 'error');       
        }      
      }

      $current_path = \Drupal::service('path.current')->getPath();

      //Create Audit log
      $data_log = [
        'companyName' => '',
        'companyDocument' => '',
        'event_type' => 'Servicios',
        'description' => 'Usuario transfirió '.$amount.' desde el numero '.$sender_sc.' al numero '.$number.' la comision por la Transferencia fue de '.$comision,
      ];
    }catch (\Exception $exception) {// el servicio esta dando error
      \Drupal::logger('balanceTransfer')->error('Error: ' . $exception->getMessage());
      
      drupal_set_message('Transferencia de Saldo', 'error');
      drupal_set_message('Ha ocurrido un problema al intentar aplicar la Transferencia. Por favor, inténtelo nuevamente.', 'error'); 
      $current_path = \Drupal::service('path.current')->getPath();     
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
    }
    //redirect
    $redirect_path = "/tranferencia-saldo";
    $url = url::fromUserInput($redirect_path);
    $form_state->setRedirectUrl($url);
  }
}