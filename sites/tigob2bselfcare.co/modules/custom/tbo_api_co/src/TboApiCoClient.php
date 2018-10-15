<?php

namespace Drupal\tbo_api_co;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\tbo_api\TboApiClient;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_rest_api\Exception\RequestException;

/**
 * Class TboApiClient.
 *
 * @package Drupal\tbo_api
 */
class TboApiCoClient extends TboApiClient implements TboApiClientInterface {

  /**
   * Validate if exist enterprise.
   *
   * @param $params
   * @param bool $batch
   *
   * @return array|bool|mixed|string
   */
  public function customerByCustomerId($params, $batch = FALSE) {

    $service = __FUNCTION__;
    // Init vars.
    $query = $tokens = [];

    // Set $query.
    if (isset($params['query'])) {
      $query = $params['query'];
    }

    // Set $tokens.
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
      $tokens['docType'] = ($params['tokens']['docType'] == 'NIT' || $params['tokens']['docType'] == 'nit') ? 'NT' : strtoupper($params['tokens']['docType']);
    }

    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      if ($batch != TRUE) {
        if (!isset($params['no_exception'])) {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
        }
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function findCustomerAccountsByIdentification($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];
    $error = "";
    if (isset($params['query'])) {
      foreach ($params['query'] as $query_key => $query_value) {
        $query[$query_key] = $query_value;
      }
    }
    if (isset($params['tokens'])) {
      foreach ($params['tokens'] as $token_key => $token_value) {
        $tokens[$token_key] = $token_value;
      }
    }
    //Get instance of child in parent class
    $tokensDV = \Drupal::service('tbo_entities_co.miscellany')->concatenarDV($params['tokens'], 'clientId');
    //Get instance of child in parent class
    try {
      BaseApiCache::deleteGlobal('adf_rest_api:access_token');
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      $error = $e;
    }
    try {
      $responseDV = $this->client->callRestService($service, $query, $tokensDV);
    }
    catch (\Exception $e) {
      $error = $e;
    }
    BaseApiCache::deleteGlobal('adf_rest_api:access_token');
    if (!empty($response) && !empty($responseDV)) {
      $exists = false;
      if (isset($response->contractsCollection) && isset($responseDV->contractsCollection)) {
        $contracts = array_merge((array)$response->contractsCollection, (array)$responseDV->contractsCollection);
        $exists = true;
      }

      $response = array_merge((array)$response, (array)$responseDV);
      if ($exists) {
        $response['contractsCollection'] = $contracts;
      }
      $response = (object)$response;
    }
    elseif (empty($responseDV) && empty($response)) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $error, 'tigoapi_co');
      }
    }elseif(!empty($responseDV) && empty($response)){
      $response = $responseDV;
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getByAccountUsingCustomer($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];
    if (isset($params['query'])) {
      foreach ($params['query'] as $query_key => $query_value) {
        $query[$query_key] = $query_value;
      }
    }
    if (isset($params['tokens'])) {
      foreach ($params['tokens'] as $token_key => $token_value) {
        $tokens[$token_key] = $token_value;
      }
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
    }
    return $response;
  }

  /**
   *
   * @Method getCustomerByContractId
   *
   * @Endpoint https://{environmentPrefix}.api.tigo.com/v1/tigo/home/{countryCode}/billing/contracts/{contractId}/customers
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getCustomerByContractId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];

    // Get instance of child in parent class.
    try {
      if (isset($params['query'])) {
        foreach ($params['query'] as $query_key => $query_value) {
          $query[$query_key] = $query_value;
        }
      }

      if (isset($params['tokens'])) {
        foreach ($params['tokens'] as $token_key => $token_value) {
          $tokens[$token_key] = $token_value;
        }
      }

      $response = $this->client->callRestService($service, $query, $tokens);

    }
    catch (\Exception $e) {
      return FALSE;
      // Throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');.
    }

    return $response;
  }

  /**
   *
   * @Method getSpoolUrlByContractId
   *
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getSpoolUrlByContractId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];

    if (isset($params['query'])) {
      foreach ($params['query'] as $query_key => $query_value) {
        $query[$query_key] = $query_value;
      }
    }

    if (isset($params['tokens'])) {
      foreach ($params['tokens'] as $token_key => $token_value) {
        $tokens[$token_key] = $token_value;
      }
    }

    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
    }

    return $response;

  }

  /**
   *
   * @Method findCustomerBillsHistoryByAccountId
   *
   * @Endpoint http://{environmentPrefix}.api.tigo.com/v2/tigo/b2b/{countryCode}/billing/customers/contracts/{contractId}/bills
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function findCustomerBillsHistoryByAccountId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];

    // Get instance of child in parent class.
    try {
      if (isset($params['query'])) {
        $query = $params['query'];
      }
      if (isset($params['tokens'])) {
        $tokens = $params['tokens'];
      }

      $response = $this->client->callRestService($service, $query, $tokens);

    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi');
      }
    }

    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getCardToken($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];
    if (isset($params['query'])) {
      foreach ($params['query'] as $query_key => $query_value) {
        $query[$query_key] = $query_value;
      }
    }
    if (isset($params['tokens'])) {
      foreach ($params['tokens'] as $token_key => $token_value) {
        $tokens[$token_key] = $token_value;
      }
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function recurringInfoByContractId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function deleteCreditToken($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      foreach ($params['query'] as $query_key => $query_value) {
        $query[$query_key] = $query_value;
      }
    }
    if (isset($params['tokens'])) {
      foreach ($params['tokens'] as $token_key => $token_value) {
        $tokens[$token_key] = $token_value;
      }
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function deleteRecurringBillingInfo($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function updateWifiPassword($params) {

    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];

    if (isset($params['query'])) {
      foreach ($params['query'] as $query_key => $query_value) {
        $query[$query_key] = $query_value;
      }
    }

    if (isset($params['tokens'])) {
      foreach ($params['tokens'] as $token_key => $token_value) {
        $tokens[$token_key] = $token_value;
      }
    }

    if (isset($params['body'])) {
      $body = $params['body'];
    }

    // Get instance of child in parent class.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }

    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function createRecurringInfoByContractId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @return array|bool|mixed|string
   */
  public function addCreditToken($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Method GET.
   *
   * @param $params
   *
   * @return array|bool|mixed|string
   */
  public function paperlessByContractId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Method POST.
   *
   * @param $params
   *
   * @return array|bool|mixed|string
   */
  public function createPaperlessInvoice($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Method PUT.
   *
   * @param $params
   *
   * @return array|bool|mixed|string
   */
  public function updatePaperlessInvoice($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Method DELETE.
   *
   * @param $params
   *
   * @return array|bool|mixed|string
   */
  public function deletePaperlessInvoice($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * Method GET.
   *
   * @param $params
   *
   * @return array|bool|mixed|string
   */
  public function findCustomerPaymentByAccountIdAndBillNo($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @param $params
   * @param contractId
   * @param productId
   * @param subscription
   * @return array|bool|mixed|string
   */
  public function getByAccountUsingContract($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['body'])) {
      $body = $params['body'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @Method registerPayments
   *
   * @Request-method POST
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function checkoutPayments($params) {
    $service = __FUNCTION__;
    // Init response [].
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
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   *
   * @Method validatePaymentWithSignature
   *
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   * @throws \Exception
   */
  public function validatePaymentWithSignature($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   * @Method registerPaymentsSimple
   *
   * @Request-method POST
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function checkoutPaymentsSimple($params) {
    $service = __FUNCTION__;
    // Init response [].
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
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (RequestException $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   *
   * @Method checkoutByInvoiceId
   *
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   * @throws \Exception
   */
  public function checkoutByInvoiceId($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    // Get data.
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

  /**
   *
   * @Method checkoutByInvoiceId
   *
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   * @throws \Exception
   */
  public function getByAccountDataUsingContract($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = $body = $headers = [];
    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    if (isset($params['headers'])) {
      $headers = $params['headers'];
    }
    // Get data.
    try {
      $data = $this->client->callRestService($service, $query, $tokens, $body, $headers);
  
      $response = [
        'date' => ($data->fromDate == '') ? 'No disponoble' : $data->fromDate,
        'id' => ($data->measuringElement == '') ? 'No disponible' : $data->measuringElement,
        'serial' => ($data->devices[0]->serialNumber == '') ? 'No disponible' : $data->devices[0]->serialNumber,
        'equipo' => ($data->devices[0]->manufacturer == '') ? 'No disponible' : $data->devices[0]->manufacturer,
      ];
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_co');
      }
    }
    return $response;
  }

}
