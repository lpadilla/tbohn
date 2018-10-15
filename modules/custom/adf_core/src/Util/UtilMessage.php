<?php

namespace Drupal\adf_core\Util;

/**
 * Class UtilMessage
 * @package Drupal\adf_core\Util
 */
class UtilMessage {

  /**
   * @param \Exception $exception
   * @return array
   */
  public static function getMessage(\Exception $exception) {
    $configException = \Drupal::service('tbo_general.tbo_config')->getExceptionMessages(); //Get exception config
    $service = isset($exception->getTrace()[0]['function']) ? $exception->getTrace()[0]['function'] : ''; //Name of service
    if (strtoupper($service) == 'GET') {
      $service = isset($exception->other_mesagge) ? $exception->other_mesagge : $service;
    }
    // Validate whether to show the exception
    if ($configException['show_exception']) {
      // Get exception message
      $message_error = json_decode($exception->getMessage());
      // Set var $sendMessage with name of exception service and exception message
      $sendMessage = $service . ': ' . $exception->getMessage();
      // Validate if you only display the error message without other exception data.
      if ($configException['show_exception_only_message']) {
        $error = isset($message_error->error->developerMessage) ? $message_error->error->developerMessage : '';
        $sendMessage = $service . ': ' . $error;
        // If no exist $message_error->error->developerMessage then Set $message_error->fault->faultstring
        if (empty($error)) {
          $error = isset($message_error->fault->faultstring) ? $message_error->fault->faultstring : '';
          $sendMessage = $service . ': ' . $error;
        }
      }
      // Validate if the name of the service that caused the exception is displayed
      if (!$configException['show_service_error']) {
        $sendMessage = $exception->getMessage();
        //Validate if you only display the error message without other exception data.
        if ($configException['show_exception_only_message']) {
          $sendMessage = isset($message_error->error->developerMessage) ? $message_error->error->developerMessage : '';
          if (empty($sendMessage)) {
            $sendMessage = isset($message_error->fault->faultstring) ? $message_error->fault->faultstring : '';
          }
        }
      }
    }
    // Not show exception, show message in exception config
    else {
      $sendMessage = $configException['message'];
      if ($configException['show_service_error']) {
        $sendMessage = $service . ': ' . $sendMessage;
      }
    }
    // This variable is used to validate some exceptions behavior with empty data. Please do not remove it because it can alter the operation of any card.
    $onlyMessageError = isset($message_error->error->developerMessage) ? $message_error->error->developerMessage : '';
    // Set var message
    $message = [
      'error' => TRUE,
      'code' => $exception->getCode(),
      'message' => $sendMessage,
      'message_error' => $onlyMessageError,
    ];
    // Here Save audit exception in log
    // Return message
    return $message;
  }
}