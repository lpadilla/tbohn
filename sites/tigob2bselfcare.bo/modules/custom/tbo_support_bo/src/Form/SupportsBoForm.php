<?php

namespace Drupal\tbo_support_bo\Form;

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
 * @package Drupal\tbo_support_bo\Form
 */
class SupportsBoForm extends FormBase {
/**
   * Drupal\tbo_api_bo\TboApiBoClient definition.
   *
   * @var \Drupal\tbo_api_bo\TboApiBoClient
   * $service_message => Almacena la instancia del servicio de envio de mail.
   */

  protected $api;
  protected $service_message;

  
  /**
   * AutoCreateAccountFormClass constructor.
  */
  public function __construct() {
    $this->api = \Drupal::service('tbo_api_bo.client');  
    $this->service_message = \Drupal::service('tbo_mail_bo.send');  
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
    return 'support_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config=null) {
    $form['#attached']['library'][] = 'tbo_support_bo/support';        
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Asunto'),
      '#placeholder' => t('Asunto del correo'),
      '#size' => 30,  
      '#maxlength' => 30,
    ];

    $form['body_mail'] = [
      '#type' => 'textarea',
      '#title' => t('Mensaje'),
      '#placeholder' => t('Escribir'),
      '#maxlength' => 300,
      '#size' => 300, 
      '#attributes' => [
        'class' => array('support_agent', 'materialize-textarea'),
      ],
    ];

    $form['mail'] = array(
      '#type' => 'hidden',     
    );
    return $form;
  }
  

  /**
   * {@inheritdoc}
  */
  public function validateForm(array &$form, FormStateInterface $form_state){
    $subject = $form_state->getValue('subject');
    $body_mail = $form_state->getValue('body_mail');
 
    if($subject==""){
      $form_state->setErrorByName('subject', t('El asunto del correo está vacio'));
    }

    if($body_mail==""){
      $form_state->setErrorByName('body_mail', t('El mensaje está vacio'));
    }

    $this->submitForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $subject = $form_state->getValue('subject');
    $body_mail = $form_state->getValue('body_mail');
    $mail = $form_state->getValue('mail');

    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $user_name_from = $account_fields->full_name;
    }
    else {
      $user_name_from = \Drupal::currentUser()->getAccountName();
    }

    //$vars to send email invitation
    $tokens['mail_to_send'] = $mail;
    $tokens['subject_user'] = $subject;
    $tokens['body_user'] = $body_mail;
    $tokens['name_company'] = $_SESSION['company']['name'];
    $tokens['nit'] = $_SESSION['company']['nit'];
    $tokens['username_admin_company'] = $user_name_from;
    $templates = 'support_agent_bo';


    try {     
      //llamada al servicio de envio de correo
      $this->service_message->send_message($tokens, $templates); 
      drupal_set_message('Usuario '.$user_name_from.' hace envio de email a '.$mail, 'status'); 
                    
    }catch (\Exception $exception) {
      \Drupal::logger('agent-support')->error('Error: ' . $exception->getMessage());      
      drupal_set_message('En estos momentos no puede realizarse la solicitud. Por favor, inténte mas tarde.', 'error'); 

      // Se guarda el log de auditoria $event_type, $description, $details = NULL.
      $this->instance->cardSaveAuditLog('Support_Agent_mail', 'Ha ocurrido un problema al intentar enviar el correo', 'Ha ocurrido un problema al intentar enviar el correo.');

      $current_path = \Drupal::service('path.current')->getPath();     
      $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
    }
    
    $redirect_path = "/soporte";
    $url = url::fromUserInput($redirect_path);
    $form_state->setRedirectUrl($url);
  }
}