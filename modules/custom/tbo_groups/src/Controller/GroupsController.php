<?php

namespace Drupal\tbo_groups\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Masterminds\HTML5\Exception;

/**
 * Class GroupsController.
 *
 * @package Drupal\tbo_groups\Controller
 */
class GroupsController extends ControllerBase {

  /**
   *
   */
  public function groupMessageConfirm($type, $name, $pathname, $confirm) {
    $this->name = str_replace('-', ' ', $name);
    if ($type == 'changeState') {
      $this->changeState($type, $name, $pathname, $confirm);
    }
    elseif ($type == 'deleteGroup') {
      $this->deleteGroup($type, $name, $pathname, $confirm);
    }

    return new RedirectResponse(Url::fromUri('internal:/' . $pathname)->toString());
  }

  /**
   *
   */
  public function deleteGroup($type, $name, $pathname, $confirm) {
    if ($confirm) {
      // Get id Group.
      $database = \Drupal::database();
      $query = $database->select('group_entity_field_data', 'group_entity');
      $query->distinct();
      $query->fields('group_entity', ['id']);
      $query->condition('group_entity.name', $name);
      $gid = $query->execute()->fetchField();

      $query = \Drupal::database()->delete('group_entity');
      $query->condition('id', $gid);
      $response = $query->execute();

      if ($response) {
        // Delete entity data.
        $query = \Drupal::database()->delete('group_entity_field_data');
        $query->condition('id', $gid);
        $response = $query->execute();
      }

      if ($response) {
        drupal_set_message('El grupo ' . $this->name . ' fue eliminado');
      }
      else {
        drupal_set_message('No se ha podido eliminar el grupo ' . $this->name . ' Verifique con el administrador');
      }
      return TRUE;
    }
    $request = \Drupal::request();
    // If the form hasn't been sent via ajax, we redirect exception.
    if (!$request->isXmlHttpRequest()) {
      throw new Exception("Error de acceso");
    }

    // Prepare vars.
    $message = '¿Está seguro que desea eliminar grupo ' . $this->name . '. Esta acción no se puede deshacer';

    // Load Service twig.
    $twig = \Drupal::service('twig');

    // Load template for change state.
    $template = $twig->loadTemplate(drupal_get_path('module', 'tbo_groups') . '/templates/block--groups-message.html.twig');

    // Render template and variables.
    echo $template->render([
      'message' => $message,
      'name' => $this->name,
      'pathname' => $pathname,
      'state_change' => 0,
      'button' => 'Eliminar',
      'type' => $type,
    ]);

    // Closet load.
    die;
  }

  /**
   *
   */
  public function getDataGroup($name) {

    // Get Group by name.
    $database = \Drupal::database();
    $query = $database->select('group_entity_field_data', 'group_entity');
    $query->distinct();
    $query->fields('group_entity', ['id', 'name', 'administrator']);
    $query->condition('group_entity.name', $name);
    $response = $query->execute()->fetchAll();

    if ($response) {

      $response = reset($response);
      $query = $database->select('group_account_relations_field_data', 'g_a_r');
      $query->condition('g_a_r.group_id', $response->id, '=');
      $query->addField('g_a_r', 'account');
      $result_2 = $query->execute()->fetchAll();

      $accounts = [];
      foreach ($result_2 as $res_2) {
        $accounts[$res_2->account] = $res_2->account;
      }

      $response->associated_accounts = $accounts;
      return JsonResponse::create($response);
    }

    return JsonResponse::create(FALSE);

  }

}
