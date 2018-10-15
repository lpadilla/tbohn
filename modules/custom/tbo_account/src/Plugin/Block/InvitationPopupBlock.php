<?php

namespace Drupal\tbo_account\Plugin\Block;

use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\tbo_general\CardBlockBase;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Provides a 'InvitationPopupBlock' block.
 *
 * @Block(
 *  id = "invitation_popup_list_block",
 *  admin_label = @Translation("Invitation popup"),
 * )
 */
class InvitationPopupBlock extends CardBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $table_fields = [
      'title'       => ['title' => $this->t("Titulo"), 'service_field' => 'title', 'show' => 1, 'weight' => 1, 'class' => 'double-top-and-bottom-padding'],
      'description' => ['title' => $this->t("Descripción"), 'service_field' => 'description', 'show' => 1, 'weight' => 1, 'class' => 'double-top-and-bottom-padding'],
      'icon'        => ['title' => $this->t("Icono"), 'service_field' => 'icon', 'show' => 1, 'weight' => 1, 'class' => 'double-top-and-bottom-padding'],
    ];

    return [
      'table_options' => [
        'table_fields' => $table_fields,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // $table_fields: variable que contiene la configuracion por defecto de las columnas de la tabla.
    $table_fields = $this->configuration['table_options']['table_fields'];

    if (!empty($table_fields)) {
      // table_options: fieldset que contiene todas las columnas de la tabla.
      $form['table_options'] = [
        '#type' => 'details',
        '#title' => $this->t('Configuraciones tabla'),
        '#open' => TRUE,
      ];
      $form['table_options']['table_fields'] = [
        '#type' => 'table',
        '#header' => [t('Title'), t('Show'), t('Weight'), ''],
        '#empty' => t('There are no items yet. Add an item.'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'fields-order-weight',
          ],
        ],
      ];

      // Se ordenan los filtros segun lo establecido en la configuración.
      uasort($table_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

      // Se crean todas las columnas de la tabla que mostrara la información.
      foreach ($table_fields as $id => $entity) {
        // TableDrag: Mark the table row as draggable.
        $form['table_options']['table_fields'][$id]['#attributes']['class'][] = 'draggable';
        // TableDrag: Sort the table row according to its existing/configured weight.
        $form['table_options']['table_fields']['#weight'] = $entity['weight'];

        // Some table columns containing raw markup.
        $form['table_options']['table_fields'][$id]['title'] = [
          '#plain_text' => $entity['title'],
        ];

        $form['table_options']['table_fields'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['show'],
        ];

        // TableDrag: Weight column element.
        $form['table_options']['table_fields'][$id]['weight'] = [
          '#type' => 'weight',
          '#title' => t('Weight for @title', ['@title' => $entity['title']]),
          '#title_display' => 'invisible',
          '#default_value' => $entity['weight'],
          // Classify the weight element for #tabledrag.
          '#attributes' => ['class' => ['fields-order-weight']],
        ];

        $form['table_options']['table_fields'][$id]['service_field'] = [
          '#type' => 'hidden',
          '#value' => $entity['service_field'],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Set data uuid, filters_fields, table_fields.
    $this->cardBuildHeader(FALSE, FALSE);
    $this->config_name = 'InvitationPopupBlock';

    // Set session var.
    $this->cardBuildSession();

    // Se construye la variable $build con los datos que se necesitan en el tema.
    $parameters = [
      'theme' => 'invitation_popup',
    ];

    usort($this->configuration['table_options']['table_fields'], function ($a1, $a2) {
      $v1 = $a1['weight'];
      $v2 = $a2['weight'];
      // $v2 - $v1 to reverse direction.
      return $v1 - $v2;
    });
    $type_service = '';
    $category = \Drupal::request()->get('category');

    $service = \Drupal::service('tbo_account.invitation_popup');
    $invitation_popup = $service->getInvitationPopupByCategory($category);
    $actions = \GuzzleHttp\json_decode($invitation_popup['actions_popup']);

    // Get type category.
    $typeCategory = 'fijo';
    if ($category == 'movil') {
      $typeCategory = 'movil';
    }

    // Parameter additional.
    $others = [
      '#fields' => $this->configuration['table_options']['table_fields'],
      '#uuid' => $this->configuration['uuid'],
      '#popup' => [
        'icon' => $invitation_popup['icon_url'],
        'title' => $invitation_popup['label'],
        'description' => $invitation_popup['description'],
        'actions' => (array) $actions,
        'typeCategory' => $typeCategory,
      ],
    ];

    $this->cardBuildVarBuild($parameters, $others);

    // Se carga los datos necesarios para la directiva angular, se envia el rest.
    $config_block = $this->cardBuildConfigBlock('/tboapi/invitation_popup/list/invitation_popup?_format=json', ['table_fields' => $this->configuration['table_options']['table_fields']]);

    // Se agrega la configuracion necesaria al objeto drupal.js.
    $this->cardBuildAddConfigDirective($config_block);

    // Se guarda el log de auditoria.
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
    if ($category == 'movil') {
      $type_service = 'moviles';
    }
    else {
      $type_service = 'fijos';
    }

    $rol = \Drupal::service('tbo_core.repository')->getRoleName($account->get('roles')->getValue()[0]['target_id']);
    $log->set('user_names', $name);
    $log->set('created', time());
    $log->set('company_segment', $segment);
    $log->set('user_id', $uid);
    $log->set('user_names', $name);
    $log->set('company_name', $_SESSION['company']['name']);
    $log->set('company_document_number', $_SESSION['company']['nit']);
    $log->set('user_role', $rol);
    $log->set('event_type', 'Servicios');
    $log->set('description', 'Usuario consulta detalle de categoría de servicios que no tiene contratada');
    $log->set('details', 'Usuario ' . $name . ' consulta la categoría ' . $category . ' de los servicios ' . $type_service . ' del portafolio de su empresa, pero no la tiene contratada');
    $log->set('old_values', 'No aplica');
    $log->set('new_values', 'No aplica');
    $log->save();

    return $this->build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $roles = $account->getRoles();

    if (in_array('administrator', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles) || in_array('admin_company', $roles)) {
      return parent::blockAccess($account);
    }

    return AccessResult::forbidden();

  }

}
