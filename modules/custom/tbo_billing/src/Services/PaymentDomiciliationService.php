<?php

namespace Drupal\tbo_billing\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\rest\ResourceResponse;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\tbo_general\Services\TboConfigServiceInterface;

/**
 * Class PaymentDomiciliationService.
 *
 * @package Drupal\tbo_billing
 */
class PaymentDomiciliationService {

  private $api;
  protected $getRecurringInfo;
  private $tbo_config;

  /**
   * PaymentDomiciliationService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  /************************
   *** SCHEDULE PAYMENT ***
   ************************/

  /**
   * @return bool
   * @throws \Exception
   */
  public function haveRecurringPayment() {

    $function_name = __FUNCTION__;
    try {
      $value = $this->retrieveRecurringInfoByContractId();

      if ($value && count((array)$value) > 0) {
        return TRUE;
      }
    }
    catch (\Exception $e) {
      $error_text = "Error calling %function_name function, serialized error: %error";
      $error_binds = array(
        '%function_name' => $function_name,
        '%error' => print_r($e->getMessage(), TRUE)
      );
      $error = t((string)$error_text, $error_binds);
      throw new \Exception($error, $e->getCode(), $e);
    }

    return FALSE;
  }

  /**
   * @param $cards
   * @return array
   */
  public function getOptionsCards($cards) {
    $options = array();

    $value = $this->retrieveRecurringInfoByContractId();

    if ($value && count((array)$value) > 0) {
      $options['none'] = 'Quitar pago programado';
      $_SESSION['recurring_info_payment'] = $value;
    }
    else {
      $_SESSION['recurring_info_payment'] = FALSE;
    }

    if ($cards) {
      foreach ($cards as $card) {
        if (isset($card->cardToken)) {
          $card_info = $this->getMaskedCardNumber($card->cardInfo);

          $options[$card->cardToken] = '<span class="col ' . strtolower($card->cardBrand) . '">' . $card->cardBrand[0] . '</span> <span class="number">' . $card_info . '</span>';
        }
      }
    }

    return $options;
  }

  /**
   * @return mixed
   */
  protected function retrieveGetCardToken() {
    $response_cards = FALSE;
    $delete_cache = TRUE;
    $method_delete_get_card = 'getCardToken';
    $params_cache = [];
    $client_id = isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '';
    $client_type = isset($_SESSION['company']['docType']) ? $_SESSION['company']['docType'] : '';
    if ($_SESSION['environment'] == 'fijo') {
      // Parameters for service
      $params['tokens'] = [
        'docType' => $client_type,
        'clientId' => $client_id,
      ];

      //No generate exception
      $params['no_exception'] = TRUE;

      $response_cards = $this->api->getCardToken($params);
      if ($response_cards) {
        foreach ($response_cards as $key => $data) {
          if (strlen($data->cardInfo) > 4) {
            unset($response_cards[$key]);
          }
          else {
            $delete_cache = FALSE;
          }
        }

        if ($delete_cache) {
          //remove cache
          // Parameters for service getCardToken
          $params_cache = [
            'tokens' => [
              'docType' => $client_type,
              'clientId' => $client_id,
            ],
          ];
        }
      }
    }
    elseif ($_SESSION['environment'] == 'movil') {
      $params = [
        'tokens' => [
          'clientId' => $client_id,
        ],
        'headers' => [
          'transactionId' => $this->getTransactionId(),
          'platformId' => 12347,
        ],
      ];

      //No generate exception
      $params['no_exception'] = TRUE;
      $cards = [];
      $response = $this->api->getCreditsCardByIdentification($params);
      $response = $response->Envelope->Body->getCreditCardsByIdentificationResponse->CreditCard;
      if ($response) {
        $count = count($response);
        if ($count == 1) {
          if (strlen($response->creditCardNumber) > 4) {
            $object = new \stdClass();
            $object->cardToken = $response->tokenCorrelationId;
            $object->cardInfo = substr($response->creditCardNumber, -4);;
            $object->cardBrand = $response->paymentMethod;
            array_push($cards, $object);
          }
        }
        else {
          foreach ($response as $key => $data) {
            if (strlen($data->creditCardNumber) == 4) {
              unset($response_cards[$key]);
            }
            else {
              $object = new \stdClass();
              $object->cardToken = $data->tokenCorrelationId;
              $object->cardInfo = substr($data->creditCardNumber, -4);
              $object->cardBrand = $data->paymentMethod;
              array_push($cards, $object);
            }
          }
        }
        if ($cards) {
          $response_cards = $cards;
          $delete_cache = FALSE;
        }
        else {
          $method_delete_get_card = 'getCreditsCardByIdentification';
          $params_cache['tokens'] = [
            'clientId' => $client_id,
          ];
        }
      }
    }

    if ($delete_cache) {
      BaseApiCache::delete('service', $method_delete_get_card, array_merge($params_cache['tokens'], []));
    }

    return $response_cards;
  }

  /**
   * @return mixed
   * @throws \Exception
   */
  protected function retrieveRecurringInfoByContractId() {
    $recurring = FALSE;
    $delete_cache = TRUE;
    $method_delete_get_card = 'recurringInfoByContractId';
    $params_cache = [];
    $contractId = isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '';
    if ($_SESSION['environment'] == 'fijo') {
      $params['query'] = [
        'limit' => 4,
      ];

      $params['tokens'] = [
        'contractId' => $contractId,
      ];

      $params['no_exception'] = TRUE;

      $recurring = $this->api->recurringInfoByContractId($params);
    }
    elseif ($_SESSION['environment'] == 'movil') {
      $params = [
        'tokens' => [
          'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        ],
        'headers' => [
          'Content-Type' => 'application/json',
          'transactionId' => $this->getTransactionId(),
          'platformId' => 12347,
        ],
      ];

      $contract = isset($_SESSION['sendDetail']['contractId']) ? $_SESSION['sendDetail']['contractId'] : '';

      $params['no_exception'] = TRUE;

      $response = $this->api->getBillingAccount($params);
      $response = $response->Envelope->Body->getBillingAccountsToCollectByIdentificationResponse->BillingAccounts;
      if ($response) {
        $count = count($response);
        if ($count == 1) {
          if ($response->accountId == $contract) {
            $object = new \stdClass();
            $object->cardInfo = substr($response->creditCardNumber, -4);
            $object->cardBrand = $response->paymentMethod;
            $recurring = $object;
            $delete_cache = FALSE;
          }
        }
        else {
          for ($i = 0; $i < count($response); $i++) {
            if ($response[$i]->accountId == $contract) {
              $object = new \stdClass();
              $object->cardInfo = substr($response[$i]->creditCardNumber, -4);
              $object->cardBrand = $response[$i]->paymentMethod;
              $recurring = $object;
              $delete_cache = FALSE;
              break;
            }
          }
        }

        if ($delete_cache) {
          $params_cache = [
            'tokens' => [
              'clientId' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
            ],
            'query' => [],
          ];

          BaseApiCache::delete('service', 'getBillingAccount', array_merge($params_cache['tokens'], $params_cache['query']));
        }
      }
    }

    return $recurring;
  }

  /**
   * @return bool|mixed
   */
  public function getMyCards() {

    $data = $this->retrieveGetCardToken();

    if ($data) {
      return $data;
    }

    return FALSE;
  }

  /**
   * @return string
   */
  public function getTransactionId() {
    $date = new DrupalDateTime();
    return $date->format('YmdHis');
  }

  /**
   * @param $options
   * @param bool $first
   * @return bool|int|mixed|string
   * @throws \Exception
   */
  public function getDefaultOptionCards($options, $first = TRUE, $option_none = TRUE) {
    $function_name = __FUNCTION__;
    $default_value = FALSE;
    if ($first) {
      $default_value = key($options);
    }
    try {
      $data_recurring = (array)$_SESSION['recurring_info_payment'];

      if ($data_recurring) {

        foreach ($options as $key => $option) {
          if (isset($data_recurring['cardInfo']) && strpos($option, $data_recurring['cardInfo'])) {
            if (!$option_none) {
              $default_value = $key;
            }
            else {
              $default_value = 'none';
            }
            continue;
          }
        }
      }
    }
    catch (\Exception $e) {
      $error_text = "Error calling %function_name function, serialized error: %error";
      $error_binds = array(
        '%function_name' => $function_name,
        '%error' => print_r($e->getMessage(), TRUE)
      );
      $error = t((string)$error_text, $error_binds);
      throw new \Exception($error, $e->getCode(), $e);
    }

    return $default_value;
  }

  /**
   * @return array|bool
   * @throws \Exception
   */
  public function getDataRecurringPayment() {
    $function_name = __FUNCTION__;
    try {
      $recurring_payment = (array)$this->retrieveRecurringInfoByContractId();
      if (count($recurring_payment) > 0) {
        return $recurring_payment;
      }
    }
    catch (\Exception $e) {
      $error_text = "Error calling %function_name function, serialized error: %error";
      $error_binds = array(
        '%function_name' => $function_name,
        '%error' => print_r($e->getMessage(), TRUE)
      );
      $error = t((string)$error_text, $error_binds);
      throw new \Exception($error, $e->getCode(), $e);
    }

    return FALSE;
  }

  /**
   * @return array|mixed
   * @throws \Exception
   */
  public function getCardsToken() {
    try {
      $cards = $this->retrieveGetCardToken();
      if ($cards) {
        if (count($cards) > 0) {
          return $cards;
        }
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return FALSE;
  }

  /**
   * @param $cardToken
   * @return array|bool|mixed
   * @throws \Exception
   */
  public function getDataCardToken($cardToken) {
    try {
      $cards = $this->getCardsToken();
      if ($cards) {
        if (count($cards) > 0) {
          foreach ($cards as $card) {
            $card = (array)$card;
            if ($card['cardToken'] === $cardToken) {
              return $card;
            }
          }
        }
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return FALSE;
  }

  // 91-VISA, 92-MASTERCARD, 93-AMEX, 94-DINERS, 95-CREDENCIAL
  /**
   * @param $cardBrand
   * @return string
   */
  public function getPrefixCardBrand($cardBrand) {
    switch ($cardBrand) {
      case 'VISA' :
        return '91';
        break;
      case 'MASTERCARD' :
        return '92';
        break;
      case 'AMEX' :
        return '93';
        break;
      case 'DINERS' :
        return '94';
        break;
      case 'CREDENCIAL' :
        return '95';
        break;
    }
  }

  /**
   * @return array
   */
  public function getAdminsCompany() {
    $database = \Drupal::database();
    $query = $database->select('company_user_relations_field_data', 'company_relation');
    $query->innerJoin('user__roles', 'user_rol', 'user_rol.entity_id = company_relation.users');
    $query->leftJoin('users_field_data', 'user', "user.uid = user_rol.entity_id");
    $query->addField('user', 'uid');
    $query->addField('user', 'mail');
    $query->addField('user', 'full_name');
    $query->addField('user_rol', 'roles_target_id');
    $query->condition('company_relation.company_id', $_SESSION['company']['id']);
    $query->condition('user_rol.roles_target_id', 'admin_company');

    $result = $query->execute()->fetchAll();

    $return = [];
    foreach ($result as $r) {
      $return[$r->uid] = $r;
    }

    return $return;
  }

  /**
   * @param $service_message
   * @param $template
   * @param $name
   * @param $mail
   * @param $uid
   * @param $enterprise_name
   * @param $document_number
   * @param $document_type
   * @param $contractId
   * @param $cardInfo
   * @param $cardBrand
   */
  public function sendEmail($service_message, $template, $name, $mail, $uid, $enterprise_name, $document_number, $document_type, $contractId, $cardInfo, $cardBrand) {

    $users = $this->getAdminsCompany();
    $tokens_email['date'] = date('Y-m-d h:i:s a');
    $tokens_email['name'] = $name;
    $tokens_email['enterprise'] = $enterprise_name;
    $tokens_email['enterprise_num'] = $document_number;
    $tokens_email['document'] = $document_type;
    $tokens_email['contract_id'] = $contractId;
    $tokens_email['card_number'] = $cardInfo;
    $tokens_email['card_brand'] = $cardBrand;

    foreach ($users as $user) {
      $current_uri = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];
      $url = $current_uri;

      $tokens_email['mail_to_send'] = $user->mail;
      $tokens_email['user'] = $user->full_name;
      $tokens_email['link'] = $url;
      $send = $service_message->send_message($tokens_email, $template);
    }
  }

  /**
   * @return \Drupal\rest\ResourceResponse|\StdClass
   *
   */
  public function getRecurringInfoByContractId($card_tokens) {

    $value = $_SESSION['recurring_info_payment'];

    $data = new \StdClass();

    if (!$value) {
      $data->message = 'show_description_block_payment_not_configured';

      $data->buttons = [
        'show_add_card_button'
      ];

      if ($card_tokens) {
        $data->buttons = [
          'show_add_programmer_payment_button', 'show_my_cards_link'
        ];
      }

      return $data;
    }
    elseif (!empty($value)) {

      $value = (array)$value;

      if ($value['paymentMethod'] == 'Debit') {
        $data->message = 'show_description_block_payment_method_debit';
      }
      else {
        $data->message = 'show_description_block_configured_payment';
        $data->buttons = [
          'show_edit_programmer_payment_button', 'show_my_cards_link'
        ];
        $cardBrand = explode('-', $value['cardBrand']);
        $cardBrand = isset($cardBrand[1]) ? $cardBrand[1] : $value['cardBrand'];

        $card_info = $this->getMaskedCardNumber($value['cardInfo']);

        $card = '<span class="col s1 ' . strtolower($cardBrand) . '">' . $cardBrand[0] . '</span> <span class="number col s10">' . $card_info . '</span>';

        $data->data_recurring_payment = [
          'card' => $card
        ];

      }

      return $data;
    }

    $error = [
      'error_code' => 500,
      'error_message' => "Error", //$value->Error->reason,
    ];

    $response = new ResourceResponse($error, 500);
    $response->setMaxAge(0);
    $response->setVary(time());

    return $response;
  }

  /**
   * @param $cardNumber
   * @return string
   */
  public function getMaskedCardNumber($cardNumber) {
    // si la cantidad de los digitos de la tarjeta que trae el servicio es mayor 4
    // tomamos los ultimos cuatro digitos y agregamos los asteriscos
    $showCardInfo = '**** **** **** ' . $cardNumber;
    if (strlen($cardNumber) > 4) {
      $showCardInfo = '**** **** **** ' . substr($cardNumber, -4);
    }
    return $showCardInfo;
  }

}
