<?php

namespace Drupal\tbo_lines\Services;

class ConsumptionDatesService {
  
  protected $conf_date;
  
  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->conf_date = \Drupal::config('tbo_lines.consumptions_filters');
  }
  
  /**
   * @return array
   * init day and end day
   */
  public function getInitFinalDates() {
    
    //configuration values
    $c_days = $this->conf_date->get('days_query');
    $c_months = $this->conf_date->get('month_query');
    $c_init_day = $this->conf_date->get('init_day');
    $c_end_date = $this->conf_date->get('end_day');
    
    //Current information
    $current_end = false;
    $current_day = date('d');
    $current_month = date('m');
    $current_year = date('Y');
    
    //validate if current day is equal
    if(empty($c_end_date) || $c_end_date == 0 || $c_end_date > $current_day) {
      $end_date = $current_day;
      $current_end = true;
    }
    else {
      $end_date = $c_end_date;
    }
    
    //Set init day variable
    $init_day = ($c_init_day == 0 || empty($c_init_day)) ? 1 : $c_init_day;
    
    //Validate days range
    /*if(!empty($c_days) && $c_days != 0) {
      if(!empty($c_init_day) && $c_init_day != 0 && !empty($c_end_date) && $c_end_date != 0 ) {
        if($c_end_date - $c_init_day > $c_days) {
          $init_day = $end_date - $c_days;
        }
      }
      else if(!empty($c_init_day) && $c_init_day != 0 && !empty($c_end_date) && $c_end_date == 0) {
        if($current_day - $c_init_day > $c_days) {
          $init_day = $current_day - $c_days;
        }
        $end_date = $current_day;
      }
      else if(!empty($c_init_day) && $c_init_day == 0 && !empty($c_end_date) && $c_end_date != 0) {
        $init_day = 1;
        if($c_end_date - $c_init_day > $c_days) {
          $init_day = $end_date - $c_days;
        }
      }
    }*/
    
    //Validate range of days with months
    if($c_months != 0 && !empty($c_months)) {
      if(!empty($c_days) && $c_days != 0) {
        $range_per_month = $c_end_date - $init_day;
        $current_m = $end_date - $init_day;
        
        $d = $range_per_month * $c_months;
        
        $month = date('m', strtotime("-$c_months months"));
        
        // if($d < $c_days - $current_m) {
        $year = date('Y', strtotime("-$c_months months"));
        $date_ini = $year.'-'.$month.'-'.$c_init_day;
        $date_ini = date('Y-m-d', strtotime($date_ini));
        //}
        
        /*if($d >= $c_days - $current_m) {
          
          $val_months = 0;
          $days_acum = 0;
          
          $val_days = ($current_end == true) ? ($c_days - $current_m) : $c_days;
          
          while($days_acum <= $val_days) {
            $days_acum += $range_per_month;
            $val_months++;
          }
        
          $c_months = $val_months;
          $days = $c_days - $current_m;
          
          if ($days > $range_per_month) {
            $days -= ($range_per_month * ($c_months - 1));
          }
          
          $new_init_day = $c_end_date - $days;
          $year = date('Y', strtotime("-$c_months months"));
          $month = date('m', strtotime("-$c_months months"));
          $date_ini = $year.'-'.$month.'-'.$new_init_day;
          $date_ini = date('Y-m-d', strtotime($date_ini));
        }*/
      }
      else {
        $year = date('Y', strtotime("-$c_months months"));
        $month = date('m', strtotime("-$c_months months"));
        $date_ini = date('Y-m-d', strtotime("$year-$month-$c_init_day"));
      }
      
      
    } else {
      $date_ini = date('Y-m-d', strtotime("$current_year-$current_month-$init_day"));
    }
    
    $date_end = date('Y-m-d', strtotime("$current_year-$current_month-$end_date"));
  
    $dates_block = [];
    $firts_day = $this->conf_date->get('init_day') == 0 ? 1 : $this->conf_date->get('init_day');
    $last_day = $this->conf_date->get('end_day') == 0 ? intval(date('d')) : $this->conf_date->get('end_day');
    $date_ini_block = strtotime($date_ini);
    $date_end_block = strtotime($date_end);
    
    for($i = $date_ini_block; $i <= $date_end_block; $i += 86400){
      
      if(intval(date('d',$i)) <= $last_day && intval(date('d',$i)) >= $firts_day ){
        $dates_block[] = date("Y-m-d", $i);
      }
    }
    
    return $dates = [
      'date_ini' => $date_ini,
      'date_end' => $date_end,
      'dates_bloqued' => array_values($dates_block),
      'amount_days' => $this->conf_date->get('days_query'),
    ];
  }
}