<?php

namespace Drupal\tigoid\Services\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Manage config a 'AuthorizationControllerService'.
 */
class AuthorizationControllerService {

  /**
   * {@inheritdoc}
   */
  public function authorizeValidationLine($msisdn, $config, $client_name) {
    $configuration = $config->get('settings');
    $type = \Drupal::service('plugin.manager.openid_connect_client.processor');
    $client = $type->createInstance($client_name, $configuration);
    return $client->authorizeValidationLine(NULL, $msisdn);
  }

  /**
   * Return Json Response with HE URL Redirect.
   *
   * @return string
   *   Return Hello string.
   */
  public function authorizeHe($config, $client_name) {

    $configuration = $config->get('settings');

    $type = \Drupal::service('plugin.manager.openid_connect_client.processor');

    $client = $type->createInstance($client_name, $configuration);

    $endpoint = $client->authorizeHe()->getTargetUrl();

    $data = ['url' => $endpoint];

    $response = new JsonResponse($data);
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function authorize($config, $client_name) {
    $configuration = $config->get('settings');
    $type = \Drupal::service('plugin.manager.openid_connect_client.processor');

    // Include tigoIdEvent GTM.
    $gtm = \Drupal::service('selfcare_gtm');
    $gtm->push("tigoIdEvent", "Flow TigoID authentication", "Start authentication", "Redirect to TigoID");

    $client = $type->createInstance($client_name, $configuration);

    return $client->authorize();
  }

}
