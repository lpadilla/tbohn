<?php

namespace Drupal\tbo_lines\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SmsBalanceRestLogic {
	
	protected $api;
	protected $account;
	
	/**
	 * {@inheritdoc}
	 */
	public function __construct(TboApiClientInterface $api, AccountInterface $account) {
		$this->api = $api;
		$this->account = $account;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function get() {
		\Drupal::service('page_cache_kill_switch')->trigger();
		
		if (!$this->account->hasPermission('access content')) {
			throw new AccessDeniedHttpException();
		}

		if(isset($_GET['val'])) {
			$resp['environment'] = (isset($_SESSION['serviceDetail']['serviceType'])) ? $_SESSION['serviceDetail']['serviceType'] : 'fijo';
		}
		else {
			$service = \Drupal::service('tbo_core.audit_log_service');

			$token_log = [
				'@user' => $service->getName(),
				'@line' => $_SESSION['serviceDetail']['address'],
				'@contractId' => $_SESSION['serviceDetail']['contractId'],
			];

			//Save audit log on fail
			$data_log = [
				'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
				'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
				'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
				'event_type' => t('Servicios'),
				'description' => t('Usuario consulto detalle de consumos SMS'),
				'details' => t('Usuario @user consulta detalle de SMS de la línea @line asociada al contrato @contractId.', $token_log),
				'old_value' => t('No aplica'),
				'new_value' => t('No aplica'),
			];

			//Insert audit log
			$service->insertGenericLog($data_log);

			$prefix_contry = \Drupal::config('adf_rest_api.settings')->get('prefix_country');
			$params = [
				'tokens' => [
					'msisdn' => $prefix_contry . $_SESSION['serviceDetail']['address'],
				],
				'query' => [
					'grouped' => '1',
				],
			];

			try {
				$response = $this->api->tolGetBalances($params);
			} catch(\Exception $e) {
				return new ResourceResponse(UtilMessage::getMessage($e));
			}

			foreach($response->balances as $key => $value) {

				if($value->category == 'SMS') {

					if($value->wallet == 'Mensajes a Tigo') {
						$id = 'smsTigo';
					} else if($value->wallet == 'Mensajes a Todo Destino') {
						$id = 'smsDestiantion';
					} else {
						$id = 'smsOperator';
					}

					$resp[$id] = $value->balanceAmount . "  SMS";
				}
			}
		}
		
		return new ResourceResponse($resp);
	}
	
}