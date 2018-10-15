<?php

namespace Drupal\tbo_api_hn;

use Drupal\tbo_api\TboApiClient;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_rest_api\Exception\RequestException;

/**
 * Class TboApiHnClient
 * @package Drupal\tbo_api_hn
 */
class TboApiHnClient extends TboApiClient implements TboApiClientInterface {
	
	/**
   *
   * @Method getClientAccountGeneralInfo
   *
   * @Endpoint https://api.tigo.com.hn/app.selfcareapp.gateway/rest/tigoapp/GetClientAccountGeneralInfo/{msisdn}?apikey=41541e9c127b5e1835aa195453ab3f9f
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getClientAccountGeneralInfo($params) {
  	$service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = [];

    //Get instance of child in parent class
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
    }

    return $response;
  }
  
  /**
   *
   * @Method getClientInfo
   *
   * @Endpoint https://test.api.tigo.com/v2/tigo/b2b/hn/crm/clients/rtn/08019001212686
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  
  public function getClientInfo($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = [];
    
    $extended = false;

    //Get instance of child in parent class
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
      
      if (isset($params['optionsCU'])) {
        $extended = $params['optionsCU']['extended'];
        unset ($params['optionsCU']);
      }
      
      $response = $this->client->callRestService($service, $query, $tokens);
      
      $responseReturn = FALSE;
      
      if (isset($response->client->company->name)) {
      	$responseReturn = $response->client->company->name;
      }
      
      if ($extended) {
				$responseReturn = array();
				
				if (isset($response->client->company->name)) {
		    	$responseReturn[0] = $response->client->company->name;
		    }
				
				if (isset($response->client->clientCode)) {
		    	$responseReturn[1] =  $response->client->clientCode;
		    }
				
			}
      
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $responseReturn;
  }
  
  
   /**
   *
   * @Method getBillingInfoPropossal
   *
   * @Endpoint http://qa.api.tigo.com/v2/tigo/b2b/hn/billing/customers/code/38/bills?quantity=3&end=2017-09-22&offset=1&limit=10
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getBillingInfoPropossal($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = [];

    //Get instance of child in parent class
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
      
      if (isset($params['headers'])) { 
        foreach ($params['headers'] as $headers_key => $headers_value) {
          $headers[$headers_key] = $headers_value;
        }
      }
      
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers );
   
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return $response;
  }


    //https://apitbo.tigo.com.hn/TigoBusinessOnline2/v2/tigo/tbo2/{codclient}/GetServiceInventory?initialResultNumber=1&resultsPerPage=30
    /**
   *
   * @Method getCustomerProductsById
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/hn/transactions/customers/clientId/38/products
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getCustomerProductsById($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = [];

    //Get instance of child in parent class
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
    }

    return $response;
  }



  /**
   * @Method putSimCard
   *
   * @Endpoint https://test.api.tigo.com/v2/tigo/b2b/hn/crm/clients/MSISDN/{phone}/simcard
   * @Request-method PUT
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function putSimCard($params) {
    
    $service = __FUNCTION__;
    //Init response []
    
    $response = false;
    
    $query = $tokens = $body = $headers = [];
    
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

    // Get data
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (RequestException $e) {
      //Validate send exception
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_hn');
      }
    }
    return $response;
  }
  
  
/*
*
* Funcion agregada como fix para notice en dashboard
* 
*
*/  
  
  public function getBillingInformation($params) {
    $service = __FUNCTION__;
    // Init response [].
    $response = $query = $tokens = [];

    if (isset($params['query'])) {
      $query = $params['query'];
    }
    if (isset($params['tokens'])) {
      $tokens = $params['tokens'];
    }
    
    $response = [];
    return $response;
  }
 
}
