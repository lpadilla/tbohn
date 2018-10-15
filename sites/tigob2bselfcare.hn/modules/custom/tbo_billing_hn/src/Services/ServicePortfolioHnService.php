<?php

namespace Drupal\tbo_billing_hn\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_billing\Services\ServicePortfolioService;
use Drupal\tbo_billing\Services\ServicePortfolioServiceInterface;

/**
 * Class ServicePortfolioService.
 *
 * @package Drupal\tbo_billing_hn\Services
 */
class ServicePortfolioHnService extends ServicePortfolioService implements ServicePortfolioServiceInterface {

  /**
   * ServicePortfolioService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
      $this->tbo_config = $tbo_config;
      $this->api = $api;
      $service = \Drupal::service('adf_segment');
      $service->segmentPhpInit();
      $this->segment = $service->getSegmentPhp();
  }

    /**
     * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
     * @return ResourceResponse
     */
    public function get(\Drupal\Core\Session\AccountProxyInterface $currentUser) {
        $this->currentUser = $currentUser;
        //Remove cache
        \Drupal::service('page_cache_kill_switch')->trigger();

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        //Get client data
        $company_code = isset($_SESSION['company']['company_code']) ? $_SESSION['company']['company_code'] : '';
          //$company_code= 38;
        // Parameters for service
        $params['tokens'] = [
            'companyCode' => $company_code,
        ];


        $params['query'] = [
            'companyCode' => $company_code,
            'offset' => '1',
            'limit' => '150',
        ];

      

        try {
            $data = $this->api->getCustomerProductsById($params); //WS call
        } catch (\Exception $e) {

            //Send exception to segment
            $segment_data = json_decode($e->getMessage(), TRUE);
            $uid = \Drupal::service('tigoid.repository')->getTigoId($this->currentUser->id());
            $this->segment->track([
                'event' => 'TBO - Excepción',
                'userId' => $uid,
                'properties' => [
                    'category' => 'Portafolio de Servicios',
                    'label' => 'Error ' . $segment_data['error']['code'] . ': ' . $segment_data['error']['message'],
                ],
            ]);

            //return message in rest
            return new ResourceResponse(UtilMessage::getMessage($e));
        }



        if ( ( isset($data) ) && ( $data !== false ) )
        {
            $response = [];
            $counter = 1;
            foreach ($data as $dato)
            {
                $contadorInterno = 0;
                foreach ($dato as $data_key => $data_value)
                {
                    $Estatus = 'Activo';
                    $Mensaje = 'Servicio Activo';                  
                    if ( isset($data_value->planCode) )
                    {
                        $code = " - Plan: ".$data_value->planCode;
                    }
                    else
                    {
                        $code = "";
                    }
                    $response[$data_value->contract][] = [
                        'customerCode' => $data_value->customerCode,
                        'msisdn' => $data_value->msisdn,
                        'anexed' => $data_value->anexed,
                        'contract' =>$data_value->contract,
                        'customerName' => $data_value->customerName,
                        'address' => $data_value->address,
                        'planCode' => $data_value->planCode,
                        'service_plan' => $data_value->planDescription.$code,
                        'plan_valor' => $data_value->planValue,
                        'category_name' => 'Telefonía móvil',
                        'service_status2' => $Estatus,
                        'service_status' => $Mensaje
                        ];
                    $contadorInterno++;
                }
                $counter++;
            }
        }
        else
        {
            $response['error'] = TRUE;
            $response['message'] = 'En este momento no podemos obtener la informaci&oacute;n de tus servicios, por favor intenta de nuevo';
        }
        return new ResourceResponse($response);

    }

    /**
     * Responds to POST requests.
     * calls create method
     * @param $params
     * @return \Drupal\rest\ResourceResponse
     */
    public function post(\Drupal\Core\Session\AccountProxyInterface $currentUser, $params) {
        $this->currentUser = $currentUser;
        \Drupal::service('page_cache_kill_switch')->trigger();

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        $service = \Drupal::service('tbo_core.audit_log_service');
        $service->loadName();

        $response = [];     


        if (isset($params['exactSearch']) && isset($params['category']  ) ){
            if($params['exactSearch'] == ''){
                $detalles = t('Usuario ' . $service->getName() . ' hace consulta  en el portafolio de servicios asociados a su empresa '  );
            }else{
                $detalles = t('Usuario ' . $service->getName() . ' hace consulta detallada en el portafolio de servicios asociados a su empresa con los siguientes datos: por ' .  implode(', ', $params['exactSearch']) );
            }
        }

        //Create array data[]
        $data = [
            'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
            'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
            'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
            'event_type' => t('Servicios'),
            'description' => t('Usuario hace consulta especifica en portafolio de servicios'),
            'details' => $detalles ,
            'old_value' => 'No aplica',
            'new_value' => 'No aplica',
        ];

        //Save audit log
        $service->insertGenericLog($data);

        return new ResourceResponse('Ok');
    }
}
