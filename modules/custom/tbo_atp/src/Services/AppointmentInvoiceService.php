<?php

namespace Drupal\tbo_atp\Services;

class AppointmentInvoiceService {

  /**
   * @param $charges
   * Load accountDetailsByCycle response and add new's charges (property typeCharge)
   */
  public function loadNewCharges($charges) {
    $config = \Drupal::configFactory()->getEditable('tbo_atp.config');

    //Get charge
    $load_charges = $config->get('atp_translate')['charges'];
    $new_charges = [];
    foreach ($charges as $key => $item) {
      if(!array_key_exists($item->typeCharge, $load_charges) && !array_key_exists($new_charges, $load_charges) && !empty($item->typeCharge)) {
        $new_charges[$item->typeCharge]['base']['title'] = $item->typeCharge;
        $new_charges[$item->typeCharge]['translation']['label'] = $item->typeCharge;
      }
    }

    if(!empty($new_charges)) {
      if(!empty($load_charges)) {
        $load_charges = array_merge($load_charges, $new_charges);

      }
      else {
        $load_charges = $new_charges;
      }
			$set_data['charges'] = $load_charges;

      $config->set('atp_translate', $set_data)
        ->save();
    }
  }

  /**
   * @param $text
   * @return text Traduction of configuration for charges
   */
  public function getTranslation($text) {

    $charges = \Drupal::config('tbo_atp.config')->get('atp_translate')['charges'];
    $text = $charges[$text]['translation']['label'];
    return $text;

  }

}
