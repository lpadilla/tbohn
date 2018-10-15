<?php

namespace Drupal\adf_rest_api\Exception;

use GuzzleHttp\Exception\RequestException as RequestExceptionGuzzle;

/**
 *
 */
class RequestException extends \RuntimeException {

  /**
   * Redefinir la excepción, por lo que el mensaje no es opcional.
   */
  public function __construct($url, $method, $params, RequestExceptionGuzzle $previous = NULL, RequestException $exception = NULL, $log_apiclient = NULL, RequestExceptionGuzzle $guzzleException = NULL) {
    $error_message = "Error llamando a servicio. %method %url %params - status code: %scode body response: %body";
    $status_code = '';
    $body_error = [];
    $send_exception = NULL;

    if ($exception != NULL) {
      $send_exception = $exception;
      if (method_exists($exception, 'getCode')) {
        $status_code = $exception->getCode();
      }

      if (method_exists($exception, 'getMessage')) {
        $body_error = $exception->getMessage();
      }
    }
    elseif ($previous != NULL) {
      $send_exception = $previous;
      if (method_exists($previous, 'getCode')) {
        $status_code = $previous->getCode();
      }
      elseif (method_exists($previous, 'getResponse')) {
        $status_code = $previous->getResponse()->getStatusCode();
      }

      if (method_exists($previous, 'getResponse')) {
        $body_error = @$previous->getResponse()->getBody()->getContents();
      }
    }
    elseif ($guzzleException != NULL) {
      $send_exception = $guzzleException;
      if (method_exists($guzzleException, 'getCode')) {
        $status_code = $guzzleException->getCode();
      }

      if (method_exists($guzzleException, 'getMessage')) {
        $body_error = $guzzleException->getMessage();
      }

    }

    $binds = [
      '%body' => $body_error,
      '%scode' => $status_code,
      '%url' => $url,
      '%method' => $method,
      '%params' => serialize($params),
    ];

    \Drupal::logger('AdfRestApi')->error($error_message, $binds);
    if ($log_apiclient != NULL) {
      \Drupal::logger($log_apiclient)->error($error_message, $binds);
    }

    // Assign parent exception.
    parent::__construct($body_error, $status_code, $send_exception);
  }

  /**
   * Representación de cadena personalizada del objeto.
   */
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }

}
