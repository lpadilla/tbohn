<?php

namespace Drupal\tbo_lines\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ConsumptionFilterRestLogic {
  
  protected $current_user;
  
  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $current_user) {
    $this->current_user = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    if (!$this->current_user->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $dates = \Drupal::service('tbo_lines.consumption_filter_service')->getInitFinalDates();
    return $dates;
  }
  
  /**
   * {@inheritdoc}
   */
  public function post($data) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    if (!$this->current_user->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $dates_config = \Drupal::config('tbo_lines.consumptions_filters');
    $amount_days = $dates_config->get('days_query');
    $end = strtotime($data['end_date']);
    $start = strtotime($data['init_date']);
    $datediff = $end - $start;
    $amount_days_query = floor($datediff / (60 * 60 * 24));
    
    if (strtotime($data['init_date']) > strtotime($data['end_date'])) {
      drupal_set_message(t('La fecha de fin no puede ser menor que la fecha de inicio'), 'error');
      return new ResourceResponse('error');
    }
    
    if($amount_days_query > $amount_days){
      drupal_set_message(t('El rango de fechas no puede ser mayor a @days días', ['@days' => $amount_days]), 'error');
      return new ResourceResponse('error');
    }
    
    //Validate filters
    if(isset($data['init_date']) && !empty($data['init_date']) && isset($data['end_date']) && !empty($data['end_date'])) {
      $case = 1;
    }
    else if( (isset($data['init_date']) || empty($data['init_date'])) && isset($data['end_date']) && !empty($data['end_date'])) {
      $case = 2;
    }
    else {
      $case = 3;
    }
    
    //Filtering information
    foreach($data['filter_data'] as $key => $value) {
      if($case == 1) {
        if(strtotime($value['date']) >= strtotime($data['init_date']) && strtotime($value['date']) <= strtotime($data['end_date'])) {
          $response[] = $value;
        }
      }
      else if($case == 2) {
        if(strtotime($value['date']) <= strtotime($data['end_date'])) {
          $response[] = $value;
        }
      }
      else {
        if(strtotime($value['date']) >= strtotime($data['init_date'])) {
          $response[] = $value;
        }
      }
    }
    
    return new ResourceResponse($response);
  }
}