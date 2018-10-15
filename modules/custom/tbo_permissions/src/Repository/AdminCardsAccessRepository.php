<?php

namespace Drupal\tbo_permissions\Repository;

/**
 * Class AdminCardsAccessRepository.
 *
 * @package Drupal\tbo_permissions\Repository
 */
class AdminCardsAccessRepository {

  /**
   * Connection service to database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  protected $tboConfig;

  protected $auditLogService;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->database = \Drupal::database();

    $this->tboConfig = \Drupal::service('tbo_general.tbo_config');

    $this->auditLogService = \Drupal::service('tbo_core.audit_log_service');
    $this->auditLogService->loadName();
  }

  /**
   * Search all cards and return them in a array.
   */
  public function getAllCards() {
    // Get blocks definition.
    $cardsInfo = [];
    $blockManager = \Drupal::service('plugin.manager.block');
    $contextRepository = \Drupal::service('context.repository');
    $definitions = $blockManager->getDefinitionsForContexts($contextRepository->getAvailableContexts());
    foreach ($definitions as $key => $block) {
      // TODO Change the filter method, meaning create a custom field
      // in CardBlockBase.
      // We only want Cards.
      if ($block['provider'] != 'system') {
        $cardsInfo[$block['id']] = [
          'adminLabel' => $block['admin_label'],
          'cardId' => $block['id'],
          'category' => $block['category'],
          'module' => $block['provider'],
        ];
      }
    }

    return $cardsInfo;
  }

  /**
   * Creates a complete set of card access for the given Company.
   *
   * @param int $company_id
   *   Company ID.
   *
   * @throws \Exception
   */
  public function createCompanyPermissionsSet($company_id = 0) {
    if ($company_id != 0 && $company_id != 0) {
      // First we get a list of all plugin blocks.
      $cardsInfo = $this->getAllCards();

      // Now we iterate on all the blocks, so we create a complete set of
      // permissions for the Company.
      try {
        $insertObj = $this->database->insert('cards_access_by_company_permissions')
          ->fields([
            'block_id',
            'company_id',
            'access_status',
            'created',
            'last_modification',
          ]);

        foreach ($cardsInfo as $card) {
          // Create a relation between the Company and Card.
          $time = time();
          $insertObj->values([
            'block_id' => $card['cardId'],
            'company_id' => $company_id,
            'access_status' => 1,
            'created' => $time,
            'last_modification' => $time,
            'last_modification_author' => $this->auditLogService->getName(),
          ]);
        }

        $insertObj->execute();
      }
      catch (\Exception $e) {
      }
    }
  }

  /**
   * Updates card access.
   *
   * @param int $card_access_id
   *   Card Access Id.
   * @param int $status
   *   New status.
   *
   * @return string
   *   Error message.
   */
  public function updateCompanyPermissions($card_access_id = 0, $status = 0) {
    $result = 0;

    if ($card_access_id != 0) {
      try {
        $author_name = $this->auditLogService->getName();
        if (empty($author_name)) {
          $author_name = t('No disponible');
        }

        $result = $updateObj = $this->database->update('cards_access_by_company_permissions')
          ->fields([
            'access_status' => $status,
            'last_modification' => time(),
            'last_modification_author' => $author_name,
          ])
          ->condition('id', $card_access_id)
          ->execute();
      }
      catch (\Exception $e) {
        return $e->getMessage();
      }
    }

    return $result;
  }

  /**
   * Get companies list.
   *
   * @param int $status
   *   Status filter.
   *
   * @return array
   *   Companies info.
   */
  public function getCompanies($status = 1) {
    $queryCompany = $this->database->select('company_entity_field_data', 'company')
      ->fields('company', ['id'])
      ->condition('company.status', $status);
    $companiesResult = $queryCompany->execute();

    return $companiesResult;
  }

  /**
   * Validates a Company Document.
   *
   * @param string $document_type
   *   Document type.
   * @param string $document_number
   *   Document number.
   *
   * @return array
   *   Company info or Error message.
   */
  public function validateCompanyDocument($document_type, $document_number) {
    $companyInfo = [
      'result' => FALSE,
      'error' => t('No hay datos disponibles'),
    ];

    if ($document_type != '' && $document_type != NULL &&
      $document_number != '' && $document_number != NULL) {
      $queryCompanies = $this->database->select('company_entity_field_data', 'comp')
        ->fields('comp', [
          'id',
          'company_name',
          'document_type',
          'document_number',
        ]);

      $queryCompanies->condition('comp.status', '1');
      $queryCompanies->condition('comp.document_type', $document_type);
      $queryCompanies->condition('comp.document_number', $document_number);

      try {
        $resultCompany = $queryCompanies->execute()->fetchAssoc();
        if ($resultCompany) {
          $company_document = strtoupper($resultCompany['document_type'])
            . ' #' . $resultCompany['document_number'];
          $companyInfo = [
            'result' => TRUE,
            'company_id' => $resultCompany['id'],
            'company_name' => $resultCompany['company_name'],
            'company_document' => $company_document,
          ];
        }
      }
      catch (\Exception $e) {
        $companyInfo = [
          'result' => FALSE,
          'error' => $e->getMessage(),
        ];
      }
    }

    return $companyInfo;
  }

  /**
   * Get all Cards Access.
   *
   * @param array $filters
   *   Filters.
   *
   * @return array
   *   All cards access info.
   */
  public function getAllCardsAccess(array $filters = []) {
    $cardsAccessInfo = [];

    // Add filters to query.
    if (count($filters) > 0) {
      $queryCardsAccess = $this->database->select('cards_access_by_company_permissions', 'ca')
        ->fields('ca', ['id', 'block_id', 'access_status']);

      foreach ($filters as $key => $filterValue) {
        if ($key == 'validated_company_id') {
          $queryCardsAccess->condition('ca.company_id', $filterValue);
        }
        elseif ($key == 'selected_block_id') {
          $queryCardsAccess->condition("ca.block_id", $filterValue);
        }
        elseif ($key == 'card_access_status') {
          $queryCardsAccess->condition('ca.access_status', $filterValue);
        }
      }

      try {
        $resultCardsAccess = $queryCardsAccess->execute()->fetchAll();

        // We complete the fields, with the name of the Card (Admin Label).
        $cardsInfo = $this->getAllCards();

        foreach ($resultCardsAccess as $cardAccess) {
          if (isset($cardsInfo[$cardAccess->block_id]['adminLabel'])) {
            $cardNameLabel = (string) $cardsInfo[$cardAccess->block_id]['adminLabel'];
            $cardsAccessInfo[] = [
              'id' => $cardAccess->id,
              'card_name' => $cardNameLabel,
              'access_status' => $cardAccess->access_status,
            ];
          }
        }
      }
      catch (\Exception $e) {
        $cardsAccessInfo = $e->getMessage();
      }
    }

    return $cardsAccessInfo;
  }

  /**
   * Get all Companies with the blocked Card Access.
   *
   * @param array $filters
   *   Filters.
   *
   * @return array
   *   Companies with blocked cards.
   */
  public function getCompaniesWithBlockedCards(array $filters = []) {
    $companiesWithBlockedCardsInfo = [];

    // Add filters to query.
    if (count($filters) > 0) {
      $queryCardsAccess = $this->database->select('cards_access_by_company_permissions', 'ca')
        ->fields('ca', ['block_id', 'last_modification'])
        ->fields('comp', ['company_name', 'document_type', 'document_number']);

      $queryCardsAccess->leftJoin('company_entity_field_data', 'comp', 'ca.company_id = comp.id');

      // Blocked card condition.
      $queryCardsAccess->condition('ca.access_status', '0');

      // Active company condition.
      $queryCardsAccess->condition('comp.status', '1');

      foreach ($filters as $key => $filterValue) {
        if ($key == 'selected_block_id') {
          $queryCardsAccess->condition("ca.block_id", $filterValue);
        }
        elseif ($key == 'document_number') {
          $queryCardsAccess->condition("comp.document_number", $filterValue);
        }
        elseif ($key == 'document_type') {
          $queryCardsAccess->condition("comp.document_type", $filterValue);
        }
      }

      try {
        $resultCardsAccess = $queryCardsAccess->execute()->fetchAll();
        if (count($resultCardsAccess) > 0) {
          // We complete the fields, with the name Card name (Admin Label)
          // and Document Types.
          $cardsInfo = $this->getAllCards();
          $documentTypes = $this->getDocumentTypesInfo();

          foreach ($resultCardsAccess as $cardAccess) {
            $cardNameLabel = $cardAccess->block_id;
            if (isset($cardsInfo[$cardAccess->block_id]['adminLabel'])) {
              $cardNameLabel = (string) $cardsInfo[$cardAccess->block_id]['adminLabel'];
            }
            $companiesWithBlockedCardsInfo[] = [
              'document_type' => $documentTypes[$cardAccess->document_type],
              'document_number' => $cardAccess->document_number,
              'company_name' => strtoupper($cardAccess->company_name),
              'card_name' => $cardNameLabel,
              'block_event_date' => $this->tboConfig->formatDateUpdate($cardAccess->last_modification),
            ];
          }
        }
      }
      catch (\Exception $e) {
        $companiesWithBlockedCardsInfo = $e->getMessage();
      }
    }

    return $companiesWithBlockedCardsInfo;
  }

  /**
   * Autocomplete function for search Cards that match the name criteria.
   *
   * @param string $keyword
   *   Keyword to use for autocomplete.
   *
   * @return array
   *   Suggested cards info.
   */
  public function getAutocompleteCards($keyword) {
    $suggestedCards = [];
    $allCardsInfo = $this->getAllCards();

    $keyword = strtoupper($keyword);

    // Now we search in $allCards.
    foreach ($allCardsInfo as $card) {
      $label = (string) $card['adminLabel'];
      $labelTmp = strtoupper($label);

      if (strpos($labelTmp, $keyword) !== FALSE) {
        $suggestedCards[] = [
          'block_id' => $card['cardId'],
          'label' => $label,
        ];
      }
    }

    return $suggestedCards;
  }

  /**
   * Get the log tokens info.
   *
   * @param int $card_access_id
   *   Card Access Id.
   *
   * @return array
   *   Log tokens info.
   */
  public function getTokenLogInfo(int $card_access_id = 0) {
    $tokenLog = [];

    $queryCardAccess = $this->database->select('cards_access_by_company_permissions', 'ca')
      ->fields('ca', ['block_id', 'last_modification'])
      ->fields('comp', ['company_name', 'document_type', 'document_number']);

    $queryCardAccess->leftJoin('company_entity_field_data', 'comp', 'ca.company_id = comp.id');
    $queryCardAccess->condition('ca.id', $card_access_id);

    try {
      $resultCardAccess = $queryCardAccess->execute()->fetchAssoc();
      $tokenLog = [
        '@company_name' => $resultCardAccess['company_name'],
        '@document_type' => $resultCardAccess['document_type'],
        '@document_number' => $resultCardAccess['document_number'],
        '@block_id' => $resultCardAccess['block_id'],
      ];
    }
    catch (\Exception $e) {
      $tokenLog = $e->getMessage();
    }

    return $tokenLog;
  }

  /**
   * Get Card Access info for a Company.
   *
   * @param int $company_id
   *   Company Id.
   * @param string $block_id
   *   Block Id.
   *
   * @return bool
   *   Card Access permission.
   */
  public function getCardAccess(int $company_id = 0, string $block_id = '') {
    $result = TRUE;

    $queryCardAccess = $this->database->select('cards_access_by_company_permissions', 'ca')
      ->fields('ca', ['access_status']);

    $queryCardAccess->condition('ca.company_id', $company_id);
    $queryCardAccess->condition('ca.block_id', $block_id);

    try {
      $resultCardAccess = $queryCardAccess->execute()->fetchAssoc();
      if ($resultCardAccess) {
        $result = $resultCardAccess['access_status'] == '1' ? TRUE : FALSE;
      }
      else {
        $result = TRUE;
      }
    }
    catch (\Exception $e) {
      $result = $e->getMessage();
    }

    return $result;
  }

  /**
   * Update Cards Access statuses.
   *
   * @param array $cardsAccessInfo
   *   Cards access statuses.
   *
   * @return bool
   *   Update results.
   */
  public function updateCardsAccess(array $cardsAccessInfo) {
    $auditLogService = \Drupal::service('tbo_core.audit_log_service');

    $result = FALSE;
    if (isset($cardsAccessInfo)) {
      try {
        // We complete the fields, with the name of the Card (Admin Label).
        $cardsInfo = $this->getAllCards();

        foreach ($cardsAccessInfo as $item) {
          if ($item['status'] == '') {
            $item['status'] = '0';
          }

          $this->updateCompanyPermissions($item['id'], $item['status']);
          $result = TRUE;

          // Save audit log on access permission modification.
          $tokenLog = $this->getTokenLogInfo($item['id']);
          $cardNameLabel = (string) $cardsInfo[$tokenLog['@block_id']]['adminLabel'];
          $tokenLog['@card_name'] = $cardNameLabel;
          $tokenLog['@user'] = $auditLogService->getName();

          $dataLog = [
            'companyName' => t('No aplica'),
            'companyDocument' => t('No aplica'),
            'companySegment' => t('No aplica'),
            'event_type' => t('Cuenta'),
            'old_value' => t('No disponible'),
            'new_value' => t('No disponible'),
          ];

          if ($item['status'] == '1') {
            $dataLog['description'] = t('Usuario activa permisos de funcionalidad por empresa');
            $dataLog['details'] = t('Usuario @user accede los permisos de una funcionalidad de la empresa @company_name @document_type @document_number. Las funcionalidades que se activaron fue: @card_name', $tokenLog);
          }
          elseif ($item['status'] == '0') {
            $dataLog['description'] = t('Usuario desactiva permisos de funcionalidad por empresa');
            $dataLog['details'] = t('Usuario @user accede los permisos de una funcionalidad de la empresa @company_name @document_type @document_number. Las funcionalidades que se desactivaron fue: @card_name', $tokenLog);
          }
          $auditLogService->insertGenericLog($dataLog);
        }
      }
      catch (\Exception $e) {
        $result = $e->getMessage();
      }
    }

    return $result;
  }

  /**
   * Gets the Check Access Card configuration value.
   *
   * @return bool
   *   Check cards access activated flag.
   */
  public function getCheckAccessCardConfig() {
    $config = \Drupal::config('tbo_permissions.tbopermissionssettings');
    return $config->get('check_cards_access');
  }

  /**
   * Get all Companies with Card Access modified today.
   *
   * @param array $filters
   *   Filters.
   *
   * @return array
   *   Companies with blocked cards.
   */
  public function getCompaniesWithCardsAccessChangedToday(array $filters = []) {
    $companiesWithModifiedCardsAccessInfo = [];
    $statusLabels = [
      '0' => 'Inactivo',
      '1' => 'Activo',
    ];

    // Add filters to query.
    if (count($filters) > 0) {
      $queryCardsAccess = $this->database->select('cards_access_by_company_permissions', 'ca')
        ->fields('ca', [
          'block_id',
          'access_status',
          'last_modification',
          'last_modification_author',
        ])
        ->fields('comp', ['company_name', 'document_type', 'document_number']);

      $queryCardsAccess->leftJoin('company_entity_field_data', 'comp', 'ca.company_id = comp.id');

      // Active company condition.
      $queryCardsAccess->condition('comp.status', '1');

      // Happened Today condition.
      // Get time range for today.
      $startTime = strtotime(date("Y-m-d") . " 00:00:00");
      $endTime = strtotime(date("Y-m-d") . " 23:59:59");
      $queryCardsAccess->condition('ca.last_modification', 'ca.created', '>');
      $queryCardsAccess->condition('ca.last_modification', $startTime, '>=');
      $queryCardsAccess->condition('ca.last_modification', $endTime, '<=');

      try {
        $documentTypes = $this->getDocumentTypesInfo();

        // We complete the fields, with the Card name (Admin Label).
        $cardsInfo = $this->getAllCards();

        $resultCardsAccess = $queryCardsAccess->execute()->fetchAll();
        foreach ($resultCardsAccess as $cardAccess) {
          $cardNameLabel = $cardAccess->block_id;
          if (isset($cardsInfo[$cardAccess->block_id]['adminLabel'])) {
            $cardNameLabel = (string) $cardsInfo[$cardAccess->block_id]['adminLabel'];
          }
          $companiesWithModifiedCardsAccessInfo[] = [
            'company_name' => strtoupper($cardAccess->company_name),
            'document_type' => $documentTypes[$cardAccess->document_type],
            'document_number' => $cardAccess->document_number,
            'card_name' => $cardNameLabel,
            'status_before' => $statusLabels[!$cardAccess->access_status],
            'status_now' => $statusLabels[$cardAccess->access_status],
            'author' => $cardAccess->last_modification_author,
            'block_event_date' => $this->tboConfig->formatDateUpdate($cardAccess->last_modification),
          ];
        }
      }
      catch (\Exception $e) {
        $companiesWithModifiedCardsAccessInfo = $e->getMessage();
      }
    }

    return $companiesWithModifiedCardsAccessInfo;
  }

  /**
   * Return all Super Admins users email info.
   */
  public function getAllSuperAdminsInfo() {
    $query = $this->database->select('users_field_data', 'ufd');
    $query->fields('ufd', ['full_name', 'name', 'mail']);
    $query->innerJoin('user__roles', 'ur', 'ufd.uid = ur.entity_id');
    $query->condition('ur.roles_target_id', 'super_admin', '=');
    $superAdminUsers = $query->execute()->fetchAll();

    return $superAdminUsers;
  }

  /**
   * Return all Document Types.
   *
   * @return array
   *   Document types.
   */
  public function getDocumentTypesInfo() {
    $entitiesService = \Drupal::service('tbo_entities.entities_service');
    $documentTypesInfo = $entitiesService->getDocumentTypes();
    $documentTypes = [];
    foreach ($documentTypesInfo as $key => $value) {
      $documentTypes[$value['id']] = $value['label'];
    }

    return $documentTypes;
  }

  /**
   * Creates a complete set of card access for the given Card.
   *
   * @param string $block_id
   *   Block ID.
   *
   * @throws \Exception
   */
  public function createCardPermissionsSet(string $block_id = '') {
    $markupInfo = [];

    if (!empty($block_id)) {
      // First we get a list of all companies.
      $queryCompany = $this->database->select('company_entity_field_data', 'company')
        ->fields('company', ['id', 'company_name']);
      $companiesInfo = $queryCompany->execute();

      // Now we iterate on all the blocks, so we create a complete set of
      // permissions for the Company.
      try {
        $insertObj = $this->database->insert('cards_access_by_company_permissions')
          ->fields([
            'block_id',
            'company_id',
            'access_status',
            'created',
            'last_modification',
            'last_modification_author',
          ]);

        foreach ($companiesInfo as $company) {
          // Create a relation between the Company and Card.
          $insertObj->values([
            'block_id' => $block_id,
            'company_id' => $company->id,
            'access_status' => 1,
            'created' => time(),
            'last_modification' => time(),
            'last_modification_author' => $this->auditLogService->getName(),
          ]);

          // We gather the markup data to show results to the admin user.
          $tokensTranslation = [
            '@company_name' => $company->company_name,
            '@plugin_id' => $block_id,
          ];
          $markupInfo[] = t('Se ha creado un registro de permiso de acceso a card para la empresa @company_name y el card @plugin_id', $tokensTranslation);
        }

        $insertObj->execute();
      }
      catch (\Exception $e) {
      }
    }

    return $markupInfo;
  }

  /**
   * Get all Companies with the blocked Card Access for the Excel report.
   *
   * @return array
   *   All companies with blocked cards.
   */
  public function getAllCompaniesWithBlockedCards() {
    $companiesWithBlockedCardsInfo = [];

    // Add filters to query.
    $queryCardsAccess = $this->database->select('cards_access_by_company_permissions', 'ca')
      ->fields('ca', ['block_id', 'last_modification'])
      ->fields('comp', ['company_name', 'document_type', 'document_number']);

    $queryCardsAccess->leftJoin('company_entity_field_data', 'comp', 'ca.company_id = comp.id');

    // Blocked card condition.
    $queryCardsAccess->condition('ca.access_status', '0');

    // Active company condition.
    $queryCardsAccess->condition('comp.status', '1');

    try {
      $resultCardsAccess = $queryCardsAccess->execute()->fetchAll();
      if (count($resultCardsAccess) > 0) {
        // We complete the fields, with the name Card name (Admin Label)
        // and Document Types.
        $cardsInfo = $this->getAllCards();
        $documentTypes = $this->getDocumentTypesInfo();

        foreach ($resultCardsAccess as $cardAccess) {
          $cardNameLabel = $cardAccess->block_id;
          if (isset($cardsInfo[$cardAccess->block_id]['adminLabel'])) {
            $cardNameLabel = (string) $cardsInfo[$cardAccess->block_id]['adminLabel'];
          }
          $companiesWithBlockedCardsInfo[] = [
            'document_type' => $documentTypes[$cardAccess->document_type],
            'document_number' => $cardAccess->document_number,
            'company_name' => strtoupper($cardAccess->company_name),
            'card_name' => $cardNameLabel,
            'access_status' => 'BLOQUEADO',
            'block_event_date' => $this->tboConfig->formatDateUpdate($cardAccess->last_modification),
          ];
        }
      }
    }
    catch (\Exception $e) {
      $companiesWithBlockedCardsInfo = $e->getMessage();
    }

    return $companiesWithBlockedCardsInfo;
  }

}
