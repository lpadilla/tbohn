<?php

namespace Drupal\tigoid\Plugin\Config\OpenIDConnectClient;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\openid_connect\StateToken;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * OpenID Connect client config for TigoID.
 */
class TigoIdClass {

  protected $client;
  protected $loggerFactory;
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $client, RequestStack $requestStack) {
    $this->client = $client;
    $this->loggerFactory = new LoggerChannelFactory();
    $this->requestStack = $requestStack;
  }

  /**
   * Overrides OpenIDConnectClientBase::settingsForm().
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $configuration) {
    $form['authorization_endpoint'] = [
      '#title' => t('Authorization endpoint'),
      '#type' => 'textfield',
      '#default_value' => $configuration['authorization_endpoint'],
    ];

    $form['token_endpoint'] = [
      '#title' => t('Token endpoint'),
      '#type' => 'textfield',
      '#default_value' => $configuration['token_endpoint'],
    ];

    $form['userinfo_endpoint'] = [
      '#title' => t('UserInfo endpoint'),
      '#type' => 'textfield',
      '#default_value' => $configuration['userinfo_endpoint'],
    ];

    $form['revoke_endpoint'] = [
      '#title' => t('Revoke token endpoint'),
      '#type' => 'textfield',
      '#weight' => 100,
      '#default_value' => $configuration['revoke_endpoint'],
    ];

    $form['logout_endpoint'] = [
      '#title' => t('Close session endpoint'),
      '#type' => 'textfield',
      '#weight' => 100,
      '#default_value' => $configuration['logout_endpoint'],
    ];

    $form['allow_he'] = [
      '#type' => 'checkbox',
      '#title' => t('Allow Header Enrichment'),
      '#default_value' => $configuration['allow_he'],
      '#weight' => 101,
    ];

    $form['country_code'] = [
      '#type' => 'textfield',
      '#title' => t('Country code'),
      '#size' => 4,
      '#maxlength' => 2,
      '#default_value' => $configuration['country_code'],
      '#description' => t('When a line has HE, his country code will be compare with this value to allow silent-login'),
      '#weight' => 102,
    ];

    $form['indicative'] = [
      '#type' => 'textfield',
      '#title' => t("Indicative's phone"),
      '#size' => 4,
      '#maxlength' => 3,
      '#default_value' => $configuration['indicative'],
      '#weight' => 102,
    ];

    $form['url_resend'] = [
      '#type' => 'url',
      '#title' => t('URL to re-send mail'),
      '#size' => 128,
      '#maxlength' => 128,
      '#default_value' => $configuration['url_resend'],
      '#weight' => 102,
    ];

    // Vista de la tabla.
    $form['parameters_options'] = [
      '#type' => 'details',
      '#title' => t('Configuracion de parametros para TigoId'),
      '#open' => TRUE,
      '#weight' => 103,
    ];

    $form['parameters_options']['table_fields'] = [
      '#type' => 'table',
      '#header' => [t('Parametro'), t('Valor'), t('Enviar')],
      '#empty' => t('No hay parametros para enviar.'),
    ];

    // Add parameter scope .
    $form['parameters_options']['table_fields']['scope']['name'] = [
      '#plain_text' => 'scope',
    ];

    $form['parameters_options']['table_fields']['scope']['value'] = [
      '#type' => 'textfield',
      '#default_value' => isset($configuration['parameters_options']['table_fields']['scope']['value']) ? $configuration['parameters_options']['table_fields']['scope']['value'] : 'openid email phone profile',
      '#size' => 40,
    ];

    $form['parameters_options']['table_fields']['scope']['active'] = [
      '#type' => 'checkbox',
      '#default_value' => $configuration['parameters_options']['table_fields']['scope']['active'],
    ];

    // Add parameter response_type .
    $form['parameters_options']['table_fields']['response_type']['name'] = [
      '#plain_text' => 'response_type',
    ];

    $form['parameters_options']['table_fields']['response_type']['value'] = [
      '#type' => 'textfield',
      '#default_value' => isset($configuration['parameters_options']['table_fields']['response_type']['value']) ? $configuration['parameters_options']['table_fields']['response_type']['value'] : 'code',
      '#size' => 40,
    ];

    $form['parameters_options']['table_fields']['response_type']['active'] = [
      '#type' => 'checkbox',
      '#default_value' => $configuration['parameters_options']['table_fields']['response_type']['active'],
    ];

    // Add parameter ui_layout .
    $form['parameters_options']['table_fields']['ui_layout']['name'] = [
      '#plain_text' => 'ui_layout',
    ];

    $form['parameters_options']['table_fields']['ui_layout']['value'] = [
      '#type' => 'textfield',
      '#default_value' => isset($configuration['parameters_options']['table_fields']['ui_layout']['value']) ? $configuration['parameters_options']['table_fields']['ui_layout']['value'] : 'b2b_co',
      '#size' => 40,
    ];

    $form['parameters_options']['table_fields']['ui_layout']['active'] = [
      '#type' => 'checkbox',
      '#default_value' => $configuration['parameters_options']['table_fields']['ui_layout']['active'],
    ];

    return $form;
  }

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   */
  public function getEndpoints($configuration) {
    return [
      'authorization' => $configuration['authorization_endpoint'],
      'token' => $configuration['token_endpoint'],
      'userinfo' => $configuration['userinfo_endpoint'],
    ];
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   */
  public function retrieveTokens($authorization_code, $configuration, $endpoints, $pluginId) {
    // Add static cache.
    $resp_tokens = &drupal_static(__FUNCTION__ . $authorization_code);
    if (!isset($resp_tokens)) {
      // Exchange `code` for access token and ID token.
      $redirect_uri = Url::fromRoute(
        'tigoid.redirect_controller_redirect',
        [], ['absolute' => TRUE]
      )->toString(TRUE);

      $request_options = [
        'form_params' => [
          'code' => $authorization_code,
          'client_id' => $configuration['client_id'],
          'client_secret' => $configuration['client_secret'],
          'redirect_uri' => $redirect_uri->getGeneratedUrl(),
          'grant_type' => 'authorization_code',
        ],
      ];

      try {
        $response = $this->client->post($endpoints['token'], $request_options);
        $response_data = json_decode((string) $response->getBody(), TRUE);

        // Expected result.
        $tokens = [
          'id_token' => $response_data['id_token'],
          'access_token' => $response_data['access_token'],
        ];
        if (array_key_exists('expires_in', $response_data)) {
          $tokens['expire'] = \Drupal::time()->getRequestTime() + $response_data['expires_in'];
        }
        return $tokens;
      }
      catch (Exception $e) {
        $variables = [
          '@message' => 'Could not retrieve tokens',
          '@error_message' => $e->getMessage(),
        ];
        $this->loggerFactory->get('openid_connect_' . $pluginId)
          ->error('@message. Details: @error_message', $variables);
        return FALSE;
      }
    }
    return $resp_tokens;
  }

  /**
   * {@inheritdoc}
   */
  public function authorize($scope, $configuration, $endpoints) {
    $redirect_uri = Url::fromRoute(
      'tigoid.redirect_controller_redirect',
      [], ['absolute' => TRUE]
    )->toString(TRUE);

    $url_options = [
      'query' => [
        'client_id' => $configuration['client_id'],
        'redirect_uri' => $redirect_uri->getGeneratedUrl(),
        'state' => StateToken::create(),
      ],
    ];

    if ($configuration['parameters_options']['table_fields']['scope']['active']) {
      $url_options['query']['scope'] = $configuration['parameters_options']['table_fields']['scope']['value'];
    }

    if ($configuration['parameters_options']['table_fields']['response_type']['active']) {
      $url_options['query']['response_type'] = $configuration['parameters_options']['table_fields']['response_type']['value'];
    }

    if ($configuration['parameters_options']['table_fields']['ui_layout']['active']) {
      $url_options['query']['ui_layout'] = $configuration['parameters_options']['table_fields']['ui_layout']['value'];
    }

    if (isset($_SESSION['guest_user'])) {
      $url_options['query']['login_hint'] = $_SESSION['mail_invited'];
    }

    if (isset($_COOKIE['SESSION_CLOSED'])) {
      $url_options['query']['prompt'] = 'login';
    }

    // Clear _GET['destination'] because we need to override it.
    $this->requestStack->getCurrentRequest()->query->remove('destination');
    $authorization_endpoint = Url::fromUri($endpoints['authorization'], $url_options)->toString(TRUE);

    $response = new TrustedRedirectResponse($authorization_endpoint->getGeneratedUrl());
    $response->addCacheableDependency($authorization_endpoint);
    $response->addCacheableDependency($redirect_uri);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeHe($scope, $configuration, $endpoints) {
    $redirect_uri = Url::fromRoute(
      'tigoid.redirect_controller_redirect',
      [], ['absolute' => TRUE]
    )->toString(TRUE);

    $url_options = [
      'query' => [
        'client_id' => $configuration['client_id'],
        'response_type' => 'code',
        'scope' => $scope,
        'redirect_uri' => $redirect_uri->getGeneratedUrl(),
        'state' => StateToken::create() . "|HE",
        'prompt'  => 'none',
      ],
    ];

    // Clear _GET['destination'] because we need to override it.
    $this->requestStack->getCurrentRequest()->query->remove('destination');
    $authorization_endpoint = Url::fromUri($endpoints['authorization'], $url_options)->toString(TRUE);

    $response = new TrustedRedirectResponse($authorization_endpoint->getGeneratedUrl());
    $response->addCacheableDependency($authorization_endpoint);
    $response->addCacheableDependency($redirect_uri);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeValidationLine($scope, $configuration, $endpoints, $msisdn) {
    $redirect_uri = Url::fromRoute(
      'tigoid.redirect_controller_redirect',
      [], ['absolute' => TRUE]
    )->toString(TRUE);

    $url_options = [
      'query' => [
        'client_id' => $configuration['client_id'],
        'response_type' => 'code',
        'scope' => $scope,
        'redirect_uri' => $redirect_uri->getGeneratedUrl(),
        'state' => StateToken::create() . "|VL",
        'prompt'  => 'login',
        'login_hint' => $configuration['indicative'] . $msisdn,
      ],
    ];

    // Clear _GET['destination'] because we need to override it.
    $this->requestStack->getCurrentRequest()->query->remove('destination');
    $authorization_endpoint = Url::fromUri($endpoints['authorization'], $url_options)->toString(TRUE);

    $response = new TrustedRedirectResponse($authorization_endpoint->getGeneratedUrl());
    $response->addCacheableDependency($authorization_endpoint);
    $response->addCacheableDependency($redirect_uri);

    return $response;
  }

}
