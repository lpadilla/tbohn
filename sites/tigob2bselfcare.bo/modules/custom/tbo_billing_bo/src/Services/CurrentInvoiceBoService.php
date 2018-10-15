<?php
namespace Drupal\tbo_billing_bo\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_billing\Services\CurrentInvoiceService;
use Drupal\tbo_billing\Services\CurrentInvoiceServiceInterface; 

/**
 * Class CurrentInvoiceBoService.
 *
 * @package Drupal\tbo_billing_bo\Services
 */
class CurrentInvoiceBoService extends CurrentInvoiceService implements CurrentInvoiceServiceInterface {
  /*
  private $api;
  private $currentUser;
  private $tbo_config;
*/

	protected $api;
  protected $currentUser;
  protected $tbo_config;

  /**
   * CurrentInvoiceService constructor.
   * @param \Drupal\tbo_general\Services\TboConfigServiceInterface $tbo_config
   * @param \Drupal\tbo_api\TboApiClientInterface $api
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }


  /**
   * @param AccountProxyInterface $currentUser
   * @return ResourceResponse
   */
   
  public function get(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();
    $uri_billing = \Drupal::request()->getRequestUri();
    $billing_type = $_GET['billing_type'];
    $billing_contract = null;
    if (isset($_GET['billing_contract'])) {
      $billing_contract = $_GET['billing_contract'];
    }

    $client_code = $_GET['client']; //client_code a enviar al servicio
    $status_sent     =  $_GET['estatus'];
    $contracts_borde =  $_GET['contracts_borde']; #arreglo de contratos borde
    $contract_borde  =  $_GET['contract_borde'];  # contrato borde en especifico

    $data = $this->getAllInvoices($billing_type, $billing_contract, $client_code,$status_sent,$contracts_borde,$contract_borde);
    
    //Save audit log
    $service = \Drupal::service('tbo_core.audit_log_service');
    $service->loadName();
    //Create array data_log[]
    $data_log = [
      'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
      'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
      'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
      'event_type' => 'Facturación',
      'description' => t('Usuario consulta listado de factutas vigentes'),
      'details' => 'Usuario ' . $service->getName() . ' consultó listado de facturas vigentes',
    ];
    //Save audit log
    $service->insertGenericLog($data_log);
    return $data;
  }
  

  /**
   * @param $billing_type
   * @param $billing_contract
   * @return \Drupal\rest\ResourceResponse|string
   */
  public function getAllInvoices($billing_type, $billing_contract = null, $client_code,$status_sent,$contracts_borde=null,$contract_borde=null) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    //missed current language, set default as spanish
    setlocale(LC_ALL, 'es_ES');
    
    $alert = TRUE;
    $result = '';
    $status_invoice = TRUE;
    if (isset($_SESSION['company'])) {
      $clientId = $_SESSION['company']['nit'];
      $company_document = $_SESSION['company']['docType'];  
      $client_code = $client_code;
    }
    else {
      $result = "no se ha seleccionado ninguna empresa";
    }
        
    $current_date = date('Y-m-d');
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $max_date_page = date('Y-m-d', strtotime(date('Y-m-d') . ' - 10 days'));
    $pastYear = $year - 1;
    $nextYear = $year + 1;
    
    $config = \Drupal::config("tbo_billing_bo.bill_payment_settings");
    $group = "visualizacion";

    # Condicion para setear el quantity y la cantidad de contratos a mostrar($def_quantity)
    # en ejecucion de sericios por status
    if($status_sent=="CA"){
        $quantity=$config->get($group)['serviceparam']['billingca'];
        $def_quantity=$config->get($group)['serviceparam']['billingca_cut'];
    }else{
      $quantity=$config->get($group)['serviceparam']['billingpc'];
      $def_quantity=$config->get($group)['serviceparam']['billingpc_cut'];
    }

    # condicion para pasar el quantity al sevicio por contract number en casos borde 
    if($billing_contract!=null){
      $def_quantity=200;
    }
    


    # Parametros a enviar al servicio para su ejecucion, dependiendo de los casos a considerar
    if($billing_contract!=null){
      # Cuando se pasa el numero de contrato, viene siendo el caso de detalle de factura      
      $params['query'] = [
        'contractNumber' => $billing_contract,
        'clientruc' => $clientId, 
        'offset' => $config->get($group)['serviceparam']['billingoffset'],
        'limit' => $config->get($group)['serviceparam']['billinglimit'],
        
      ];

      $params['tokens'] = [
        'contractNumber' => $billing_contract, 
        'clientruc' => $clientId, 
        'offset' => $config->get($group)['serviceparam']['billingoffset'],
        'limit' => $config->get($group)['serviceparam']['billinglimit'],
        
      ];
    }else{

      if($contract_borde!=null){ # consulta casos borde por num contrato
        $params['query'] = [
          'contractNumber' => $contract_borde,
          'clientruc' => $clientId,
          'offset' => $config->get($group)['serviceparam']['billingoffset'],
          'limit' => $config->get($group)['serviceparam']['billinglimit'],
        ];

        $params['tokens'] = [
          'contractNumber' => $contract_borde, 
          'clientruc' => $clientId,
          'offset' => $config->get($group)['serviceparam']['billingoffset'],
          'limit' => $config->get($group)['serviceparam']['billinglimit'],
        ];
      }else{
        
        if($quantity==0 ){
           # Caso con quantity en 0, trae todos los contratos por su estatus
          $params['query'] = [
            'clientruc' => $clientId, 
            'offset' => $config->get($group)['serviceparam']['billingoffset'],
            'limit' => $config->get($group)['serviceparam']['billinglimit'],
            'status'=> $status_sent,
          ];

          $params['tokens'] = [
            'clientruc' => $clientId, 
            'offset' => $config->get($group)['serviceparam']['billingoffset'],
            'limit' => $config->get($group)['serviceparam']['billinglimit'],
            'status'=> $status_sent,
          ];

        }else{
           # Caso normal de consulta para mostrar la lista de contratos pendientes y cancelados
          $params['query'] = [
            'clientruc' => $clientId, 
            'offset' => $config->get($group)['serviceparam']['billingoffset'],
            'limit' => $config->get($group)['serviceparam']['billinglimit'],
            'quantity'=> $quantity,
            'status'=> $status_sent,
          ];

          $params['tokens'] = [
            'clientruc' => $clientId, 
            'offset' => $config->get($group)['serviceparam']['billingoffset'],
            'limit' => $config->get($group)['serviceparam']['billinglimit'],
            'quantity'=> $quantity,
            'status'=> $status_sent,
          ];
        }

       
      }
    }

    $account_contract = [];
    $data_filters_address = [];

    $account_data = [];
    $data = [];
    $contracts_old     =[]; #arreglo para setear contratos que  serian borde
    $contador_contracts=0; # variable para recorrer la cantidad de contratos por estatus a mostrar

    
    # llamadas a los servicios
    try {
      if($billing_contract!=null){
        $response = $this->api->findCustomerBillsByRucWithContractNumber($params);
      }else{
        if($contract_borde!=null){
          $response = $this->api->findCustomerBillsByRucWithContractNumber($params);
        }else{
          if($quantity==0){
            $response = $this->api->findCustomerBillsByRucNoLimit($params);
          }else{
            $response = $this->api->findCustomerBillsByRuc($params);
          }
          
        }
      }      
    } catch (\Exception $e) {
        //return message in rest
        return new ResourceResponse(UtilMessage::getMessage($e));
    }

    if($response){
      if(is_array($response)){        
        $account_data[0]="morecontract";
        foreach ($response as $resp1) {
          
          # si no existen casos borde,  continua  ejecucion normal, seteando valores a mostrar como respuesta del servicio
          if(($resp1->unpaidInvoiceCount!=0 && $status_sent=="PC") || ( $status_sent=="CA")){
            if (!in_array($resp1->billingAccountId,$contracts_borde) || !empty($contract_borde) ){
              $for_data = $resp1->invoicesCollection;
                 
              if(empty($for_data)){ # setea contratos borde

                array_push($contracts_old,$resp1->billingAccountId);
                $_SESSION['casos_borde']=$resp1->billingAccountId;
              }else{
                $contador_contracts=0; 
                foreach ($for_data as $invoice){      
                  if($contador_contracts<$def_quantity){
                  $contador_contracts++;      
                    $msisdn = '000000';
                    if ($invoice->invoiceStatus == 'CA'){
                        $status = t('PAGADA'); 
                        $status_invoice = TRUE;
                    }
                    else{
                        $status = t('SIN PAGO');
                        $status_invoice = FALSE;
                    }
                    
                    $dateI = substr($invoice->billPeriod, 0, 10) . " 00:00:00";

                    $period_format = strftime('%b %G', strtotime($dateI));

                    $period_format = format_date(strtotime($dateI), 'shortfactura');
                    if (date('Y-m-d', strtotime($invoice->expirationDate)) < $current_date){
                      $alert = FALSE;
                      if ($invoice->invoiceStatus != 'CA'){
                        $status_invoice = FALSE;
                      }
                    }
                    else{
                      $status_invoice = TRUE;
                    }
                    $date_status = '';

                    if($invoice->expirationDate != '-'){              
                      $date_payment_format = format_date(strtotime(substr($invoice->expirationDate, 0, 10)), 'longfactura');
                    }else{
                      $date_payment_format = $invoice->expirationDate;
                    }
                      
                    //Generate status revision
                    if ($status == 'PAGADA'){
                      $date_status = 'paid';
                    }
                    elseif (date('Y-m-d', strtotime($invoice->expirationDate)) >= $current_date && $status == 'SIN PAGO'){
                      $date_status = 'slopes';
                    }
                    elseif ($current_date > date('Y-m-d', strtotime($invoice->expirationDate)) && $status == 'SIN PAGO'){
                      $date_status = 'overdue';
                    }
                    else{
                      $date_status = 'overdue';
                    }  
                    #valores a devolver en el arreglo que leera angular o js
                    $the_invoice[] = [
                      'address_show' => 1,
                      'cai' => $invoice->cai,
                      'msisdn' => $msisdn,
                      'status' => $status,
                      'status_invoice' => $status_invoice,
                      'period' => ucfirst($period_format),
                      'zipcode' => $invoice->billingAddress->zipCode,
                      'country' => $invoice->billingAddress->country,
                      'state' => $invoice->billingAddress->state,
                      'date_status' => $date_status,
                      'alert' => $alert,
                      'city' => $invoice->billingAddress->city,
                      'addressActual' =>ucwords( $invoice->billingAddress),
                      'invoiceId' => $invoice->invoiceNumber,
                      'company_document' => $company_document,
                      'adjustment' => FALSE,
                      'contract' => $invoice->contract,
                      'payment_reference' => $invoice->invoiceNumber,
                      'date_payment'=>$invoice->expirationDate,                      
                      'date_payment2'=>$date_payment_format,
                      'invoice_value' => $invoice->invoiceAmount,
                      'invoice_value2' => $this->tbo_config->formatCurrency($invoice->invoiceAmount),
                      'client_code' => $invoice->client->id,
                    ];
                  }
                }
              }

            }
            
          }
          if(!in_array($resp1->billingAccountId,$contracts_old) && !empty($for_data) && !empty($resp1->invoicesCollection) )
            $invoice_for_contract[] = $the_invoice;

          

        }        
        array_push($account_data, $invoice_for_contract);
        $account_data[2]=$contracts_old; 
        $result = new ResourceResponse($account_data);   
        return $result;
      }else{
        # Un solo contrato  que trae el servicio
        $for_data = $response->invoicesCollection;
          
          if (!in_array($response->billingAccountId,$contracts_borde) || !empty($contract_borde) ){
            if(empty($for_data)){ # setea contratos borde
              
                array_push($contracts_old,$response->billingAccountId);
                
                array_push($data_filters_address, ['name' => $contracts_old]);
                
            }else{
              $contador_contracts=0;
              foreach ($for_data as $invoice){
                if($contador_contracts<$def_quantity){
                  $contador_contracts++;    
                  $msisdn = '000000';
                  $direccionFactura = $invoice->billingAddress->street;
                  array_push($account_contract, ['name' => $direccionFactura]);

                  if ($invoice->invoiceStatus == 'CA'){
                      $status = t('PAGADA');
                      $status_invoice = TRUE;
                  }
                  else{
                      $status = t('SIN PAGO');
                      $status_invoice = FALSE;
                  }
                  
                  if($invoice->expirationDate != '-'){              
                    $date_payment_format = format_date(strtotime(substr($invoice->expirationDate, 0, 10)), 'longfactura');
                  }else{
                    $date_payment_format = $invoice->expirationDate;
                  }

                  if($invoice->billPeriod!=null){
                  $dateI = substr($invoice->billPeriod, 0, 10) . " 00:00:00";

                  $period_format = strftime('%b %G', strtotime($dateI));

                  $period_format = format_date(strtotime($dateI), 'shortfactura'); 
                  }else{
                    $period_format = "-";
                  }
                 
                  $paymentDueDate = $invoice->expirationDate;
                  $paymentDueDate_exp = str_replace('/', '-', $paymentDueDate);
                 

                  if (date('Y-m-d', strtotime($invoice->expirationDate)) < $current_date){
                    $alert = FALSE;
                    
                    if ($invoice->invoiceStatus != 'CA'){
                      $status_invoice = FALSE;
                    }
                  }
                  else{
                    $status_invoice = TRUE;
                  }
                  $date_status = '';
                  
                  //Generate status revision
                  if ($status == 'PAGADA'){
                    $date_status = 'paid';
                  }
                  elseif (date('Y-m-d', strtotime($invoice->expirationDate)) >= $current_date && $status == 'SIN PAGO'){
                    $date_status = 'slopes';
                  }
                  elseif ($current_date > date('Y-m-d', strtotime($invoice->expirationDate)) && $status == 'SIN PAGO'){
                    $date_status = 'overdue';
                  }
                  else{
                  	$date_status = 'overdue';
                  }
                  
                  $the_invoice = [
                    'address' => $direccionFactura, 
                    'invoice_value' => $invoice->invoiceAmount,
                    'invoice_value2' => $this->tbo_config->formatCurrency($invoice->invoiceAmount),
                    'date_payment' => $invoice->expirationDate,
                    'date_payment2' => $date_payment_format,
                    'contract' => $invoice->contract,
                    'payment_reference' => $invoice->invoiceNumber,
                    'period' => ucfirst($period_format),
                    'status' => $status,
                    'status_invoice' => $status_invoice,
                    'adjustment' => FALSE,
                    'company_document' => $company_document,
                    'invoiceId' => $invoice->invoiceNumber,
                    'addressActual' =>ucwords( $invoice->billingAddress),
                    'city' => $invoice->billingAddress->city,
                    'alert' => $alert,
                    'date_status' => $date_status,
                    'state' => $invoice->billingAddress->state,
                    'country' => $invoice->billingAddress->country,
                    'zipcode' => $invoice->billingAddress->zipCode,
                    'address_show' => 1,
                    'cai' => $invoice->cai,
                    'msisdn' => $msisdn,
                    'client_code' => $invoice->client->id,
                  ];

                  array_push($account_data, $the_invoice);
                  array_push($data_filters_address, ['name' => $direccionFactura]);
                } 
              }
            }
          }

      }        
      usort($account_data, function ($a1, $a2) {
        $v1 = strtotime($a1['date_payment']);
        $v2 = strtotime($a2['date_payment']);
        return $v2 - $v1; 
      });
    }
    

    
  	$account_dataPaid = [];      
		foreach($account_data as $key => $val) { 
        if($val['date_status'] != 'overdue') {
		        $itemTemp = $val;
		        unset($account_data[$key]);
		        array_push($account_dataPaid, $itemTemp); 
		    }
		}
 
  	$account_data = $this->groupInvoices($account_data, 'address'); 
			
		$account_dataPaid = $this->groupInvoices($account_dataPaid, 'address'); 
		
		foreach ($account_dataPaid as $paidItem){
			array_push($account_data, $paidItem); 
		}
    
      
    $account_dataTemp = $account_data; 
    $account_data = array();
		foreach($account_dataTemp as $val1){

        $account_data[] = $val1;
    }


    #posicion 2 del arreglo a devolver
		$account_contract = array(); 
    array_push($data, $this->deleteDuplicates($account_contract, 'name'));
    
    array_push($data, $account_data);
    if(!is_array($response) && !empty($contracts_old))
      array_push($data, $contracts_old);
    else
      array_push($data, $this->deleteDuplicates($data_filters_address, 'name'));
    
   
    $result = new ResourceResponse($data);
   
    return $result;
  }


}