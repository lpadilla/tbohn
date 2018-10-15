<?php

namespace Drupal\tbo_billing\Controller;

use Drupal\user\Entity\User;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\tbo_core\Entity\AuditLogEntity;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Class SummaryLogController.
 *
 * @package Drupal\tbo_billing\Controller
 */
class SummaryLogController extends ControllerBase {

  /**
   * Registerevent.
   *
   * @return string
   *   Return Hello string.
   */
  public function registerEvent($event) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    // Guardado log auditoria.
    $log = AuditLogEntity::create();
    // Temporary.
    $segment = 'segmento';
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    // Load fields account.
    $account_fields = \Drupal::currentUser()->getAccount();
    if (isset($account_fields->full_name) && !empty($account_fields->full_name)) {
      $name = $account_fields->full_name;
    }
    else {
      $name = \Drupal::currentUser()->getAccountName();
    }

    // Get name rol.
    $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);

    $log->set('user_names', $name);
    $log->set('created', time());
    $log->set('company_segment', $segment);
    $log->set('user_id', $uid);
    $log->set('user_names', $name);
    $log->set('company_name', $_SESSION['company']['name']);
    $log->set('company_document_number', $_SESSION['company']['nit']);
    $log->set('user_role', $rol);
    $log->set('event_type', 'Facturación');
    $log->set('old_values', 'No aplica');
    $log->set('new_values', 'No aplica');

    $type_service = '';

    if ($_GET['type'] == 'movil') {
      $type_service = 'moviles';
    }
    else {
      $type_service = 'fijos';
    }

    switch ($event) {
      case 'details':
        $log->set('description', 'Usuario consulta listado de facturas vigentes');
        $log->set('details', 'Usuario ' . $name . ' ' . 'consultó listado de facturas vigentes de sus servicios ' . $type_service);
        $log->save();
        return new TrustedRedirectResponse($_GET['redirect']);

      break;

      case 'payment':
        // TODO pendiente implementación pago multiple de facturas.
        $log->set('description', 'Usuario consulta listado de facturas vigentes');
        $log->set('details', 'Usuario ' . $name . ' ' . 'consultó listado de facturas vigentes de sus servicios ' . $type_service);
        $log->save();
        return new TrustedRedirectResponse($_GET['redirect']);

      break;

      case 'knowledge':
        $log->set('description', 'Usuario accede a más información de servicio no contratado');
        $log->set('details', 'Usuario ' . $name . ' ' . 'consultó información para conocer más sobre los servicios ' . $_GET['type'] . 's');
        $log->save();
        return new TrustedRedirectResponse($_GET['redirect']);

      break;

      case 'service':
        $log->set('description', 'Usuario consulta portafolio de servicios');
        $log->set('details', 'Usuario ' . $name . ' ' . 'consulta la categoría ' . $_GET['category'] . ' de los servicios ' . $type_service . ' del portafolio de su empresa');
        $log->save();
        return new RedirectResponse(Url::fromUri('internal:' . $_GET['redirect'] . '?category=' . $_GET['category'])
          ->toString());

      break;

      case 'briefcase':
        $log->set('description', 'Usuario accede al portafolio de servicios');
        $log->set('details', 'Usuario ' . $name . ' accede al portafolio de servicios asociados a su empresa');
        $log->save();
        return new RedirectResponse(Url::fromUri('internal:' . $_GET['redirect'])
          ->toString());

      break;

      case 'te llamamos':
        $type_service = '';
        if ($_GET['category'] == 'Móvil') {
          $type_service = 'moviles';
        }
        else {
          $type_service = 'fijos';
        }

        $log->set('description', 'Usuario accede a formulario “Te llamamos"');
        $log->set('details', 'Usuario ' . $name . ' accede a formulario “Te llamamos” de la categoría ' . $_GET['category'] . ' que no tiene contratada de los servicios ' . $type_service);
        $log->save();
        return new RedirectResponse(Url::fromUri('internal:' . $_GET['redirect'])
          ->toString());

      break;

      case 'Más información':
        $type_service = '';
        if ($_GET['category'] == 'Móvil') {
          $type_service = 'moviles';
        }
        else {
          $type_service = 'fijos';
        }

        $log->set('description', 'Usuario accede a más información de un servicio no contratado');
        $log->set('details', 'Usuario ' . $name . ' accede a más información de la categoría ' . $_GET['category'] . ' que no tiene contratado en su portafolio de servicios  ' . $type_service);
        $log->save();
        return new RedirectResponse(Url::fromUri('internal:' . $_GET['redirect'])
          ->toString());

      break;
    }
  }

}
