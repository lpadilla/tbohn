<?php

namespace Drupal\selfcare_gtm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SelfcareGtmPushTagSuscriber.
 *
 * @package Drupal\selfcare_gtm
 */
class SelfcareGtmPushTagSuscriber implements EventSubscriberInterface {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.response'] = ['pushTag', -500];
    return $events;
  }

  /**
   * This method is called whenever the kernel.response event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function pushTag(Event $event) {

    $response = $event->getResponse();
    $statusCode = $response->getStatusCode();
    if (isset($_SESSION['gtm_event']) && $statusCode != '302') {
      $script = "<script>";
      $script .= "\ndataLayer = [";
      foreach ($_SESSION['gtm_event'] as $gtm_event) {
        $script .= "\n{
          \"event\" : \"" . $gtm_event['event'] . "\",
          \"selfcareCategory\" : \"" . $gtm_event['category'] . "\",
          \"selfcareAction\" : \"" . $gtm_event['action'] . "\",
          \"selfcareLabel\" : \"" . $gtm_event['label'] . "\"
        },";
      }
      $script .= "]" . "\n</script>";

      // Insert snippet after the opening body tag.
      $response_text = preg_replace('@<head[^>]*>@', '$0' . $script, $response->getContent(), 1);
      $response->setContent($response_text);
      unset($_SESSION['gtm_event']);
    }

  }

}
