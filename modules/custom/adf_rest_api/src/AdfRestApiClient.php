<?php

namespace Drupal\adf_rest_api;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilString;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Drupal\adf_rest_api\Exception\RequestException as AdfRestApiException;

define("ADF_REST_API_CONEXION_ERROR_CODE", 704);

/**
 * Class AdfRestApiClient.
 *
 * @package Drupal\adf_rest_api
 */
class AdfRestApiClient implements AdfRestApiClientInterface {
  public $endpoints;
  private $product_credentials;
  private $settings;

  /**
   * Constructor.
   */
  public function __construct() {
    // Cargar configuracion global.
    $this->settings = \Drupal::config('adf_rest_api.settings')->getRawData();
    // Cargamos todos los endpoints REST configurados en el sistema.
    $this->endpoints = $this->getEndPoints();
  }

  /**
   * Retorna los endpoints configurados.
   */
  private function getEndPoints() {
    $query = \Drupal::entityQuery('adf_rest_api_endpoint_entity');
    $nids = $query->execute();
    $endpoints_entities = \Drupal::entityManager()->getStorage('adf_rest_api_endpoint_entity')->loadMultiple($nids);
    $endpoints = [];
    foreach ($endpoints_entities as $entity) {
      $service_name = trim($entity->get('label'));
      $endpoint = [];
      $endpoint['base_url'] = trim($entity->get('endpoint'));
      $endpoint['method'] = trim($entity->get('method'));
      $endpoint['cache_time'] = (int) $entity->get('cache_time');
      $endpoint['settings'] = [
        'client_id' => trim($entity->get('client_id')),
        'client_secret' => trim($entity->get('client_secret')),
        'environment_prefix' => trim($entity->get('env_prefix')),
        'country_code' => trim($entity->get('country_iso')),
        'timeout' => (int) $entity->get('timeout'),
        'prefix_country' => (int) $entity->get('prefix_country'),
      ];
      // Set global settings values if local settings are empty.
      foreach ($endpoint['settings'] as $key => $option) {
        if (empty($option)) {
          $endpoint['settings'][$key] = $this->settings[$key];
        }
      }
      $endpoints[$service_name] = $endpoint;
      unset($endpoint);
    }
    return $endpoints;
  }

  /**
   *
   */
  public function setAccessTokenEndPoint() {
    $this->getAccessToken();
  }

  /**
   * @param null $method
   * @return mixed
   */
  public function getAccessToken($method = NULL) {
    $settingsMethod = $this->endpoints[$method]['settings'];
    $client_id = (isset($settingsMethod['client_id'])) ? $settingsMethod['client_id'] : $this->settings['client_id'];
    $client_secret = (isset($settingsMethod['client_secret'])) ? $settingsMethod['client_secret'] : $this->settings['client_secret'];
    $environment = (isset($settingsMethod['environment_prefix'])) ? $settingsMethod['environment_prefix'] : $this->settings['environment_prefix'];
    $cid = "adf_rest_api:access_token";
    if (empty($data = BaseApiCache::getGlobal($cid))) {
      $data = $this->oAuthClient($client_id, $client_secret, $environment);
      if ($data) {
        // Se le restan 10 segundos de diferencia para evitar que utilizar un token vencido.
        $expire_timestamp = ($data->expires_in - 10) / 60;
        BaseApiCache::setGlobal($cid, $data, $expire_timestamp);
      }
    }
    return $data->access_token;
  }

  /**
   * Setting the future basic request options.
   *
   * @param mixed[] $options
   *   Array structure to endpoint options.
   * @param mixed[] $base_url
   *   Array structure to URL query parameters.
   *
   * @return mixed[]
   */
  public function setBasicDefaults($options, $base_url = NULL) {
    // Remove optional parameter not passed to the web service.
    foreach ($options['query'] as $key => $value) {
      if (is_null($value)) {
        unset($options['query'][$key]);
      }
    }
    $query = $options['query'];
    // Assign optional values clientId, clientSecret
    // TODO si $options['client_id'] no existe leer configuracion global;.
    $clientId = $options['client_id'];
    // TODO si $options['client_id'] no existe leer configuracion global;.
    $clientSecret = $options['client_secret'];
    $defaults = [
      'headers' => [
        'Authorization' => 'Basic ' . base64_encode($clientId . ":" . $clientSecret),
      ],
      'query' => $query,
      'timeout' => 60,
    ];
    // Remove optional values clientId, clientSecret.
    unset($options['client_id']);
    unset($options['client_secret']);
    return $defaults;
    // Set product credentials.
    if ($this->product_credentials && $base_url !== NULL) {
      list($defaults['headers'], $base_url['base_url']) = $this->replaceBasicAuthentication($defaults['headers'], $base_url['base_url']);
      return [$defaults, $base_url];
    }
    else {
      return $defaults;
    }
  }

  /**
   * Setting the future request options.
   *
   * @param mixed[] $options
   *   Array structure to endpoint options.
   * @param mixed[] $base_url
   *   Array structure to URL query parameters.
   *
   * @return mixed[]
   */
  public function setRequestDefaults($options, $base_url) {
    // Remove optional parameter not passed to the web service.
    foreach ($options['query'] as $key => $value) {
      if (is_null($value)) {
        unset($options['query'][$key]);
      }
    }
    $query = $options['query'];
    $defaults = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $options['access_token'],
      ],
      'query' => $query,
      'timeout' => $this->settings['timeout'],
    ];
    if (isset($options['debug'])) {
      $defaults['debug'] = $options['debug'];
    }
    if (!empty($options['timeout'])) {
      $defaults['timeout'] = $options['timeout'];
    }
    if (!empty($options['body'])) {
      $defaults['body'] = $options['body'];
    }
    // Remove optional values clientId, clientSecret.
    unset($options['client_id']);
    unset($options['client_secret']);
    // Set product credentials.
    if ($this->product_credentials && $base_url) {
      list($defaults['headers'], $base_url['base_url']) = $this->replaceBearerAuthentication($defaults['headers'], $base_url['base_url']);
    }
    return [$defaults, $base_url];
  }

  /**
   * @param $method
   * @param $base_url
   * @param $params
   * @param array $send_options
   * @param bool $static_cache
   *
   * @return array|mixed|string
   * @throws \Exception
   */
  protected function send($method, $base_url, $params, $send_options = [], $static_cache = TRUE) {
    $arguments = func_get_args();
    $arguments = UtilString::getMultiImplode($arguments, '_');
    $hash = __FUNCTION__ . "_" . md5($arguments);
    if ($static_cache) {
      $body = &drupal_static($hash);
    }
    if (!isset($body)) {
      $url = UtilString::replaceTokensUrl($base_url);
      $client = \Drupal::httpClient();
      $logger_message = "Consumiendo servicio @url ";
      $binds = ['@url' => $url];
      \Drupal::logger('AdfRestApi')->info($logger_message, $binds);
      try {
        $response = $client->request($method, $url, $params);
      }
      catch (ConnectException $e) {
        // Delete Auth if is different.
        if (isset($send_options['delete_token']) && $send_options['delete_token']) {
          BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        }

        // Send segment track.
        $code = $e->getCode();
        $validate = $this->validateCodeException($code);
        if ($this->settings['segment_track_exception'] && !$validate) {
          $this->sendSegmentException($send_options, $e->getMessage(), $code);
        }

        // Send logger.
        $error_message = "Error conectando con @url - Error: @err";
        $binds = ['@url' => $url, '@err' => $e->getMessage()];
        \Drupal::logger('AdfRestApi')->error($error_message, $binds);
        // Add Guzzle Exception.
        throw new AdfRestApiException($url, $method, $params, $e);
        /*throw new \Exception("Error de conexión con el servicio $url " . strtr($error_message, $binds), ADF_REST_API_CONEXION_ERROR_CODE, $e);*/
      }
      catch (RequestException $e) {
        // Delete Auth if is different.
        if (isset($send_options['delete_token']) && $send_options['delete_token']) {
          BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        }

        // Send segment track.
        $code = $e->getCode();
        $validate = $this->validateCodeException($code);
        if ($this->settings['segment_track_exception'] && !$validate) {
          $this->sendSegmentException($send_options, $e->getMessage(), $code);
        }

        // Add Guzzle Exception.
        throw new AdfRestApiException($url, $method, $params, $e);
      }
      catch (\Exception $e) {
        // Delete Auth if is different.
        if (isset($send_options['delete_token']) && $send_options['delete_token']) {
          BaseApiCache::deleteGlobal('adf_rest_api:access_token');
        }

        // Send segment track.
        $code = $e->getCode();
        $validate = $this->validateCodeException($code);
        if ($this->settings['segment_track_exception'] && !$validate) {
          $this->sendSegmentException($send_options, $e->getMessage(), $code);
        }

        // Add Guzzle Exception.
        throw new AdfRestApiException($url, $method, $params, $e);
      }

      // Delete Auth if is different.
      if (isset($send_options['delete_token']) && $send_options['delete_token']) {
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
      }

      $body = (string) $response->getBody();
      if ($body == "" && $response->getStatusCode() === 201) {
        $body = json_encode($response->getReasonPhrase());
      }
      if (isset($send_options['decode_json']) && $send_options['decode_json']) {
        $body = json_decode($body);
      }
    }
    return $body;
  }

  /**
   * Cliente para consumir oAuthClient
   * Este servicio obtiene un token de acceso para posteriormente consumir otros servicios.
   *
   * @param null $clientId
   *   Client ID del app - Proveido por Apigee.
   * @param null $clientSecret
   *   Client secret del app - Proveido por Apigee.
   * @param null $environment
   *   Prefijo de ambiente.
   *
   * @return array|mixed|string
   *
   * @throws \Exception
   */
  public function oAuthClient($clientId = NULL, $clientSecret = NULL, $environment = NULL) {
    $method_name = __FUNCTION__;
    $options = $this->endpoints[$method_name]['settings'];
    if (empty($environment)) {
      $environment = $this->endpoints[$method_name]['settings']['environment_prefix'];
    }
    $options['query'] = [
      // Is fixed.
      'grant_type' => 'client_credentials',
    ];
    $options['client_id'] = (NULL !== $clientId) ? $clientId : $options['client_id'];
    $options['client_secret'] = (NULL !== $clientSecret) ? $clientSecret : $options['client_secret'];
    // TODO Validar si no estan seteados los valores de client_id y client_secret y retornar expecion en caso de.
    $defaults = $this->setBasicDefaults($options);
    $base_url = [
      $this->endpoints[$method_name]['base_url'],
      [
        'environmentPrefix' => $environment,
      ],
    ];

    $send_options['decode_json'] = TRUE;
    $response = $this->send($this->endpoints[$method_name]['method'], $base_url, $defaults, $send_options);
    if ($response) {
      return $response;
    }
    throw new \Exception("Error Processing Request", 1);
  }

  /**
   * @param $method_name
   * @param array $query
   * @param array $tokens
   * @param array $body
   * @param array $headers
   * @param array $extra_options
   * @param string[] $tags
   *
   * @return array|bool|mixed|string
   */
  public function callRestService($method_name, $query = [], $tokens = [], $body = [], $headers = [], $extra_options = [], array $tags = []) {
    $send_options = [];
    if (empty($extra_options['force_without_cache'])) {
      $without_cache = FALSE;
    }
    else {
      $without_cache = $extra_options['force_without_cache'];
    }
    $response = [];
    if (strtoupper($this->endpoints[$method_name]['method']) == 'GET' && $without_cache == FALSE) {
      $response = BaseApiCache::get("service", $method_name, array_merge($tokens, $query));
    }
    if (empty($response)) {
      // Get options with guzzle accepted format.
      $options = $this->endpoints[$method_name]['settings'];
      $send_options['delete_token'] = FALSE;
      if ($options['environment_prefix'] !== $this->settings['environment_prefix']) {
        $send_options['delete_token'] = TRUE;
        BaseApiCache::deleteGlobal('adf_rest_api:access_token');
      }

      $options['access_token'] = $this->getAccessToken($method_name);
      $options['query'] = $query;
      $options['body'] = $body;
      $array_base_url = [
        'environmentPrefix' => (!empty($options['environment_prefix'])) ? $options['environment_prefix'] : $this->settings['environment_prefix'],
        'countryCode' => (!empty($options['country_code'])) ? $options['country_code'] : $this->settings['country_code'],
      ];
      // Consideración especial para enviar sms.
      if (isset($tokens['send_mobile'])) {
        $options['query']['to'] = $options['prefix_country'] . $options['query']['to'];
      }
      if (!empty($tokens)) {
        foreach ($tokens as $key => $token) {
          $array_base_url[$key] = $token;
        }
      }
      $base_url = [$this->endpoints[$method_name]['base_url'], $array_base_url];
      list($defaults, $base_url) = $this->setRequestDefaults($options, $base_url);
      if (strtoupper($this->endpoints[$method_name]['method']) != 'GET') {
        $defaults['headers']['Content-Type'] = 'application/json';
      }
      if ($headers) {
        foreach ($headers as $header => $data) {
          $defaults['headers'][$header] = $data;
        }
      }

      // Validate json.
      if (!isset($extra_options['decode_json'])) {
        $send_options['decode_json'] = TRUE;
      }
      else {
        $send_options['decode_json'] = FALSE;
      }

      // Send parameters to exception.
      $send_options['tokens'] = $tokens;
      $send_options['query'] = $query;
      $send_options['type_service'] = $extra_options['type_service'];
      $send_options['method_name'] = $method_name;

      $response = $this->send($this->endpoints[$method_name]['method'], $base_url, $defaults, $send_options, TRUE);
      if ($response) {
        if (strtoupper($this->endpoints[$method_name]['method']) == 'GET') {
          $cache_time = $this->endpoints[$method_name]['cache_time'];
          if ($cache_time > 0) {
            BaseApiCache::set("service", $method_name, array_merge($tokens, $query), $response, $cache_time, $tags);
          }
        }
      }
    }
    return $response;
  }

  /**
   * @param array $options
   * @param string $message
   */
  public function sendSegmentException($options = [], $message = '', $code) {
    $tokens = $query = '';
    if (isset($options['tokens']) && !empty($options['tokens'])) {
      $tokens = ' - tokens: ';
      $tokens_last_one = $options['tokens'];
      end($tokens_last_one);
      $last_one = key($tokens_last_one);
      foreach ($options['tokens'] as $key => $value) {
        if ($key != $last_one) {
          $tokens .= $key . '=' . $value . ', ';
        }
        else {
          $tokens .= $key . '=' . $value;
        }
      }
    }

    if (isset($options['query']) && !empty($options['query'])) {
      $query = ' - query: ';
      $query_last_one = $options['query'];
      end($query_last_one);
      $last_one = key($query_last_one);
      foreach ($options['query'] as $key => $value) {
        if ($key != $last_one) {
          $query .= $key . '=' . $value . ', ';
        }
        else {
          $query .= $key . '=' . $value;
        }
      }
    }
    $event = 'TBO - Mensaje de excepción - ' . $options['method_name'];
    $category = 'Excepción';
    $label = $message . $tokens . $query . ' - ' . $options['type_service'];
    $value = (string) $code;
    \Drupal::service('adf_segment')->sendSegmentTrack($event, $category, $label, $value);
  }

  /**
   * @param $code
   * @return bool
   */
  public function validateCodeException($code) {
    $exceptions_code = $this->settings['segment_track_exception_code'];
    $exceptions_explode = [];
    if ($exceptions_code != '') {
      $exceptions_explode = explode(',', $exceptions_code);
    }
    $validate = in_array((string) $code, $exceptions_explode);
    return $validate;
  }

}
