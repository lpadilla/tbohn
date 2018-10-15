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

class LineInfoRestLogic {
  
  protected $api;
  protected $currentUser;
  protected $tbo_config;
  
  /**
   * LineInfoRestLogic constructor.
   */
  public function __construct(TboConfigServiceInterface $tbo_config, TboApiClientInterface $api, AccountProxyInterface $current_user) {
    $this->tbo_config = $tbo_config;
    $this->api = $api;
    $this->currentUser = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $type_service = '';
    
    try {
      $params_type = [
        'tokens' => [
          'msisdn' => $_SESSION['serviceDetail']['address'],
        ],
      ];
      $response_type = $this->api->getLineInfoMobile($params_type)->response;
      $type_service = $response_type->GetLineInfoResponse->responseBody->PlanType->planType;
    }
    catch (Exception $e) {
    }
    return ($type_service);
  }
}
