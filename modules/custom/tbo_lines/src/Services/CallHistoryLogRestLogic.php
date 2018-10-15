<?php

namespace Drupal\tbo_lines\Services;


use Drupal\Core\Session\AccountInterface;
use Drupal\rest\ResourceResponse;


class CallHistoryLogRestLogic {

  protected $currentUser;

  /**
   * TransactionCategoryRestLogic constructor.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * @return ResourceResponse
   */
  public function post($data) {
    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $type = $data['type'];
    $action = $data['action'];
    $user = $this->currentUser->getAccount()->full_name;;
    $line = $_SESSION['serviceDetail']['address'];
    $contrato = $_SESSION['serviceDetail']['contractId'];
    $description = t("Usuario descarga reporte de historial de consumos voz");
    $details = t("Usuario @usuario descarga reporte @type de consumo de voz de la linea @linea asociada al contrato @contrato", [
      '@usuario' => $user,
      '@type' => $type,
      '@linea' => $line,
      '@contrato' => $contrato,
    ]);
    if ($action == "detalle") {
      $description = t("Usuario consulta historial de consumos voz");
      $details = t("Usuario @usuario consulta historial de voz de la línea @linea asociada al contrato @contrato", [
        '@usuario' => $user,
        '@linea' => $line,
        '@contrato' => $contrato,
      ]);
    }

    $service = \Drupal::service('tbo_core.audit_log_service');

    $params = [
      'companyName' => $_SESSION['company']['name'],
      'companyDocument' => $_SESSION['company']['nit'],
      'companySegment' => $_SESSION['company']['segment'],
      'event_type' => 'Servicios',
      'description' => $description,
      'details' => $details,
      'old_value' => 'No disponible',
      'new_value' => 'No disponible',
    ];
    $service->insertGenericLog($params);
    return (new ResourceResponse('Log insertado'))->addCacheableDependency($build);
  }
}