<?php
namespace Drupal\tbo_billing_hn\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\adf_core\Util\UtilMessage;
use Drupal\tbo_billing\Services\CurrentInvoiceService;
use Drupal\tbo_billing\Services\CurrentInvoiceServiceInterface; 

/**
 * Class CurrentInvoiceHnService.
 *
 * @package Drupal\tbo_billing_hn\Services
 */
class CurrentInvoiceHnService extends CurrentInvoiceService implements CurrentInvoiceServiceInterface {


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
 // public function get(AccountProxyInterface $currentUser) {//oegi
  public function get(\Drupal\Core\Session\AccountProxyInterface $currentUser) {

    $this->currentUser = $currentUser;
    //Remove cache
    \Drupal::service('page_cache_kill_switch')->trigger();
    $uri_billing = \Drupal::request()->getRequestUri();
    $billing_type = $_GET['billing_type'];
    $billing_contract = null;
    if (isset($_GET['billing_contract'])) {
      $billing_contract = $_GET['billing_contract'];
    }
    $data = $this->getAllInvoices($billing_type, $billing_contract);
   
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
  public function getAllInvoices($billing_type, $billing_contract = null) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    //missed current language, set default as spanish
    setlocale(LC_ALL, 'es_ES');
    
    $alert = TRUE;
    $result = '';
    $status_invoice = TRUE;
    if (isset($_SESSION['company'])) {
      $clientId = $_SESSION['company']['nit'];
      $company_document = $_SESSION['company']['docType'];  
      $company_code = $_SESSION['company']['company_code'];  
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
    
    $config = \Drupal::config("tbo_billing_hn.bill_payment_settings");
    $group = "visualizacion";

    $params['query'] = [
      'clientId' => $clientId,
      'companyCode' => $company_code,
      'quantity' => $config->get($group)['serviceparam']['billingquantity'],
      'end' => date('Y-m-d'),
      'offset' => 1,
      'limit' => $config->get($group)['serviceparam']['billinglimit'],      
    ];
    $params['tokens'] = [
      'clientId' => $clientId,
      'companyCode' => $company_code,
      'quantity' => $config->get($group)['serviceparam']['billingquantity'],
      'end' => date('Y-m-d'),
      'offset' => 1,
      'limit' => $config->get($group)['serviceparam']['billinglimit'],
    ];
     
    $params['headers'] = [
      'cache-control' => 'no-cache',  
      'x-debug' => 'true',       
    ];  
      
    $account_contract = [];
    $account_contract1 = [];
    $data_filters_address = [];
    
    $account_data = [];
    $data = [];
    try {  
      $response = $this->api->getBillingInfoPropossal($params); //WS call        
     } catch (\Exception $e) {
      //return message in rest
      return new ResourceResponse(UtilMessage::getMessage($e));
    }

    //Validate quantity billingAccount to foreach
      
    if($response) {        
      $for_data = $response->billsDetails->billingGroupCollection;  
      if(count($for_data) < 1){        
        $for_data = $response->billsDetails;
      }
        
      foreach($for_data as $keyD => $infoD) {
      	$for_invoice = $infoD->billsCollection;
      }
                  
      foreach($for_invoice as $invoice) {
        $msisdn = trim($invoice->msisdn);            
        if(!empty($msisdn) ){//oculta las facturas que vienen con msisdn null
          array_push($account_contract, ['name' => $msisdn]);
          array_push($account_contract1, $msisdn);
        }     
        if(($invoice->invoiceStatus == 'PAGADO')||($invoice->invoiceStatus == 'PAGADA')){
          $status = t('PAGADA');
          $status_invoice = TRUE;
        }else{
          $status = t('SIN PAGO');
          $status_invoice = FALSE;
        }
        $date_invoice = date('Y-m', strtotime($invoice->expirationDate));
        $date_payment_format = format_date(strtotime($invoice->expirationDate), 'longfactura'); //RVS 20171129    
        $dateI = substr($invoice->billPeriod, 0, 6) . "01 00:00:00"; //RVS 20171120
            
        $period_format = strftime('%b %G', strtotime($dateI));            
        $period_format = format_date(strtotime($dateI), 'shortfactura'); //RVS 20171122  
            
        $paymentDueDate = $invoice->expirationDate;
        $paymentDueDate_exp = str_replace('/', '-', $paymentDueDate);
        $date_invoice = $this->tbo_config->formatDate(strtotime($paymentDueDate_exp));
        if (date('Y-m-d', strtotime($invoice->expirationDate)) < $current_date) {
          $alert = FALSE;
          if(($invoice->invoiceStatus != 'PAGADO')&&($invoice->invoiceStatus != 'PAGADA')){
            $status_invoice = FALSE;
          }
        }else{
          $status_invoice = TRUE;
        }
        $date_status = '';
                        
        //Generate status revision
        if ($status == 'PAGADA') {
          $date_status = 'paid';
        }
        elseif (date('Y-m-d', strtotime($invoice->expirationDate)) >= $current_date && $status == 'SIN PAGO') {
          $date_status = 'slopes';
        }
        elseif ($current_date > date('Y-m-d', strtotime($invoice->expirationDate)) && $status == 'SIN PAGO') {
          $date_status = 'overdue';
        }
        else{
        	$date_status = 'overdue';
        }
            
        $the_invoice = [
          'address' => $msisdn,
          'invoice_value' => $invoice->invoiceAmount,
          'invoice_value2' => $this->tbo_config->formatCurrency($invoice->invoiceAmount),
          'date_payment' => $invoice->expirationDate,
          'date_payment2' => $date_payment_format,
          'contract' => $invoice->contract,
          'payment_reference' => $invoice->contract,
          'period' => ucfirst($period_format),
          'status' => $status,
          'status_invoice' => $status_invoice,
          'adjustment' => FALSE,
          'company_document' => $company_document,
          'invoiceId' => $invoice->dei, //cambio solicitado RVS 20171124
          'addressActual' =>ucwords(trim($invoice->billingAddress)),
          'city' => trim($invoice->billingAddress),
          'alert' => $alert,
          'date_status' => $date_status,
          'state' => trim($invoice->billingAddress),
          'country' => trim($invoice->billingAddress),
          'zipcode' => trim($invoice->billingAddress),
          'address_show' => 1,
          'cai' => $invoice->cai,
          'msisdn' => $msisdn,
        ];
            
        if (!empty($msisdn) ){ //oculta las facturas que vienen con msisdn null
          array_push($account_data, $the_invoice);
          array_push($data_filters_address, ['name' => $msisdn]);
        }
      } 
        
      usort($account_data, function ($a1, $a2) {
        $v1 = strtotime($a1['date_payment']);
        $v2 = strtotime($a2['date_payment']);
        return $v2 - $v1; // $v1 - $v2 to reverse direction
      });
    }
    if($billing_type == 2){
      return array_unique($account_contract1);
    }
      
    $account_dataPaid = [];
    foreach($account_data as $key => $val) { //RVS 20171106 sort by overdue to the top
			if($val['date_status'] != 'overdue') {
        $itemTemp = $val;
        unset($account_data[$key]);
        array_push($account_dataPaid, $itemTemp); 
      }
		}
			
		$account_data = $this->groupInvoices($account_data, 'address'); //RVS 20171106 sort by address which in this case is the msisdn
		
		$account_dataPaid = $this->groupInvoices($account_dataPaid, 'address'); //RVS 20171106 sort by address which in this case is the msisdn
		
		foreach ($account_dataPaid as $paidItem){
			array_push($account_data, $paidItem); 
		}
		
    $account_dataTemp = $account_data; //RVS 20171106 hack para reordenar
    $account_data = array();
    foreach($account_dataTemp as $val1) {
    	$account_data[] = $val1;
    }
      
    array_push($data, $this->deleteDuplicates($account_contract, 'name'));
    
    array_push($data, $account_data);
    array_push($data, $this->deleteDuplicates($data_filters_address, 'name'));
    $result = new ResourceResponse($data);
    return $result;
  }
}