<?php

namespace Drupal\tbo_atp\Services;

use Drupal\tbo_api\TboApiClientInterface;
use Drupal\adf_core\Util\UtilMessage;

class AtpGeneralService {

  protected $api;

  /**
   * AtpGeneralService constructor.
   * @param TboApiClientInterface $api
   */
  public function __construct(TboApiClientInterface $api) {
    $this->api = $api;
  }

  /**
   * @param int $cycle
   * @param string $type
   * @return string
   *
   * Return a formated date with the given $cycle parameter.
   */
  public function getFormattedBillingCycle ($cycle, $type) {
    $formattedBillingCycle = '';
    $day = \Drupal::service('date.formatter')->format(time(), 'custom', 'd');

    switch ( $type )
    {
      case 'card':
        $dateFormat = '{}/M/Y';
        break;

      case 'inside':
        $dateFormat = 'Y-m-{}';
        break;

      default:
        return t('No disponible');
    }

    if ( $day < $cycle ) {
      $time = strtotime('-1 month',time());
      $billingCycle = \Drupal::service('date.formatter')->format($time, 'custom', $dateFormat);
    }
    else {
      $billingCycle = \Drupal::service('date.formatter')->format(time(), 'custom', $dateFormat);
    }

    $formattedBillingCycle = str_replace('{}', $cycle, $billingCycle);
    return $formattedBillingCycle;
  }

  /**
   * @return string state of atp services
   */
  public function validateAtpServices() {

    if(empty($_SESSION['atp_services'])) {

      $params['query']['docId'] = $_SESSION['company']['nit'];

      try {
        $this->api->getATPAccountsById($params);
        unset($_SESSION['atp_services']);
      }
      catch (\Exception $e) {
        $message = UtilMessage::getMessage($e);
        if($message['code'] == 404) {
          $_SESSION['atp_services'] = 'no_atp_aviable';
          return $_SESSION['atp_services'];
        }
      }

    }
    else {
      return $_SESSION['atp_services'];
    }
  }

}
