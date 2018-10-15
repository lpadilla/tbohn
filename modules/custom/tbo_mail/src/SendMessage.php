<?php

namespace Drupal\tbo_mail;

use Drupal\user\Entity\User;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Class SendMessage.
 *
 * @package Drupal\tbo_mail
 */
class SendMessage implements SendMessageInterface {

  private $params = [];

  private $api;

  /**
   * Constructor.
   *
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   *   Api is injected to access the services.
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * Configure and send the email.
   *
   * @param array $tokens
   *   Message tokens.
   * @param string $template
   *   Template name.
   */
  public function send_message(array $tokens, $template) {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    // Load fields account.
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }

    $email = $account->getEmail();
    $params = [];
    $settings = \Drupal::config('tbo_mail.settings');
    $params['mail_to_send'] = $tokens['mail_to_send'];
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $params['langcode'] = $langcode;
    $logoid = $settings->get('images')['logo'];

    if ($logoid > 0 && !is_array($logoid)) {
      $file = file_load($logoid[0]);
      $params['tokens']['logo'] = $file->url();
    }
    else {
      $params['tokens']['logo'] = $GLOBALS['base_url'] . '/' . drupal_get_path('module', 'tbo_mail') . '/images/logo_email.png';
    }

    $rol = '';
    if (isset($tokens['role'])) {
      // Get name rol.
      $rol = \Drupal::service('tbo_core.repository')
        ->getRoleName($tokens['role']);
    }

    switch ($template) {
      case 'new_user':
        $params['subject'] = $settings->get('new_user')['subject'];
        $params['body'] = $settings->get('new_user')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['role'] = $rol;
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'new_enterprise':
        $params['subject'] = $settings->get('new_enterprise')['subject'];
        $params['body'] = $settings->get('new_enterprise')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['admin_enterprise'] = $tokens['admin_enterprise'];
        $params['tokens']['admin_mail'] = $tokens['admin_mail'];
        $params['tokens']['admin_phone'] = $tokens['admin_phone'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        // $this->send_sms('new_enterprise', $parameters);.
        break;

      case 'assing_enterprise':
        $params['subject'] = $settings->get('assing_enterprise')['subject'];
        $params['body'] = $settings->get('assing_enterprise')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'config_bill':
        $params['subject'] = $settings->get('config_bill')['subject'];
        $params['body'] = $settings->get('config_bill')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['bill_status'] = $tokens['bill_status'];
        $params['tokens']['bill_number'] = $tokens['bill_number'];
        $params['tokens']['bill_old'] = $tokens['bill_old'];
        $params['tokens']['bill_new'] = $tokens['bill_new'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'register_complain':
        $params['subject'] = $settings->get('register_complain')['subject'];
        $params['body'] = $settings->get('register_complain')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['complain_type'] = $tokens['complain_type'];
        $params['tokens']['complain_description'] = $tokens['complain_description'];
        $params['tokens']['attachments'] = $tokens['attachments'];
        $params['tokens']['cun'] = $tokens['cun'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'autocreate_account':
        $params['subject'] = $settings->get('autocreate_account')['subject'];
        $params['body'] = $settings->get('autocreate_account')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['creator'] = $tokens['creator'];
        $params['tokens']['creator_mail'] = $tokens['creator_mail'];
        $params['tokens']['invitation_code'] = $tokens['invitation_code'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'schedule_payment':
        $params['subject'] = $settings->get('schedule_payment')['subject'];
        $params['body'] = $settings->get('schedule_payment')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['date'] = $tokens['date'];
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['name'] = $tokens['name'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['card_number'] = $tokens['card_number'];
        $params['tokens']['card_brand'] = $tokens['card_brand'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'remove_schedule_payment':
        $params['subject'] = $settings->get('remove_schedule_payment')['subject'];
        $params['body'] = $settings->get('remove_schedule_payment')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['date'] = $tokens['date'];
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['name'] = $tokens['name'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['card_number'] = $tokens['card_number'];
        $params['tokens']['card_brand'] = $tokens['card_brand'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'add_card_token':
        $params['subject'] = $settings->get('add_card_token')['subject'];
        $params['body'] = $settings->get('add_card_token')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['date'] = $tokens['date'];
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['name'] = $tokens['name'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['card_number'] = $tokens['card_number'];
        $params['tokens']['card_brand'] = $tokens['card_brand'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'remove_card_token':
        $params['subject'] = $settings->get('remove_card_token')['subject'];
        $params['body'] = $settings->get('remove_card_token')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['date'] = $tokens['date'];
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['name'] = $tokens['name'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['card_number'] = $tokens['card_number'];
        $params['tokens']['card_brand'] = $tokens['card_brand'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'assing_enterprise_super_admin':
        $params['subject'] = $settings->get('assing_enterprise_super_admin')['subject'];
        $params['body'] = $settings->get('assing_enterprise_super_admin')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['date'] = $tokens['date'];
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['name'] = $tokens['name'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['tokens']['admin_enterprise'] = $tokens['admin_enterprise'];
        $params['tokens']['admin_mail'] = $tokens['admin_mail'];
        $params['tokens']['admin_phone'] = $tokens['admin_phone'];
        $params['tokens']['creator_docType'] = $tokens['creator_docType'];
        $params['tokens']['creator_docNumber'] = $tokens['creator_docNumber'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'assing_enterprise_old':
        $params['subject'] = $settings->get('assing_enterprise_old')['subject'];
        $params['body'] = $settings->get('assing_enterprise_old')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['date'] = $tokens['date'];
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['name'] = $tokens['name'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['document'];
        $params['tokens']['admin_enterprise'] = $tokens['admin_enterprise'];
        $params['tokens']['admin_mail'] = $tokens['admin_mail'];
        $params['tokens']['admin_phone'] = $tokens['admin_phone'];
        $params['tokens']['creator_docType'] = $tokens['creator_docType'];
        $params['tokens']['creator_docNumber'] = $tokens['creator_docNumber'];
        $params['tokens']['link'] = $tokens['link'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'change_wifi_pass':
        $params['subject'] = $settings->get('change_wifi_pass')['subject'];
        $params['body'] = $settings->get('change_wifi_pass')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['user_change'] = $tokens['user_change'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['address'] = $tokens['address'];
        $params['tokens']['service_id'] = $tokens['service_id'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'change_wifi_net_name':
        $params['subject'] = $settings->get('change_wifi_net_name')['subject'];
        $params['body'] = $settings->get('change_wifi_net_name')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['user_change'] = $tokens['user_change'];
        $params['tokens']['wifi_new_name'] = $tokens['wifi_new_name'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['address'] = $tokens['address'];
        $params['tokens']['service_id'] = $tokens['service_id'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'change_wifi_dmz':
        $params['subject'] = $settings->get('change_wifi_dmz')['subject'];
        $params['body'] = $settings->get('change_wifi_dmz')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['user_change'] = $tokens['user_change'];
        $params['tokens']['wifi_dmz'] = $tokens['wifi_dmz'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['address'] = $tokens['address'];
        $params['tokens']['service_id'] = $tokens['service_id'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'multiple_invoices_payment':
        $params['subject'] = $settings->get('multiple_invoices_payment')['subject'];
        $params['body'] = $settings->get('multiple_invoices_payment')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['username'] = $tokens['user'];
        $params['tokens']['admin'] = $name;
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['tokens']['status'] = $tokens['status'];
        $params['tokens']['attachments'] = $tokens['attachments'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'change_sim_card':
        $params['subject'] = $settings->get('change_sim_card')['subject'];
        $params['body'] = $settings->get('change_sim_card')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['service_id'] = $tokens['service_id'];
        $params['tokens']['line_number'] = $tokens['line_number'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['enterprise_name'] = $tokens['enterprise_name'];
        $params['tokens']['enterprise_type'] = $tokens['enterprise_type'];
        $params['tokens']['enterprise_number'] = $tokens['enterprise_number'];
        $params['tokens']['old_sim'] = $tokens['old_sim'];
        $params['tokens']['new_sim'] = $tokens['new_sim'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        // $params['link'] = $tokens['link'];.
        $this->params = $params;
        $this->send_mail();
        break;

      case 'change_wifi_channel':
        $params['subject'] = $settings->get('change_wifi_channel')['subject'];
        $params['body'] = $settings->get('change_wifi_channel')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['user_change'] = $tokens['user_change'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['address'] = $tokens['address'];
        $params['tokens']['service_id'] = $tokens['service_id'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['tokens']['new_channel'] = $tokens['new_channel'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'change_security_type':
        $params['subject'] = $settings->get('change_security_type')['subject'];
        $params['body'] = $settings->get('change_security_type')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['service_id'] = $tokens['service_id'];
        $params['tokens']['line_number'] = $tokens['line_number'];
        $params['tokens']['address'] = $tokens['address'];
        $params['tokens']['contract_id'] = $tokens['contract_id'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['enterprise_doc'] = $tokens['enterprise_doc'];
        $params['tokens']['new_security_type'] = $tokens['new_security_type'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'daily_cards_access_modifications_excel_report':
        $params['subject'] = $settings->get('daily_cards_access_modifications_excel_report')['subject'];
        $params['body'] = $settings->get('daily_cards_access_modifications_excel_report')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['attachments'] = $tokens['attachments'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'block_sim_card':
        $params['subject'] = $settings->get('block_sim_card')['subject'];
        $params['body'] = $settings->get('block_sim_card')['body']['value'];
        $params['tokens']['username'] = $tokens['userName'];
        $params['tokens']['user_change'] = $tokens['userChange'];
        $params['tokens']['line_number'] = $tokens['msisdn'];
        $params['tokens']['imsi'] = $tokens['imsi'];
        $params['tokens']['contract_id'] = $tokens['contractId'];
        $params['tokens']['enterprise_name'] = $tokens['company'];
        $params['tokens']['enterprise_type'] = $tokens['companyDocumentType'];
        $params['tokens']['enterprise_number'] = $tokens['companyDocument'];
        $params['tokens']['block_sim_reason'] = $tokens['reason'];
        $params['mail_to_send'] = $tokens['mail'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'download_contract':
        $params['subject'] = $settings->get('download_contract')['subject'];
        $params['body'] = $settings->get('download_contract')['body']['value'];
        $params['tokens']['username'] = $tokens['username'];
        $params['tokens']['enterprise'] = $tokens['enterprise'];
        $params['tokens']['enterprise_num'] = $tokens['enterprise_num'];
        $params['tokens']['document'] = $tokens['document'];
        $params['tokens']['service_type'] = $tokens['service_type'];
        $params['tokens']['data_type'] = $tokens['data_type'];
        $params['tokens']['admin_enterprise'] = $tokens['admin_enterprise'];
        $params['tokens']['admin_mail'] = $tokens['admin_mail'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;

      case 'update_user_info':
        $params['subject'] = $settings->get('update_user_info')['subject'];
        $params['body'] = $settings->get('update_user_info')['body']['value'];
        $params['tokens']['admin_mail'] = $tokens['admin_mail'];
        $params['mail_to_send'] = $tokens['mail_to_send'];
        $this->params = $params;
        $this->send_mail();
        break;
    }
  }

  /**
   * Send the already configured email.
   */
  public function send_mail() {
    $send = \Drupal::service('plugin.manager.mail')
      ->mail('tbo_mail', 'default', $this->params['mail_to_send'], $this->params['langcode'], $this->params);
  }

  /**
   * Send the SMS message.
   *
   * @param string $type
   *   Template name.
   * @param array $tokens
   *   Message tokens.
   * @param bool $exception
   *   Sets the Exception parameter.
   */
  public function send_sms($type, $tokens, $exception = TRUE) {
    $token_service = \Drupal::token();
    $settings = \Drupal::config('tbo_mail.tbosmssettings');
    $bubbleable_metadata = new BubbleableMetadata();
    $sms_message = $token_service->replace($settings->get($type)['body'], $tokens, [], $bubbleable_metadata);
    $sms_message_thx = $settings->get($type)['subject'];
    $params['query'] = [
      'from' => $settings->get('sms_code'),
      'to' => $tokens['phone_to_send'],
      'text' => str_replace('#', '', $sms_message . ' ' . $sms_message_thx),
    ];

    if ($exception == FALSE) {
      $params['no_exception'] = 1;
    }

    try {
      $this->api->sendSMS($params);
    }
    catch (\Exception $e) {
      // get message.
      $message = str_replace('#', '', $sms_message . ' ' . $sms_message_thx);
      // Save action.
      $service = \Drupal::service('tbo_core.audit_log_service');
      $service->loadName();
      // Create array data[].
      $data = [
        'event_type' => t('sms'),
        'description' => t('Error en el envio del SMS'),
        'details' => t("Usuario @username presento error al enviar el mensaje a @phone con el mensaje @message", [
          '@username' => $service->getName(),
          '@phone' => $tokens['phone_to_send'],
          '@message' => $message,
        ]),
      ];

      // Save audit log.
      $service->insertGenericLog($data);
    }
  }

}
