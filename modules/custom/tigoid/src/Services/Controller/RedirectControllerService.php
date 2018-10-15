<?php

namespace Drupal\tigoid\Services\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;
use Drupal\openid_connect\StateToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Class RedirectController.
 *
 * @package Drupal\tigoid\Controller
 */
class RedirectControllerService {

  use StringTranslationTrait;

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var \Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $pluginManager;

  /**
   * The request stack used to access request globals.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    OpenIDConnectClientManager $plugin_manager,
    RequestStack $requestStack,
    LoggerChannelFactory $loggerChannelFactory,
    AccountInterface $current_user
  ) {
    $this->pluginManager = $plugin_manager;
    $this->requestStack = $requestStack;
    $this->loggerFactory = $loggerChannelFactory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.openid_connect_client.processor'),
      $container->get('request_stack'),
      $container->get('logger.factory'),
      $container->get('current_user')
    );
  }

  /**
   * Access callback: Redirect page.
   *
   * @return bool
   *   Whether the state token matches the previously created one that is stored
   *   in the session.
   */
  public function access() {
    // Confirm anti-forgery state token. This round-trip verification helps to
    // ensure that the user, not a malicious script, is making the request.
    $query = $this->requestStack->getCurrentRequest()->query;
    $state_token = $query->get('state');
    if (strpos($state_token, '|') !== FALSE) {
      $state_token = explode("|", $state_token);
      $state_token = array_shift($state_token);
    }
    if ($state_token && StateToken::confirm($state_token)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Redirect.
   *
   * {@inheritdoc}
   */
  public function authenticate($client_name, $configuration) {
    $query = $this->requestStack->getCurrentRequest()->query;

    $state_token = $query->get('state');

    unset($_SESSION['openid_connect_state']);

    $case = TIGOID_REDIRECT_ONDEMAND;

    if (strpos($state_token, '|') !== FALSE) {
      $token = explode("|", $state_token);
      if ($token[1] == 'HE') {
        $case = TIGOID_REDIRECT_HE;
      }
      if ($token[1] == 'VL') {
        $case = TIGOID_REDIRECT_VALIDATION_LINE;
      }
    }

    $client = $this->pluginManager->createInstance(
      $client_name,
      $configuration
    );

    if ($case == TIGOID_REDIRECT_VALIDATION_LINE || $case == TIGOID_REDIRECT_ONDEMAND) {

      $query = $this->requestStack->getCurrentRequest()->query;

      if (!$query->get('error') && (!$client || !$query->get('code'))) {
        // In case we don't have an error, but the client could not be loaded or
        // there is no state token specified, the URI is probably being visited
        // outside of the login flow.
        throw new NotFoundHttpException();
      }

      if ($case == TIGOID_REDIRECT_ONDEMAND) {
        $url = $this->authenticateOnDemand($client, $client_name);
        return $url;
      }

    }

  }

  /**
   * {@inheritdoc}
   */
  private function authenticateOnDemand($client, $client_name = '') {
    $query = $this->requestStack->getCurrentRequest()->query;

    $provider_param = ['@provider' => $client->getPluginDefinition()['label']];

    if ($query->get('error')) {
      // Include tigoIdEvent GTM.
      $gtm = \Drupal::service('selfcare_gtm');
      $gtm->push("tigoIdEvent", "Flow TigoID authentication", "Unsuccessful Authorization", $query->get('error'));

      if (in_array($query->get('error'), [
        'interaction_required',
        'login_required',
        'account_selection_required',
        'consent_required',
      ])) {
        // If we have an one of the above errors, that means the user hasn't
        // granted the authorization for the claims.
        drupal_set_message($this->t('Logging in with <b>@provider</b> has been canceled.', $provider_param), 'warning');
      }
      else {
        // Any other error should be logged. E.g. invalid scope.
        $variables = [
          '@error' => $query->get('error'),
          '@details' => $query->get('error_description'),
        ];
        $message = 'Authorization failed: @error. Details: @details';
        $this->loggerFactory->get('openid_connect_' . $client_name)
          ->error($message, $variables);
        drupal_set_message($this->t('Error al iniciar sesión, por favor intenta de nuevo'), 'error');
      }
      return Url::fromUri('<front>')->toString();
    }

    // Get parameters from the session, and then clean up.
    $parameters = [
      'destination' => 'user',
      'op' => 'login',
      'connect_uid' => NULL,
    ];

    foreach ($parameters as $key => $default) {
      if (isset($_SESSION['openid_connect_' . $key])) {
        $parameters[$key] = $_SESSION['openid_connect_' . $key];
        unset($_SESSION['openid_connect_' . $key]);
      }
    }

    $destination = $parameters['destination'];
    $provider_param = ['@provider' => $client->getPluginDefinition()['label']];

    // Process the login or connect operations.
    try {
      $tokens = $client->retrieveTokens($query->get('code'));
    }
    catch (\Exception $e) {
      $tokens = FALSE;
      drupal_set_message("error", $this->t("En este momento no podemos validar tu información por favor intenta de nuevo."));
      \Drupal::logger('openid_connect')
        ->error($e->getCode() . " " . $e->getMessage());
    }

    if ($tokens) {
      // Get tokens tigoId for code.
      $user_data = $client->decodeIdToken($tokens['id_token']);
      $userinfo = $client->retrieveUserInfo($tokens['access_token']);
      $_SESSION['userInfo'] = $userinfo;

      if ($parameters['op'] === 'login') {
        $success = $this->completeAuthorizationOnDemand($client, $tokens, $destination);
        \Drupal::logger('notice')->notice('completeAuthorizationOnDemand');
        if (!$success) {
          drupal_set_message($this->t('Logging in with <b>@provider</b> could not be completed due to an error.', $provider_param), 'error');
        }
      }
      elseif ($parameters['op'] === 'connect' && $parameters['connect_uid'] === $this->currentUser->id()) {

        $old_email = $this->currentUser->getEmail();
        $message = t('Tu información ha sido actualizada.');

        if ($old_email != $user_data['email']) {
          $label_event = 'Old account updated with new email registered in TigoID';
          $message = t('Has utilizado un correo electrónico diferente al de tu cuenta antigua, así que tu información ha sido actualizada con el nuevo correo <b>@email</b>. ¡Gracias!', ['@email' => $user_data['email']]);

          // Eliminar cuenta si existe el correo.
          $exist_account = $this->deleteExistUserByEmail($user_data['email']);
          if ($exist_account) {
            $add_link = \Drupal::l('añadir nuevas líneas', new Url('user_lines.manage_lines'));
            $message = t("Tus cuentas registradas con los correos
              <b>@email</b> y <b>@before_email</b> 
              han sido unificadas utilizando como correo principal <b>@email</b>.
              <br> En esta unificación hemos conservado únicamente las líneas asociadas a
              <b>@before_email</b>; las líneas asociadas a tu cuenta <b>@email</b>
              han sido descartadas. Recuerda que siempre podrás @add_link a tu cuenta.",
              [
                '@before_email' => $old_email,
                '@email' => $user_data['email'],
                '@add_link' => $add_link,
              ]);
            $label_event = 'Accounts with differents emails were merged';
          }

          $evento[] = [
            'event' => "tigoIdEvent",
            'category' => "Flow TigoID migration",
            'action' => "Successful migration",
            'label' => $label_event,
          ];
          // Se actualiza cuenta con el correo de tigoId.
          $this->updateCurrentUserEmail($user_data['email']);
        }
        else {
          // Definicion eventos GTM.
          $evento[] = [
            'event' => "tigoIdEvent",
            'category' => "Flow TigoID authentication",
            'action' => "Successful authentication",
            'label' => "First TigoID session (Automigration)",
          ];
          $evento[] = [
            'event' => "tigoIdEvent",
            'category' => "Flow TigoID migration",
            'action' => "Successful migration",
            'label' => "Automigrate user with same email",
          ];
        }

        // Include event GTM.
        $gtm = \Drupal::service('selfcare_gtm');
        foreach ($evento as $value) {
          $gtm->push($value['event'], $value['category'], $value['action'], $value['label'], 0);
        }
        unset($evento);

        $success = openid_connect_connect_current_user($client, $tokens);
        if ($success) {
          drupal_set_message($message);
        }
        else {
          drupal_set_message(t('Connecting with @provider could not be completed due to an error.', $provider_param), 'error');
        }

      }

      if (isset($userinfo['phone_number'])) {
        // Guardar linea que proviene de TigoID
        // $this->updateLineCurrentUser($userinfo['phone_number']);.
      }
      else {
        \Drupal::logger('tigoid')
          ->error("phone_number no existe en la informacion devuelta por TigoID. No se pudo asociar la linea. Informacion retornada por TigoID: <pre>" . print_r($userinfo, TRUE) . "</pre>");
      }

    }

    // Almacenar una cookie para establecer
    // tigoid como el modo por defecto para autenticacion.
    setcookie('AUTHORIZATION_OP', 'tigoid', time() + 3600 * 24 * 365, '/');
    \Drupal::moduleHandler()
      ->alter("tigoid_successful_login_destination", $destination);

    // Save var session for login user.
    $_SESSION['user_login_in'] = TRUE;

    $user = \Drupal::currentUser();
    $account = $user->getAccount();

    // Usuario inactivo.
    if ($account->get('status')->value === '0') {
      // Cerrar sesion usuario.
      $session_manager = \Drupal::service('session_manager');
      $session_manager->setOptions(['cookie_lifetime' => 0]);
      // Force-start a session.
      $session_manager->start();
      // Check whether a session has been started.
      $session_manager->isStarted();
      // Migrate the current session
      // from anonymous to authenticated (or vice-versa).
      $session_manager->regenerate();

      // Redireccionar a login de drupal con mensaje.
      drupal_set_message(t('Lo sentimos, al parecer su cuenta de usuario se encuentra inactiva, lo invitamos a que se comunique con un super administrador del sitio o intente iniciar sesion desde la URL de invitado'), 'error');

      return URL::fromUri('internal:/')->toString();
    }

    // FIXME Validación nuevo flujo.
    $newFlow = isset($_SESSION['click_login_info']['new_flow']) ? $_SESSION['click_login_info']['new_flow'] : FALSE;
    $action = $_SESSION['click_login_info']['button'];

    // Se verifica que siga el nuevo flujo, que se encuentre autenticado
    // y que de click en el boton crear cuenta.
    if ($newFlow && $user->isAuthenticated() && $action == 'create') {
      // Obtenemos todos los roles del usuario.
      $roles = $user->getRoles();
      $roles = array_diff($roles, ['authenticated']);
      if ($roles) {
        if (count($roles) == 1) {
          $rolName = array_shift($roles);
          switch ($rolName) {
            case 'super_admin':
            case 'tigo_admin':
              return URL::fromUri('internal:/empresa')->toString();

            case 'ejecutivo':
              // Informar de la novedad.
              drupal_set_message($this->t('No tiene permisos para auto crear una cuenta, por favor comuníquese con un administrador del sitio'), 'error');
              return URL::fromUri('internal:/emular-sesion')->toString();

            case 'admin_company':
              // \Drupal\tbo_general\Services\Controller\CompanySelectorControllerClass
              // $companySelectorClass.
              $companySelectorClass = \Drupal::service('tbo_general.company_selector_controller');
              $companies = $companySelectorClass->getCompaniesUser();
              if (count($companies) == 1) {
                $company = reset($companies);
                $result = $companySelectorClass->companySelector($company->company_id);
              }
              return URL::fromRoute('tbo_account.create_account_form')->toString();

            case 'admin_group':
              $loginInfo = $_SESSION['click_login_info'];
              if ($loginInfo['button'] == 'login') {
              }
              return URL::fromRoute('tbo_account.create_account_form')->toString();
          }
        }
        // TODO Implementar funcionalidad en caso de tener más de un rol asociado.
        // Cerrar sesion usuario.
        unset($_SESSION['click_login_info']);
        // Cerrar sesion usuario.
        $session_manager = \Drupal::service('session_manager');
        $session_manager->setOptions(['cookie_lifetime' => 0]);
        // Force-start a session.
        $session_manager->start();
        // Check whether a session has been started.
        $session_manager->isStarted();
        // Migrate the current session
        // from anonymous to authenticated (or vice-versa).
        $session_manager->regenerate();
        // Informar de la novedad.
        drupal_set_message($this->t('Error al iniciar sesión, el usuario tiene más de un ROL asociado.'), 'error');
        // Redireccionar al login.
        return Url::fromUri('internal:/')->toString();
      }
      else {
        // Flujo para cuando el usuario no tiene rol asociado.
        return URL::fromRoute('tbo_account.create_account_form')->toString();
      }
    }

    // Redirect select enterprise.
    return URL::fromUri('internal:/tbo_general/selector/0')->toString();

  }

  /**
   * TODO verficar.
   */
  public function authenticationType($client, $tokens) {

    /* @var \Drupal\openid_connect\Authmap $authmap */
    $authmap = \Drupal::service('openid_connect.authmap');
    $user_data = $client->decodeIdToken($tokens['id_token']);
    $userinfo = $client->retrieveUserInfo($tokens['access_token']);
    $logger = \Drupal::logger('openid_connect');

    if ($userinfo && empty($userinfo['email'])) {
      $message = 'No e-mail address provided by @provider';
      $variables = ['@provider' => $client->getPluginId()];
      $logger->error($message . ' (@code @error). Details: @details', $variables);
      return TIGOID_UNKNOWN_AUTHENTICATION_TYPE;
    }

    $sub = openid_connect_extract_sub($user_data, $userinfo);
    if (empty($sub)) {
      $message = 'No "sub" found from @provider';
      $variables = ['@provider' => $client->getPluginId()];
      $logger->error($message . ' (@code @error). Details: @details', $variables);
      return TIGOID_UNKNOWN_AUTHENTICATION_TYPE;
    }

    /* @var \Drupal\user\UserInterface $account */
    $account = $authmap->userLoadBySub($sub, $client->getPluginId());
    if ($account) {
      return TIGOID_SIGNIN_AUTHENTICATION_TYPE;
    }

    // Check whether there is an e-mail address conflict.
    $account = user_load_by_mail($userinfo['email']);
    if ($account) {
      return TIGOID_MIGRATE_AUTHENTICATION_TYPE;
    }
    else {
      return TIGOID_SIGNUP_AUTHENTICATION_TYPE;
    }
  }

  /**
   * Complete the authorization after tokens have been retrieved.
   *
   * @param object $client
   *   The client.
   * @param array $tokens
   *   The tokens as returned from
   *   OpenIDConnectClientInterface::retrieveTokens().
   * @param string|array &$destination
   *   The path to redirect to after authorization.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function completeAuthorizationOnDemand($client, array $tokens, &$destination) {
    $active = FALSE;
    if (\Drupal::currentUser()->isAuthenticated()) {
      throw new \RuntimeException('User already logged in');
    }

    /* @var \Drupal\openid_connect\Authmap $authmap */
    $authmap = \Drupal::service('openid_connect.authmap');
    $user_data = $client->decodeIdToken($tokens['id_token']);
    $userinfo = $client->retrieveUserInfo($tokens['access_token']);
    $logger = \Drupal::logger('openid_connect');

    // Validate if not exist email.
    if ($userinfo && empty($userinfo['email'])) {
      $message = 'No e-mail address provided by @provider';
      $variables = ['@provider' => $client->getPluginId()];
      $logger->error($message . ' (@code @error). Details: @details', $variables);
      return FALSE;
    }

    $sub = openid_connect_extract_sub($user_data, $userinfo);
    if (empty($sub)) {
      $message = 'No "sub" found from @provider';
      $variables = ['@provider' => $client->getPluginId()];
      $logger->error($message . ' (@code @error). Details: @details', $variables);
      return FALSE;
    }

    // TODO Validación nuevo flujo.
    // Pendiente remover cuando se agregue en los otros paises.
    $newFlow = isset($_SESSION['click_login_info']['new_flow']) ? $_SESSION['click_login_info']['new_flow'] : FALSE;
    if (!$userinfo['email_verified']) {
      if (!$newFlow) {
        $_SESSION['email_verified'] = [
          'name' => $userinfo['name'],
          'email' => $userinfo['email'],
        ];
      }
      else {
        // Set new var to notification
        $_SESSION['user_not_verified'] = TRUE;
        $_SESSION['notification_verified'] = [];
      }
    }

    /* @var \Drupal\user\UserInterface $account */
    $account = $authmap->userLoadBySub($sub, $client->getPluginId());
    $gtm = \Drupal::service('selfcare_gtm');
    $evento = [];
    if (isset($_SESSION['first_login'])) {
      unset($_SESSION['first_login']);
    }

    if ($account) {
      $evento[] = [
        'event' => "tigoIdEvent",
        'category' => "Flow TigoID authentication",
        'action' => "Successful authentication",
        'label' => "New TigoID session",
      ];
      // An existing account was found. Save user claims.
      if (isset($_SESSION['guest_user'])) {
        $user = $this->invited_load_by_token($_SESSION['guest_user']);
        if (!empty($user)) {
          // Actualice data.
          openid_connect_save_userinfo($account, $userinfo);

          $active = TRUE;

          // Delete invited for token.
          $this->invited_delete_by_token($user['id']);
        }
        else {
          if (\Drupal::config('openid_connect.settings')
            ->get('always_save_userinfo')
          ) {
            openid_connect_save_userinfo($account, $userinfo);
          }
        }
        // Remove var session.
        unset($_SESSION['guest_user']);
        unset($_SESSION['mail_invited']);
        // First login active user.
        $_SESSION['first_login'] = TRUE;
      }
      else {
        if (\Drupal::config('openid_connect.settings')
          ->get('always_save_userinfo')
        ) {
          // Se comenta por el cambio en el jira 488.
          // Los datos del cliente se actualizaran en Drupal.
          if (!$newFlow) {
            openid_connect_save_userinfo($account, $userinfo);
          }
        }
      }
    }
    else {
      // Check whether there is an e-mail address conflict.
      $account = user_load_by_mail($userinfo['email']);

      // Validate is invited.
      if (isset($_SESSION['guest_user'])) {
        $user = $this->invited_load_by_token($_SESSION['guest_user']);
        if (!empty($user)) {
          if (!$account) {
            // Load user from tigoId.
            $account = user_load($user['user_id']);
          }
          // Actualice data.
          openid_connect_save_userinfo($account, $userinfo);
          $authmap->createAssociation($account, $client->getPluginId(), $sub);

          $active = TRUE;

          // Delete invited for token.
          $this->invited_delete_by_token($user['id']);
          unset($_SESSION['guest_user']);
          unset($_SESSION['mail_invited']);
          // First login active user.
          $_SESSION['first_login'] = TRUE;
        }
      }
      else {
        if ($account) {
          openid_connect_save_userinfo($account, $userinfo);
          $message = t('Tu información ha sido actualizada.');
          drupal_set_message($message);
          $authmap->createAssociation($account, $client->getPluginId(), $sub);

          // Include tigoIdEvent GTM.
          $evento[] = [
            'event' => "tigoIdEvent",
            'category' => "Flow TigoID authentication",
            'action' => "Successful authentication",
            'label' => "First TigoID session (Automigration)",
          ];
          $evento[] = [
            'event' => "tigoIdEvent",
            'category' => "Flow TigoID migration",
            'action' => "Successful migration",
            'label' => "Automigrate user with same email",
          ];
        }
        else {
          $account = openid_connect_create_user($sub, $userinfo, $client->getPluginId());
          openid_connect_save_userinfo($account, $userinfo);
          openid_connect_login_user($account);
          $authmap->createAssociation($account, $client->getPluginId(), $sub);
        }
        $evento[] = [
          'event' => "tigoIdEvent",
          'category' => "Flow TigoID authentication",
          'action' => "Successful authentication",
          'label' => "First TigoID session (New User)",
        ];

        // FIXME Validación nuevo flujo.
        $newFlow = isset($_SESSION['click_login_info']['new_flow']) ? $_SESSION['click_login_info']['new_flow'] : FALSE;
        if ($newFlow) {
          /** @var \Drupal\tbo_core\Services\AuditLogService $service */
          $service = \Drupal::service('tbo_core.audit_log_service');
          $service->loadName();
          // Se agrega log de auditoría.
          $log = AuditLogEntity::create();
          $uid = \Drupal::currentUser()->id();

          $log->set('user_names', $service->getName());
          $log->set('created', time());
          $log->set('company_segment', 'No disponible');
          $log->set('user_id', $uid);
          $log->set('company_name', $this->t('No aplica'));
          $log->set('company_document_number', $this->t('No aplica'));
          $log->set('user_role', $this->t('Sin rol'));
          $log->set('event_type', $this->t('Cuenta'));
          $log->set('description', $this->t('Usuario auto crea cuenta de usuario'));
          $log->set('details', $this->t('Usuario @user auto crea su perfil con el email @mail', [
            '@user' => $service->getName(),
            '@mail' => $account->getEmail(),
          ]));
          $log->set('old_values', 'No disponible');
          $log->set('new_values', 'No disponible');
          $log->save();
        }

        // First login active user.
        $_SESSION['first_login'] = TRUE;
      }
    }
    // Enviar eventos GTM.
    foreach ($evento as $value) {
      $gtm->push($value['event'], $value['category'], $value['action'], $value['label'], 0);
    }

    $_SESSION['tigo_id']['msisdn_verified'] = $userinfo['phone_number'];

    openid_connect_login_user($account);

    \Drupal::moduleHandler()->invokeAll('openid_connect_post_authorize', [
      $tokens,
      $account,
      $userinfo,
      $client->getPluginId(),
    ]);

    if ($active) {
      // Active user.
      $account->set('status', TRUE);
      $account->save();
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  private function errorResponse($message, $variables) {
    $type = 'error';
    $response = [
      'status' => 'error',
      'message' => [
        'error_type' => $type,
        'error_description' => strtr($message, $variables),
      ],
    ];
    $this->logger($type, $message, $variables);
    return $this->jsonResponse($response, 200);
  }

  /**
   * {@inheritdoc}
   */
  private function withoutHe() {
    $response = ['authorized' => FALSE, 'description' => 'There are not HE'];
    // user_cookie_save(['he' => 'verified']);.
    setrawcookie('he', rawurlencode("verified"), REQUEST_TIME + 60, '/');
    return $this->jsonResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  private function authenticated() {
    $response = ['authorized' => TRUE, 'description' => 'There are HE'];
    return $this->jsonResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  private function jsonResponse($message, $status = 200) {
    $response = new JsonResponse(NULL, $status);
    $response->setCallback("jsonpTigoIDHECallback");
    $response->setData($message);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  private function logger($type, $message, $variables) {
    // TODO agregar variable de configuracion para prender o apagar log de HE.
    if (TRUE) {
      $logger = \Drupal::logger('openid_connect');
      $logger->$type($message, $variables);
    }
  }

  /**
   * Delete user account given email.
   *
   * @param string $email
   *   The email.
   *
   * @return bool
   *   TRUE if exist user
   */
  private function deleteExistUserByEmail($email) {
    $account = user_load_by_mail($email);
    // Eliminar cuenta si existe el correo.
    if ($account) {
      $account->delete();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Function to change the curren user email.
   *
   * @param string $email
   *   The email.
   */
  private function updateCurrentUserEmail($email) {
    $user_entity = user_load($this->currentUser->id());
    $user_entity->setEmail($email);
    $user_entity->save();
    $this->currentUser->setAccount($user_entity);
  }

  /**
   * Fetches a object by token.
   *
   * @param string $token
   *   Token with the account's token in invitation_access_entity_field_data.
   *
   * @return object|bool
   *   A fully-loaded $user object upon successful user load or FALSE if user
   *   cannot be loaded.
   */
  public function invited_load_by_token($token) {
    $database = \Drupal::database();
    $query = $database->select('invitation_access_entity_field_data', 'invited');
    $query->fields('invited', ['id', 'user_id']);
    $query->condition('token', $token);
    return $query->execute()->fetchAssoc();
  }

  /**
   * Fetches a object by token.
   *
   * @param string $id
   *   Id with the account's token in invitation_access_entity_field_data.
   *
   * @return object|bool
   *   A fully-loaded $user object upon successful user load or FALSE if user
   *   cannot be loaded.
   */
  public function invited_delete_by_token($id) {
    $database = \Drupal::database();
    $query = $database->delete('invitation_access_entity');
    $query->condition('id', $id);
    $query->execute();

    $query2 = $database->delete('invitation_access_entity_field_data');
    $query2->condition('id', $id);
    return $query2->execute();
  }

}
