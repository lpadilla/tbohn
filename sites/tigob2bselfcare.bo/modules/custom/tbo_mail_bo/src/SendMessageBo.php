<?php

namespace Drupal\tbo_mail_bo;

use Drupal\user\Entity\User;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_mail\SendMessage;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Class SendMessageBo.
 *
 * @package Drupal\tbo_mail_bo
 */
class SendMessageBo extends SendMessage {

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
    $settings = \Drupal::config('tbo_mail_bo.settings');
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
      case 'support_agent_bo':
        $params['subject'] = $settings->get('support_agent_bo')['subject'];
        $params['body'] = $settings->get('support_agent_bo')['body']['value'];
        $params['tokens'] = $settings->get('social_networks');
        $params['tokens']['subject_user'] = $tokens['subject_user'];
        $params['tokens']['mail_to_send'] = $tokens['mail_to_send'];
        $params['tokens']['body_user'] = $tokens['body_user'];
        $params['tokens']['name_company'] = $tokens['name_company'];
        $params['tokens']['username_admin_company'] = $tokens['username_admin_company'];
        $params['tokens']['nit'] = $tokens['nit'];
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
}