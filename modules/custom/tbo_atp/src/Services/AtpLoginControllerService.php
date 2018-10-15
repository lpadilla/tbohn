<?php

namespace Drupal\tbo_atp\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\user\Entity\User;

class AtpLoginControllerService {

	protected $api;
	protected $current_user;
	protected $segment;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(TboApiClientInterface $api, AccountProxyInterface $currentUser) {
		$this->api = $api;
		$this->current_user = $currentUser;
	}

	/**
	 * @return TrustedRedirectResponse|RedirectResponse
	 * Redirect to atp page or url configured
	 */
	public function validateAtp() {

    // Remove cache.
    \Drupal::service('page_cache_kill_switch')->trigger();

		// Set service parameters.
		$params['query']['docId'] = $_SESSION['company']['nit'];
		// Get ATP configuration.
    $service = \Drupal::config('tbo_atp.config');
		$url_to_redirect = 'internal:' . $service->get('atp_config')['atp_url'];

		try {
			$resp = $this->api->getATPAccountsById($params);
      // Redirect to atp page if user have atp account.
			return new RedirectResponse(Url::fromUri($url_to_redirect)->toString());
		} catch(\Exception $e) {
			// Get type of redirect internal or external URL.
			$type_redirect = $service->get('apt_config')['type_redirect'];
			$message = UtilMessage::getMessage($e);

			// Validate error code and set message and redirect.
      if($message['code'] != 404) {
				drupal_set_message(t('Ha ocurrido un error, por favor  intente  de  nuevo  más  tarde'), 'error');
			}
			else {
        $_SESSION['atp_services'] = 'no_atp_aviable';

      	$log_service = \Drupal::service('tbo_core.audit_log_service');

				$account = User::load($this->current_user->id());
				$full_name = $account->get('full_name');
				$user = (empty($full_name) || isset($full_name)) ? $this->current_user->getAccountName() : $full_name;

				$token_log = [
					'@user' => $user,
				];

				// Save audit user without ATP services.
				$data_log = [
					'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
					'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
					'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
					'event_type' => t('ATP'),
					'description' => t('Usuario consultó página informativa de planes corporativos (ATP).'),
					'details' => t('Usuario @user consulto página informativa cuando no tiene servicios ATP contratados a su emrpesa', $token_log),
					'old_value' => t('No disponible'),
					'new_value' => t('No disponible'),
				];
				$log_service->insertGenericLog($data_log);

				$url_to_redirect = $service->get('atp_config')['url_redirect'];
			}

      if($type_redirect == 'internal') {
				return new RedirectResponse(Url::fromUri('internal:' . $url_to_redirect)->toString());
			}
			else {
        return new TrustedRedirectResponse($url_to_redirect, 302);
			}
		}

	}

}
