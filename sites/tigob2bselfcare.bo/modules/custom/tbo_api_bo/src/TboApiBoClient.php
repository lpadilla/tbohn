<?php

namespace Drupal\tbo_api_bo;

use Drupal\tbo_api\TboApiClient;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_rest_api\Exception\RequestException;


/**
 * Class TboApiBoClient
 * @package Drupal\tbo_api_bo
 */
class TboApiBoClient extends TboApiClient implements TboApiClientInterface {
  
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

    $messageDB = "DEBUG: " . $service; 
    \Drupal::logger('TboApiBoClient')->notice($messageDB); 

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
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/crm/customers/ruc/{nit}/generalInfo?offset=1&limit=10
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  
  public function getClientInfo($params) {
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
      
      if (isset($response->client->company->name)) {
        $responseReturn = $response->client->company->name;
      }
      
    }
    catch (\Exception $e) {
      return FALSE;    
    }

    return $responseReturn;
  }

  


  /**
   *
   * @Method getBalanceInquiry
   *
   *  @Endpoint https://prod.api.tigo.com/v1/tigo/mobile/bo/upselling/subscribers/{msisdn}/balances
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getBalanceInquiry($params) {
    
    $service = __FUNCTION__;    
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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
    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers );   
    }
    catch (\Exception $e) {
      return FALSE;
      
    }
    return $response;
  }

  
  /**
   *
   * @Method getFacturacionInformation
   *
   * @Endpoint http://{{host}}.api.tigo.com/v2/tigo/b2b/bo/billing/customers/number/13621643/bills?contractNumber=7065024&offset=1&limit=1&quantity=1
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  
  public function getFacturacionInformation($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = $headers = [];

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

  /** 
   *
   * @Method getEnterprise
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/crm/customers/{document}/{nit}/generalInfo?offset=1&limit=10
   * @Request.method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getEnterprise($params){
  	
  	$service = __FUNCTION__;    
    
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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

    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers );   
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return $response;
    }


    /**
   *
   * @Method getContracts
   *
   * @Endpoint http://{{host}}.api.tigo.com/v2/tigo/b2b/bo/crm/customers/clientId/13621643/services
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  
  public function getContractsMobile($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = $headers =[];

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
      
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);      
    }
    catch (\Exception $e) {
      return FALSE;
      
    }

    return $response;
  }

  /**
   *
   * @Method findCustomerBillsByCustomertNumber
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/billing/customers/number/{clientId}/bills?&offset=1&limit=2000&quantity=50
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
   
  public function findCustomerBillsByCustomertNumber($params)
  {
      $service = __FUNCTION__;
      //Init response []
      $response = $query = $tokens = $body = $headers =[];

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
          
          $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);

      }
      catch (\Exception $e)
      {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }

      return $response;
  }


 /**
   *
   * @Method getContracts
   *
   * @Endpoint http://{{host}}.api.tigo.com/v2/tigo/b2b/bo/crm/customers/clientId/13621643/services
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  
  public function getCustomerLinesByCustomerId($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = $headers =[];

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
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);      
    }
    catch (\Exception $e) {
      return FALSE;
      
    }

    return $response;
  }

  /**
   *
   * @Method PostTransferBalance
   *
   *  @Endpoint http://qa.api.tigo.com/v1/tigo/mobile/bo/balance_management/{number}/transfer
   * @Request-method POST
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function PostTransferBalance($params) {
    $service = __FUNCTION__;    
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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
    
    if (isset($params['headers'])) { 
      foreach ($params['headers'] as $headers_key => $headers_value) {
        $headers[$headers_key] = $headers_value;
      }
    }   

    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers );   
    }
    catch (\Exception $e) {
      if (!isset($params['no_exception'])) {
        throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }
    }
    return $response;
  }


  /**
   *
   * @Method getFacturacionInformation
   *
   * @Endpoint http://{{host}}.api.tigo.com/v2/tigo/b2b/bo/billing/customers/number/13621643/bills?contractNumber=7065024&offset=1&limit=1&quantity=1
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  
  public function getFacturacionInformationInicio($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = $headers = [];

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
      return $e;
      
    }

    return $response;
  }


  /**
   *
   * @Method findCustomerBillsByCustomertNumberWithContractNumberParam(
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/billing/customers/number/{clientId}/bills?contractNumber={}&offset=1&limit=2000&quantity=50
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function findCustomerBillsByCustomertNumberWithContractNumberParam($params)
  { // Servicio BO findCustomerBillsByCustomertNumber
      $service = __FUNCTION__;
      //Init response []
      $response = $query = $tokens = $body = $headers =[];

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
          
          $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);

      }
      catch (\Exception $e)
      {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }

      return $response;
  }


  /**
   *
   * @Method sendSMS
   *
   * @Endpoint https://prod.api.tigo.com/v1/tigo/mobile/kannel/sendsms?from={from}&to={to}&text={text}
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function sendSMS($params) {
    $service = __FUNCTION__;    
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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

    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers ); 
      return $response;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }
  
  
  
  /**
   *
   * @Method getCustomerGeneralInfoByCustomerId
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/crm/customers/{docType}/{documentNumber}/generalInfo?offset=1&limit=1000
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getCustomerGeneralInfoByCustomerId($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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

    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $response;
  }

  /**
   *
   * @Method getCustomerGeneralInfoByCustomerIdBySupportAgent
   *
   * @Endpoint https://api.tigo.com.hn/app.selfcareapp.gateway/rest/tigoapp/GetClientAccountGeneralInfo/{msisdn}?apikey=41541e9c127b5e1835aa195453ab3f9f
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getCustomerGeneralInfoByCustomerIdBySupportAgent($params) {
    $service = __FUNCTION__;
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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

    try {
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $response;
  }

  public function getCustomerSegmentLimitContract($params){

    $service = __FUNCTION__;    
    //Init response []
    $response = $query = $tokens = $body = $headers = $extra_options = [];
    //Get instance of child in parent class

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
    
    if (isset($params['extra_options'])) {
      foreach ($params['extra_options'] as $extra_options_key => $extra_options_value) {
        $extra_options[$extra_options_key] = $extra_options_value;
      }
    }
       
    try {			

      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers, $extra_options);
      
    }
    catch (\Exception $e) {
      return FALSE;
    } 
    
    return $response;
  }
  
  /**
   *
   * @Method getCustomerBasicPlanInfoByMsisdn
   *
   *  @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/crm/customers/{msisdn}/plans/info
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function getCustomerBasicPlanInfoByMsisdn($params){
   
    
    $service = __FUNCTION__;    
    //Init response []
    $response = $query = $tokens = $body = $headers = [];
    //Get instance of child in parent class

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
    try {
      
      $response = $this->client->callRestService($service, $query, $tokens, $body, $headers ); 
      return $response;
    }
    catch (\Exception $e) {
      return FALSE;
    }  
  }



public function db_change_varchar_field($entity_type_id, $field_name, $field_length, $default_value = '') {
	
  // Ignore entity manager caches.
  /** @var \Drupal\Core\Entity\EntityManager $entity_manager */
  $entity_manager = \Drupal::service('entity.manager');
  $entity_manager->useCaches(FALSE);

  /** @var \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface $schema_repository */
  $schema_repository = \Drupal::service('entity.last_installed_schema.repository');
  /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
  $entity_field_manager = \Drupal::service('entity_field.manager');
  $base_field_definitions = $entity_field_manager->getBaseFieldDefinitions($entity_type_id);
  $schema_repository->setLastInstalledFieldStorageDefinition($base_field_definitions[$field_name]);
  $field_storage_definitions = $schema_repository->getLastInstalledFieldStorageDefinitions($entity_type_id);
  $field_storage_definitions[$field_name]['schema'] = $field_storage_definitions[$field_name]->getSchema();
  $field_storage_definitions[$field_name]['schema']['columns']['value']['length'] = $field_length;
  $schema_repository->setLastInstalledFieldStorageDefinitions($entity_type_id, $field_storage_definitions);
  $is_revisionable = $field_storage_definitions[$field_name]->isRevisionable();

  // Update the storage schema.
  $key_value = \Drupal::keyValue('entity.storage_schema.sql');
  $key_name = $entity_type_id . '.field_schema_data.' . $field_name;
  $storage_schema = $key_value->get($key_name);
  $storage_schema[$entity_type_id . '_data']['fields'][$field_name]['length'] = $field_length;
  if ($is_revisionable) {
    $storage_schema[$entity_type_id . '_revision']['fields'][$field_name]['length'] = $field_length;
  }
  $key_value->set($key_name, $storage_schema);

  // Update the base database field.
  $database = \Drupal::database();
  $db_schema =  $database->schema();
  $db_schema->changeField($entity_type_id, $field_name, $field_name, [
    'type' => 'varchar',
    'length' => $field_length,
    'not null' => !empty($storage_schema[$entity_type_id]['fields'][$field_name]['not null']),
    'default' => $default_value,
  ]);

  // Update the revision database field.
  if ($is_revisionable) {
    $db_schema->changeField($entity_type_id . '_revision', $field_name, $field_name, [
      'type' => 'varchar',
      'length' => $field_length,
      'not null' => !empty($storage_schema[$entity_type_id]['fields'][$field_name]['not null']),
      'default' => $default_value,
    ]);
  }
}


  /**
   *
   * @Method findCustomerBillsByRuc(
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/billing/customers/ruc/{clientruc}/bills?offset=1&limit=2000&quantity=3
*/
  public function findCustomerBillsByRuc($params)
  { // Servicio BO findCustomerBillsByCustomertNumber
      $service = __FUNCTION__;
      //Init response []
      $response = $query = $tokens = $body = $headers =[];

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
          
          $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);

      }
      catch (\Exception $e)
      {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }

      return $response;
  }


  /**
   *
   * @Method findCustomerBillsByCustomertNumberWithContractNumberParam(
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/billing/customers/number/{clientId}/bills?contractNumber={contractNumber}&offset=1&limit=2000
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function findCustomerBillsByRucWithContractNumber($params)
  { // Servicio BO findCustomerBillsByRucWithContractNumber
      $service = __FUNCTION__;
      //Init response []
      $response = $query = $tokens = $body = $headers =[];

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
          
          $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);

      }
      catch (\Exception $e)
      {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }

      return $response;
  }

  /**
   *
   * @Method findCustomerBillsByCustomertNumberWithContractNumberParam(
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/billing/customers/number/{clientId}/bills?contractNumber={}&offset=1&limit=2000&quantity=50
   * @Request-method GET
   *
   * @param $params
   * @return array|bool|mixed|string
   */
  public function findCustomerBillsByRucWithContractNumberOne($params)
  { // Servicio BO findCustomerBillsByCustomertNumber
      $service = __FUNCTION__;
      //Init response []
      $response = $query = $tokens = $body = $headers =[];

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
          
          $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);

      }
      catch (\Exception $e)
      {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }

      return $response;
  }


  
  /**
   *
   * @Method findCustomerBillsByRuc(
   *
   * @Endpoint http://test.api.tigo.com/v2/tigo/b2b/bo/billing/customers/ruc/{clientruc}/bills?offset=1&limit=2000&status=PC
*/
  public function findCustomerBillsByRucNoLimit($params)
  { // Servicio BO findCustomerBillsByRutNoLimit
      $service = __FUNCTION__;
      //Init response []
      $response = $query = $tokens = $body = $headers =[];

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
          
          $response = $this->client->callRestService($service, $query, $tokens, $body, $headers);

      }
      catch (\Exception $e)
      {
          throw new RequestException(NULL, $service, $params, NULL, $e, 'tigoapi_bo');
      }

      return $response;
  }

}