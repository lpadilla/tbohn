<?php

namespace Drupal\tbo_lines\Services;


class CallHistoryDateService {

  protected $config_date;

  protected $range_days;

  public function __construct() {
    $this->config_date = $dates = \Drupal::service('tbo_lines.consumption_filter_service')
      ->getInitFinalDates();
    $this->range_days = $this->config_date['amount_days'];
  }

  /**
   * Get filter range date
   *
   * @return array
   */
  public function getFilterDate() {
    $end_date = $this->config_date['date_end'];
    $interval=$this->range_days-1;
    $init_date = date("Y-m-d", strtotime("$end_date -$interval day"));
    return ['init_date' => $init_date, 'end_date' => $end_date];

  }

  /**
   * Validate range date
   *
   * @param $init_date
   * @param $end_date
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   */
  public function validateRangeDate($init_date, $end_date) {
    $date_f = new \DateTime($end_date);
    $date_i = new \DateTime($init_date);
    $dif = $date_f->diff($date_i)->days;
    $error = "";
    if (strtotime($init_date) > strtotime($end_date)) {
      $error = t('La fecha de fin no puede ser menor que la fecha de inicio');
    }
    elseif ($dif > $this->range_days) {
      $error = t('El rango de fechas no puede ser mayor a @days días', ['@days' => $this->range_days]);
    }
    return $error;
  }


}