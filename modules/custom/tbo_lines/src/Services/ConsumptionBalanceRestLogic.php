<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\adf_core\Base\BaseApiCache;
use Drupal\adf_core\Util\UtilMessage;
use Masterminds\HTML5\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;
use Drupal\Core\Session\AccountProxyInterface;

class ConsumptionBalanceRestLogic {
  
  protected $api;
  protected $currentUser;
  protected $tbo_config;
  
  /**
   * ConsumptionBalanceRestLogic constructor.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AccountProxyInterface $current_user) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->currentUser = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get($numberPhone) {
    $wallets_config = \Drupal::config('tbo_wallets.walletsconfig');
    $response = [];
    $result = [];
    $partitions = [];
    $sms = $data = $minutes = $saldo = 0;
    
    try {
      $params = [
        'tokens' => [
          'msisdn' => $numberPhone,
        ],
      ];
      $response = $this->api->getBillableMsisdn($params);
      $groups = $response->response->getBillableProductsByMsisdnResponse->groups;
      
      foreach ($groups as $group) {
        if (isset($group)) {
          $products = $group->products;
          if (count($products) == 1) {
            $part = '$_' . $products->partition;
            $partitions[$part] = $partitions[$part] + floatval($products->amount);
          }else {
            foreach ($products as $product) {
              if (isset($product)) {
                $part = '$_' . $product->partition;
                $partitions[$part] = $partitions[$part] + floatval($product->amount);
              }
            }
          }
        }
      }
      
      $wallets = $wallets_config->get('wallets')['table_fields'];
      
      foreach ($wallets as $wallet) {
        $id = explode('_', $wallet['id']);
        
        switch (strtolower(current($id))) {
          case 'min':
            $formula_to_eval = strtr($wallet['formula'], $partitions);
            $minutes = $minutes + eval("return " . $formula_to_eval . ";");
            break;
          
          case 'sms':
            $formula_to_eval = strtr($wallet['formula'], $partitions);
            $sms = $sms + eval("return " . $formula_to_eval . ";");
            break;
          
          case 'saldo':
            //$formula_to_eval = strtr($wallet['formula'], $partitions);
            //$saldo = $saldo + eval("return ".$formula_to_eval.";");
            break;
          
          case 'data':
            $formula_to_eval = strtr($wallet['formula'], $partitions);
            $data = $data + eval("return " . $formula_to_eval . ";");
            break;
        }
      }
      
      $result = [
        'minutes' => $minutes,
        'sms' => $sms,
        'saldo' => $saldo,
        'data' => $data / 1000000,
      ];
    }
    catch (Exception $e) {
    }
    return $result;
  }
}
