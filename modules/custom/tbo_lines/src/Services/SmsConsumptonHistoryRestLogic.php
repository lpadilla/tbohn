<?php

namespace Drupal\tbo_lines\Services;

use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SmsConsumptonHistoryRestLogic {

	protected $api;
	protected $account;
	protected $service;
	protected $segment;

	/**
	 * SmsConsumptonHistoryRestLogic constructor.
	 * @param TboApiClientInterface $api
	 * @param AccountInterface $account
	 */
	public function __construct(TboApiClientInterface $api, AccountInterface $account) {
	  $this->api = $api;
	  $this->account = $account;
	  $this->service = \Drupal::service('tbo_core.audit_log_service');
	  \Drupal::service('adf_segment')->segmentPhpInit();
	  $this->segment = \Drupal::service('adf_segment')->getSegmentPhp();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get() {

		\Drupal::service('page_cache_kill_switch')->trigger();

		if(!$this->account->hasPermission('access content')) {
			throw new AccessDeniedHttpException();
		}

		$token_log = [
			'@user' => $this->service->getName(),
			'@line' => $_SESSION['serviceDetail']['address'],
			'@contractId' => $_SESSION['serviceDetail']['contractId'],
		];

		//Save audit log on fail
		$data_log = [
			'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
			'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
			'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
			'event_type' => t('Servicios'),
			'description' => t('Usuario consulta historial de consumos SMS'),
			'details' => t('Usuario @user consulta historial de SMS de la línea @line asociada al contrato @contractId.', $token_log),
			'old_value' => t('No aplica'),
			'new_value' => t('No aplica'),
		];

		$this->service->insertGenericLog($data_log);

		$config = \Drupal::config('tbo_lines.consumptions_filters');
		$dates = \Drupal::service('tbo_lines.consumption_filter_service')->getInitFinalDates();
		$params = [
			'query' => [
				'dateFrom' => $dates['date_ini'],
				'dateTill' => $dates['date_end'],
			],
			'tokens' => [
				'msisdn' => $_SESSION['serviceDetail']['address'],
			],
		];

		try {
			$response = $this->api->smsGprsMmsDetail($params);
			$response = $response->response->SmsGprsMmsDetailResponse->body->smsGprsMmsDetailList->detail;
		} catch(\Exception $e) {
			return new ResourceResponse(UtilMessage::getMessage($e));
		}
		$day_ini = ($config->get('init_day') == 0) ? 1 : $config->get('init_day');
		$day_end = ($config->get('end_day') == 0) ? 30: $config->get('end_day');

		$get_type_hour = \Drupal::config('tbo_general.settings')->get('region')['format_hour'];
		$format_hour = \Drupal::config('core.date_format.'.$get_type_hour)->get('pattern');

		$get_type_format = \Drupal::config('tbo_general.settings')->get('region')['format_date'];
		$format = \Drupal::config('core.date_format.'.$get_type_format)->get('pattern');

		foreach ($response as $key => $value) {
			//\Drupal::logger('dato')->notice(print_r($value, TRUE));

			if(strpos($value->messageDescription, 'SMS') !== FALSE) {

			  $get_date = new \DateTime($value->eventDateTime);

			  if($get_date->format('d') >= $day_ini && $get_date->format('d') <= $day_end) {

			  	$date_show = $get_date->format($format);
		  		$resp[] = [
		  			'date' => $get_date->format('Y-m-d'),
		   			'date_show' => $date_show,
						'date_sort' => $value->eventDateTime,
		  			'hour' => $get_date->format($format_hour),
		  			'both' => $get_date->format($format) . ' - '. $get_date->format($format_hour),
		  			'msisdn' =>  $this->_setFormat($value->msisdn),
		  			'download' => [
						  'date' => $date_show,
						  'hour' => $get_date->format($format_hour),
				  		'msisdn' =>  (empty($value->msisdn)) ? t('No disponible') : $value->msisdn,
					  ],
				  ];
			  }
		  }
		}


		return new ResourceResponse($resp);
	}

	/**
	 * @param $val, Set a number format
	 * @return string
	 */
	protected function _setFormat($val) {
		if($val == NULL) {
			$val = t('No disponible');
		}
		elseif(strlen($val) == 10 && substr($val, 0,1) == 3) {
			$val = '('.substr($val,0, 3) .') '.substr($val, 3,3) . '-' . substr($val, 6, 4);
		}

		return $val;
	}

	/**
	 * @param $data
	 * @return ResourceResponse
	 */
	public function post($data) {

		$type_file = isset ($data['type']) ? $data['type'] : 'txt';
		$type_download = isset($data['download']) ? $data['download'] : 'sms';

		try {
      $tigoId = \Drupal::service('tigoid.repository')
        ->getTigoId(\Drupal::currentUser()->id());
      
      if(isset($tigoId)) {
        $this->segment->track([
          'event' => 'TBO - Descargar reporte consumos - Tx',
          'userId' => $tigoId,
          'properties' => [
            'category' => 'Portafolio de Servicios',
            'label' => 'Telefonía móvil - ' . strtoupper($type_download) . ' - movil',
            'site' => 'NEW',
          ],
        ]);
      }
    }catch (\Exception $exception){
    }

		//creación path del archivo
		$dir = \Drupal::service('stream_wrapper_manager')
			->getViaUri('public://')
			->realpath();

		$doc_name =  "Reporte-consumo-$type_download";

		$date = date('Y-m-d');
		$file_name = $doc_name . $date .'.'. $type_file;
		$path = $dir . '/' . $file_name;

		$data_headers = $data['headers'];

		try {
			//preparación del archivo excel
			if ($type_file == 'xlsx' || $type_file == 'csv') {

				$writer = $type_file == 'xlsx' ? WriterFactory::create(Type::XLSX) : WriterFactory::create(Type::CSV);
				$writer->openToFile($path);

				if ($type_file == 'xlsx') {
					$writer->getCurrentSheet()->setName('Detalle de consumo '.$type_download);
				}

				if ($type_file == 'csv') {
					$writer->setFieldDelimiter(';');
				}

				//Preparación de filas
				$writer->addRow($data_headers);

				foreach ($data['data'] as $key => $item) {
					$writer->addRow($data['data'][$key]['download']);
				}

				if ($writer->close()) {
				}
				else {
				}
			}

			//preparación archivo de texto
			if ($type_file == 'txt') {
				$file = fopen($path, 'w');

				//Write data if export is in format txt or csv
				foreach ($data['data'] as $key => $value) {
					foreach ($data_headers as $header => $value_header) {
						fwrite($file, $value_header. "\r\n");

						fwrite($file, $data['data'][$key]['download'][$header] . "\r\n \r\n");
					}
					fwrite($file, "---------------------------------\r\n \r\n");
				}

				if (fclose($file)) {
				}
				else {
				}
			}

			$file_data = [
				'file_name' => $file_name,
			];

			$token_log = [
				'@user' => $this->service->getName(),
				'@type' => $type_file,
				'@line' => $_SESSION['serviceDetail']['address'],
				'@contractId' => $_SESSION['serviceDetail']['contractId'],
			];

			//Save audit log on fail
			$data_log = [
				'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
				'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
				'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
				'event_type' => t('Servicios'),
				'description' => t('Usuario descarga reporte de historial de consumos ' . $type_download),
				'details' => t('Usuario @user descarga reporte @type de consumos de '.$type_download.' de la línea @line asociada al contrato @contractId', $token_log),
				'old_value' => t('No aplica'),
				'new_value' => t('No aplica'),
			];

			$this->service->insertGenericLog($data_log);

			return new ResourceResponse($file_data);
		}catch (\Exception $e){
		}

	}

}
