<?php

namespace Drupal\tbo_lines\Services;

use Drupal\rest\ResourceResponse;
use Drupal\tbo_general\Services\TboConfigServiceInterface;
use Drupal\tbo_api\TboApiClientInterface;

/**
 * Class ValidateAndMaskCurrencyService.
 */
class ValidateAndMaskCurrencyService {

	protected $tbo_config;
	protected $api;

  /**
   * Constructs a new ValidateAndMaskCurrencyService object.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
  }

  public function get($currencyInNum) {
    if(is_numeric($currencyInNum)) {
      $resp['currency'] = $this->tbo_config->formatCurrency($currencyInNum);
    }
    else {
      if($currencyInNum=='all') {
        $resp['1'] = $this->tbo_config->formatCurrency(1);
        $resp['2'] = $this->tbo_config->formatCurrency(12);
        $resp['3'] = $this->tbo_config->formatCurrency(123);
        $resp['4'] = $this->tbo_config->formatCurrency(1234);
        $resp['5'] = $this->tbo_config->formatCurrency(12345);
        $resp['6'] = $this->tbo_config->formatCurrency(123456);
        $resp['7'] = $this->tbo_config->formatCurrency(1234567);
        $resp['8'] = $this->tbo_config->formatCurrency(12345678);
        $resp['9'] = $this->tbo_config->formatCurrency(123456789);
      }
      else {
        $resp['currency'] = $currencyInNum;
      }
    }
    
    return new ResourceResponse($resp);
  }
}