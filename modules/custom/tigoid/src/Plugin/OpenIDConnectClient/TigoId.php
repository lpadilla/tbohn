<?php

namespace Drupal\tigoid\Plugin\OpenIDConnectClient;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * OpenID Connect client for TigoID.
 *
 * Implements OpenID Connect Client plugin for Google.
 *
 * @OpenIDConnectClient(
 *   id = "tigoid",
 *   label = @Translation("TigoID")
 * )
 */
class TigoId extends OpenIDConnectClientBase {

  protected $confiClass;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack, ClientInterface $http_client, LoggerChannelFactory $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request_stack, $http_client, $logger_factory);
    $this->confiClass = \Drupal::service('tigoid.open_id_connect_client');
  }

  /**
   * Overrides OpenIDConnectClientBase::settingsForm().
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form = $this->confiClass->buildConfigurationForm($form, $form_state, $this->configuration);

    return $form;
  }

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   */
  public function getEndpoints() {
    return $this->confiClass->getEndpoints($this->configuration);
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   *
   * @param string $authorization_code
   *   A authorization code string.
   *
   * @return array|bool
   *   A result array or false.
   */
  public function retrieveTokens($authorization_code) {
    $endpoints = $this->getEndpoints();
    return $this->confiClass->retrieveTokens($authorization_code, $this->configuration, $endpoints, $this->pluginId);
  }

  /**
   * {@inheritdoc}
   */
  public function authorize($scope = 'openid email phone') {
    $endpoints = $this->getEndpoints();
    $response = $this->confiClass->authorize($scope, $this->configuration, $endpoints);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeHe($scope = 'openid mobileid') {
    $endpoints = $this->getEndpoints();
    $response = $this->confiClass->authorizeHe($scope, $this->configuration, $endpoints);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeValidationLine($scope = 'openid mobileid', $msisdn) {
    $endpoints = $this->getEndpoints();
    $response = $this->confiClass->authorizeValidationLine($scope, $this->configuration, $endpoints, $msisdn);
    return $response;
  }

  /**
   * Overrides retrieveUserInfo.
   */
  public function retrieveUserInfo($access_token) {
    // Add static cache.
    $response = &drupal_static(__FUNCTION__ . $access_token);
    if (!isset($response)) {
      $response = parent::retrieveUserInfo($access_token);
    }
    return $response;
  }

}
