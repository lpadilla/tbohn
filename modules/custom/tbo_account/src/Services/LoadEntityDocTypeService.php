<?php

namespace Drupal\tbo_account\Services;

use Drupal\adf_core\Base\BaseApiCache;

/**
 * Class LoadEntityDocTypeService.
 *
 * @package Drupal\tbo_account\Services
 */
class LoadEntityDocTypeService {

  private $documents;

  /**
   * Constructor.
   */
  public function __construct() {

    // Getting data from cache before executing a request.
    $response = BaseApiCache::get('document_types', []);
    if ($response !== FALSE && !is_null($response)) {
      $this->documents = $response;
    }
    else {
      $documents = \Drupal::entityQuery('document_type_entity')->execute();
      $options = [];

      foreach ($documents as $document => $value) {
        $entity = \Drupal::entityTypeManager()
          ->getStorage('document_type_entity')
          ->load($document);
        $options[$entity->get('id')] = $entity->get('label');
      }

      // Save cache.
      BaseApiCache::set('document_types', [], $options, 60);

      $this->documents = $options;
    }
  }

  /**
   * @return mixed
   */
  public function getDocuments() {
    return $this->documents;
  }

}
