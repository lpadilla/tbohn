<?php

namespace Drupal\tbo_mail;

/**
 * Interface SendMessageInterface.
 *
 * @package Drupal\tbo_mail
 */
interface SendMessageInterface {

  /**
   *
   */
  public function send_message(array $tokens, $template);

}
