<?php

namespace Drupal\tbo_entities\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;

/**
 * Class EntitiesService.
 *
 * @package Drupal\tbo_entities\Services
 */
class EntitiesService implements EntitiesServiceInterface {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Get the document types list.
   *
   * @return array
   *   List of document types.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getDocumentTypes() {
    $entityQuery = \Drupal::entityQuery('document_type_entity');
    $entities_ids = $entityQuery->execute();
    $document_types = \Drupal::entityTypeManager()
      ->getStorage('document_type_entity')
      ->loadMultiple($entities_ids);
    $doctypes = [];
    $doctype = [];
    foreach ($document_types as $key => $value) {
      $doctype['id'] = $value->id();
      $doctype['label'] = $value->label();
      // $doctype['abbreviated_label'] = $value->getAbbDocType();
      array_push($doctypes, $doctype);
      unset($doctype);
    }

    return $doctypes;
  }

  /**
   * Get invitations popup.
   *
   * @return array
   *   Invitations list.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getInvitationsPopup() {
    $entityQuery = \Drupal::entityQuery('invitation_popup_entity');
    $entities_ids = $entityQuery->execute();
    $invitation_popup = \Drupal::entityTypeManager()
      ->getStorage('invitation_popup_entity')
      ->loadMultiple($entities_ids);
    $options = [];
    foreach ($invitation_popup as $key => $value) {
      $options[$key] = $value->label();
    }

    return $options;
  }

  /**
   * Get abreviated document types.
   *
   * @return array
   *   List of abreviated document types.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getAbreviatedDocumentTypes() {
    $entityQuery = \Drupal::entityQuery('document_type_entity');
    $entities_ids = $entityQuery->execute();
    $document_types = \Drupal::entityTypeManager()
      ->getStorage('document_type_entity')
      ->loadMultiple($entities_ids);
    $doctypes = [];
    $doctype = [];
    foreach ($document_types as $key => $value) {
      $doctype['id'] = $value->id();
      $doctype['label'] = strtoupper($value->id());
      // $doctype['abbreviated_label'] = $value->getAbbDocType();
      array_push($doctypes, $doctype);
      unset($doctype);
    }
    return $doctypes;
  }

  /**
   * Get a list of the WiFi Channels.
   *
   * @return array|bool
   *   List of WiFi channels.
   */
  public function getWifiChannels() {
    $entityQuery = \Drupal::entityQuery('wifi_channel');
    $entityQuery->sort('id');
    $entities_ids = $entityQuery->execute();
    $wifiChannels = [];

    try {
      $wifiChannelsResult = \Drupal::entityTypeManager()
        ->getStorage('wifi_channel')
        ->loadMultiple($entities_ids);

      $wifiChannel = [];

      foreach ($wifiChannelsResult as $key => $value) {
        $wifiChannel['keyword'] = $value->getKeyword();
        $wifiChannel['name'] = $value->getName();
        array_push($wifiChannels, $wifiChannel);
        unset($wifiChannel);
      }
    }
    catch (InvalidPluginDefinitionException $e) {
      return FALSE;
    }

    return $wifiChannels;
  }

  /**
   * Get Wifi security types.
   *
   * @return array|bool
   *   Array containing Wifi security types options.
   */
  public function getWifiSecurityTypes() {
    $entityQuery = \Drupal::entityQuery('wifi_security_type_entity');
    $entityQuery->sort('id');
    $entities_ids = $entityQuery->execute();
    $wifiSecurityTypes = [];

    try {
      $wifiSecurityTypesResult = \Drupal::entityTypeManager()
        ->getStorage('wifi_security_type_entity')
        ->loadMultiple($entities_ids);

      $wifiSecurityType = [];

      foreach ($wifiSecurityTypesResult as $key => $value) {
        $wifiSecurityType['id'] = $key;
        $wifiSecurityType['keyword'] = $value->keyword->value;
        $wifiSecurityType['display_order'] = $value->display_order->value;
        $wifiSecurityType['name'] = $value->getName();

        // Index for sorting.
        $wifiSecurityTypes[$value->display_order->value] = $wifiSecurityType;

        unset($wifiSecurityType);
      }

      // We sort the results in ascending order.
      ksort($wifiSecurityTypes);
    }
    catch (InvalidPluginDefinitionException $e) {
      return FALSE;
    }

    return $wifiSecurityTypes;
  }

}
