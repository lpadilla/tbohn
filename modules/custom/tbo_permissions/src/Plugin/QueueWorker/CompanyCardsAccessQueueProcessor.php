<?php

namespace Drupal\tbo_permissions\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Processes Company Cards Access Tasks.
 *
 * @QueueWorker(
 *   id = "company_permissions_queue_processor",
 *   title = @Translation("Task Worker: Company Cards Access"),
 * )
 */
class CompanyCardsAccessQueueProcessor extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($companyId) {
    // Get repository.
    $permissionsRepository = \Drupal::service('tbo_permissions.admin_cards_repository');
    $permissionsRepository->createCompanyPermissionsSet($companyId);
  }

}
