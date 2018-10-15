<?php

namespace Drupal\tbo_api;

use Drupal\adf_rest_api\AdfRestApiClient;
use Drupal\adf_rest_api\Exception\RequestException;

define('TBO_API_REQUEST_ERROR', 700);
define('TBO_API_UNEXPECTED_RESPONSE_ERROR_MSG', 'Respuesta del servicio inesperada');
define('TBO_API_UNEXPECTED_RESPONSE_ERROR_CODE', 701);
define('TBO_API_CLIENT_NOTFOUND_MSG', "Client not found");
define('TBO_API_CLIENT_NOTFOUND_CODE', 702);

/**
 * Class TboApiClient.
 *
 * @package Drupal\tbo_api
 */
class TboApiClient implements TboApiClientInterface {
  protected $client;

  /**
   * TboApiClient constructor.
   *
   * @param mixed $adfRestApiClient
   *   $adfRestApiClient.
   */
  public function __construct(AdfRestApiClient $adfRestApiClient) {
    $this->client = $adfRestApiClient;
  }

  /**
   * Implement of getBillingInformation.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getBillingInformation($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $extra_options = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get instance of child in parent class.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, [], [], $extra_options);

      // Handle different names of responses according to the type of service.
      if (isset($params['query']['type'])) {
        if ($params['query']['type'] == 'fixed') {
          $response_name = 'GetBillingInformationFixResponse';
        }
        else {
          $response_name = 'GetBillingInformationMobileResponse';
        }
        if (isset($response->response->{$response_name}->responseBody)) {
          $response = $response->response->{$response_name}->responseBody;
        }
        else {
          $response = [];
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('tigoapi_getbillingInformation')->error($e->getMessage());
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getContractMSISDN.
   *
   * @param mixed $params
   *   Parametros del servicio.
   * @param mixed $list
   *   List.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getContractMSISDN($params, $list = FALSE) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    try {
      $response = $this->client->callRestService($service, $query, $tokens, [], [], $extra_options);
      if (isset($response->status) && $response->status == 200) {
        if ($list == TRUE) {
          $response = $response->response->GetContractMSISDNResponse->responseBody->msisdnList;
        }
        else {
          $response = $response->response->GetContractMSISDNResponse->responseBody->msisdnList->msisdn;
          $response = substr($response, 0, 10);
        }
      }
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getContractInformation.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getContractInformation($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $extra_options = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get instance of child in parent class.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, [], [], $extra_options);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of tolGetClientAccountGeneralInfo.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function tolGetClientAccountGeneralInfo($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $extra_options = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get instance of child in parent class.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, [], [], $extra_options);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getInvoicePDFMobile.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getInvoicePDFMobile($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $extra_options = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get instance of child in parent class.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, [], [], $extra_options);
      if (isset($response->status) && $response->status == 200) {
        $params = $response->response->GetInvoicePdfResponse->responseBody->additionalResult->ParameterType;
        if (isset($params) && strtolower($params->parameterName) == 'url' && !empty($params->parameterValue)) {
          $response = $params->parameterValue;
        }
      }
    }
    catch (\Exception $e) {
      throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
    }
    return $response;
  }

  /**
   * Implement of getCreditsCardByIdentification.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getCreditsCardByIdentification($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of getBillingAccount.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getBillingAccount($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of addCreditCard.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function addCreditCard($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of deleteCreditCards.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function deleteCreditCards($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of addBillingsAccount.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function addBillingsAccount($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of deleteBillingAccount.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function deleteBillingAccount($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of sendSMS.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function sendSMS($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $extra_options = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get instance of child in parent class.
    try {

      $tokens['send_mobile'] = TRUE;

      $response = $this->client->callRestService($service, $query, $tokens, [], [], $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getPaperlessInvoiceStatusV2.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getPaperlessInvoiceStatusV2($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of putPaperlessInvoice.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function putPaperlessInvoice($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of changeSimCard.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function changeSimCard($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of GetLineDetailsbyDocumentId.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function GetLineDetailsbyDocumentId($params) {
    $service = __FUNCTION__;

    // Tags.
    $tags = [$service];
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
      $stringTag = "$service:" . $query['id'];
      array_push($tags, "$service:" . $query['id']);
      $stringTag .= ':' . $query['idType'];
      array_push($tags, $stringTag);
      $stringTag .= ':' . $query['businessUnit'];
      array_push($tags, $stringTag);
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options, $tags);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of smsGprsMmsDetail.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function smsGprsMmsDetail($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of tolGetBalances.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function tolGetBalances($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getBillableMsisdn.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getBillableMsisdn($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of transferBalance.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function transferBalance($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getCallDetails.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getCallDetails($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getCustomerCallsDetailsService.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getCustomerCallsDetailsService($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Implement of getLineInfoMobile.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getLineInfoMobile($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getATPAccountsById.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getATPAccountsById($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getATPAccountProfilesByAccountId.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getATPAccountProfilesByAccountId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getATPAccountProfileDetailsByProfileId.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getATPAccountProfileDetailsByProfileId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of accountDetailsByCycle.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function accountDetailsByCycle($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of getATPAccountDetailsByAccountId.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getATPAccountDetailsByAccountId($params) {
    $service = __FUNCTION__;

    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (\Exception $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getLocalCallsByContractIdAndMeasuringElement.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getLocalCallsByContractIdAndMeasuringElement($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getCallsByContractIdAndMeasuringElement.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getCallsByContractIdAndMeasuringElement($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getPQRSByIdsByDocumentMovil.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getPQRSByIdsByDocumentMovil($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement of getSubscriberDevicesByCi.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getSubscriberDevicesByCi($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    if (isset($params['extra_options'])) {
      $extra_options = $params['extra_options'];
    }

    $headers['headers'] = [
      'Content-Type' => 'application/json',
    ];

    // Add type service.
    $extra_options = [
      'type_service' => 'movil',
      'decode_json' => FALSE,
    ];

    // Get data.
    try {
      $data = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
      // Format valid json.
      $data = str_replace('"support": "', '', $data);
      $data = substr($data, 0, -1);
      $response = json_decode($data);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }
    return $response;
  }

  /**
   * Implement tolBlockUnlock.
   *
   * @param mixed $params
   *   Service parameters.
   *
   * @return array|bool|mixed|string
   *   Response.
   */
  public function tolBlockUnlock($params) {
    $service = __FUNCTION__;

    $response = $query = $tokens = $body = $headers = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }

    // Add type service.
    $extra_options['type_service'] = 'movil';

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * Implement of getDigitalDocumentByClientId.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getDigitalDocumentByClientId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    // Add type service.
    $extra_options['type_service'] = 'movil';
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Implement of getAccountByDocIdAndDocType.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function getAccountByDocIdAndDocType($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    // Add type service.
    $extra_options['type_service'] = 'movil';
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Implement of forwardingVerificationEmail.
   *
   * @param mixed $params
   *   Parametros del servicio.
   *
   * @return array|bool|mixed|string
   *   Resultado de llamado a metodo.
   */
  public function forwardingVerificationEmail($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    // Add type service.
    $extra_options['type_service'] = 'movil';
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
    }
    catch (RequestException $e) {
      // Validate send exception.
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

}
