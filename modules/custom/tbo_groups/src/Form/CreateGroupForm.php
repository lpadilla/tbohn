<?php

namespace Drupal\tbo_groups\Form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tbo_groups\Entity\GroupAccountRelations;
use Drupal\tbo_groups\Entity\GroupEntity;
use Drupal\tbo_core\Entity\AuditLogEntity;

/**
 * Class CreateGroupForm.
 *
 * @package Drupal\tbo_groups\Form
 */
class CreateGroupForm extends FormBase {

  /**
   * $fixed => Valor cuando el servicio no es fijo
   * $mobile => Valor cuando el servicio no es mobile.
   */
  protected $service_message;
  private $fixed = 'no fixed';
  private $mobile = 'no mobile';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_group_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div class="formselect jscroll" id="appcat">';
    $form['#suffix'] = '</div>';
    $form['#tree'] = TRUE;

    $form['is_new'] = [
      '#type' => 'hidden',
      // '#value' => 1,.
    ];

    $form['gid_update'] = [
      '#type' => 'hidden',
      // '#value' => 0,.
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del grupo'),
      '#maxlength' => 130,
      '#required' => TRUE,
    ];

    $options = $this->getAdministrators();
    $default_value = array_values($options);
    $default_value = array_shift($default_value);
    $form['administrator'] = [
      '#type' => 'select',
      '#title' => $this->t('Administrador'),
      '#options' => $options,
      '#default_value' => $default_value,
      '#description' => $this->t('El administrador de empresas debe crearse previamente. Crear uno ahora'),
      '#required' => TRUE,
    ];

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['tags-wrapper'],
        'ng-mouseleave' => 'closeSuggestions()',
      ],
    ];

    $form['container']['associated_accounts'] = [
      '#id' => 'account',
      '#name' => 'account',
      '#title' => $this->t('Cuentas asociadas'),
      '#type' => 'textfield',
      '#maxlength' => 200,
      '#attributes' => [
        'ng-change' => ["searchAccount('account')"],
        'data-ng-model' => ['account'],
        'autocomplete' => ['off'],
        'ng-keydown' => ['checkKeyDownAccount($event,' . "'account')"],
        'class' => ['isautocomplete', 'format-moments2'],
        'autocomplete_ajax_account' => 'autocomplete_ajax_account',
      ],
      '#autocomplete' => ['TRUE'],
    ];

    $form['container']['associated_accounts']['#prefix'] = '<div id="tagsList" class="tags-cloud">';
    $form['container']['associated_accounts']['#suffix'] = '</div>';

    /*$form['container']['multiple_select_account_mobile'] = ['#markup' => '

    <div class="multiple-select-account-mobile"
    id="multiple-select-account-mobile">
    <ul>
    <li ng-repeat="item in suggestionsAccountMobile">
    <input type="checkbox" id="{[{item.name}]}"
    ng-model="checked"
    ng-click="filtersChangeMobile(account,checked, $event)"/>
    <label
    for="{[{item.account}]}">{[{item.name}]}</label>
    </li>
    </ul>
    </div>'
    ];

    $form['container']['suggestions_accounts'] = ['#markup' => '
    <div id="suggestions123" class="collection" style="border: none;">
    <div id="suggestionsAccount">
    <ul>
    <li ng-repeat="item in suggestionsAccount">
    <span>
    <input type="checkbox" id="check-{[{item.account}]}" value="{[{item.account}]}" ng-model="checked" ng-click="filtersChangeMobile(account, checked, $event)"/>
    <label for="check-{[{item.account}]}">{[{item.account}]}</label>
    </span>
    </li>
    </ul>
    </div>
    <div class="chip" ng-repeat="key in accountOptions track by key">
    {[{ key }]}
    <span class="closebtn" ng-click="removeChipAccount({[{ key }]})">×</span>
    </div>
    </div>'];*/

    $form['associated_accounts_value'] = [
      '#type' => 'textfield',
      '#maxlength' => 12800,
      '#attributes' => [
        'value' => ['{[{ enter_value}]}'],
      ],
    ];

    $form['button-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-wrapper-button', 'form-item'],
      ],
    ];

    $form['button-wrapper']['closet'] = [
      '#markup' => '<a href="#" data-ng-click="usersListClear()" class="modal-action modal-close create-account waves-effect waves-light btn btn-second">Cancelar</a>',
    ];

    $form['button-wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Guardar'),
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
      ],
    ];

    $form['#attributes']['class'] = ['create-group'];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'tbo_general/tools.tbo';
    // $form['#attached']['library'][] = 'tbo_groups/groups-list';.
    return $form;
  }

  /**
   * @return array
   */
  private function getAdministrators() {

    $database = \Drupal::database();
    $query = $database->select('company_user_relations_field_data', 'company_user_relations');
    // $query->innerJoin('group_user_relations_field_data', 'groupUser', 'groupUser.group_id = group.id');.
    $query->innerJoin('user__roles', 'user_roles', 'user_roles.entity_id = company_user_relations.users');
    $query->innerJoin('users_field_data', 'user', 'user.uid = company_user_relations.users');

    $query->addField('company_user_relations', 'users', 'id');
    $query->addField('user', 'name');

    kint($_SESSION);
    $query->condition('company_user_relations.name', '%' . $_SESSION['company']['name'] . '%', 'LIKE');
    $query->condition('user_roles.roles_target_id', 'admin_group', '=');

    $result = $query->execute()->fetchAll();

    $a = [];
    foreach ($result as $user) {
      $a[$user->id] = $user->name;
    }

    return $a;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $is_new = $form_state->getValue('is_new');
    if ($is_new) {
      $name_group = db_query_range('SELECT id FROM {{group_entity_field_data}} WHERE name = :name', 0, 1, [':name' => $form_state->getValue('name')])->fetchField();
      if ($name_group) {
        $form_state->setErrorByName('name', t('El nombre del grupo ya se encuentra registrado en el sistema'));
      }
    }

    if (strlen($form_state->getValue('name')) < 3) {
      $form_state->setErrorByName('name', t('El nombre del grupo debe tener más de tres caracteres'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $log = AuditLogEntity::create();
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

    $is_new = $form_state->getValue('is_new');

    // CREATE GROUP.
    if ($is_new === '') {
      try {
        $group = GroupEntity::create([
          'name' => $form_state->getValue('name'),
          'administrator' => $form_state->getValue('administrator'),
          'status' => TRUE,
        ]);

        $group->save();

        $this->saveGroupAccountRelations($group, $form_state->getValue('associated_accounts_value'));

        // Create Audit log.
        $log->set('created', time());
        $log->set('company_name', $_SESSION['company']['name']);
        $log->set('user_id', $uid);
        $log->set('user_names', $name);
        $log->set('user_role', $account->get('roles')
          ->getValue()[0]['target_id']);
        $log->set('event_type', 'Cuenta');
        $log->set('description', 'Usuario crea el grupo ' . $form_state->getValue('name'));
      }
      catch (\Exception $exception) {
        drupal_set_message('Error al crear grupo, por favor verifique los datos: ' . $exception->getMessage(), 'error');
        $current_path = \Drupal::service('path.current')->getPath();
        $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
      }

      drupal_set_message('El grupo ' . $form_state->getValue('name') . ' se ha creado exitosamente.');
    }
    // UPDATE GROUP.
    else {
      try {

        $group = GroupEntity::load($form_state->getValue('gid_update'));

        $group->setName($form_state->getValue('name'));
        $group->setAdministrator($form_state->getValue('administrator'));

        // Save edit group accoun relations.
        $group->save();

        // Create Audit log.
        $log->set('created', time());
        $log->set('company_name', $_SESSION['company']['name']);
        $log->set('user_id', $uid);
        $log->set('user_names', $name);
        $log->set('user_role', $account->get('roles')
          ->getValue()[0]['target_id']);
        $log->set('event_type', 'Cuenta');
        $log->set('description', 'Usuario edita el grupo ' . $form_state->getValue('name'));
      }
      catch (\Exception $exception) {
        drupal_set_message('Error al crear grupo, por favor verifique los datos: ' . $exception->getMessage(), 'error');
        $current_path = \Drupal::service('path.current')->getPath();
        $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
      }

      drupal_set_message('El grupo ' . $form_state->getValue('name') . ' ha sido editado.');
    }

    $associated = NULL;
    $log->save();
    $current_path = \Drupal::service('path.current')->getPath();
    $form_state->setRedirectUrl(Url::fromUri('internal:/' . $current_path));
  }

  /**
   * {@inheritdoc}
   */
  private function saveGroupAccountRelations($group, $associated_accounts) {

    $associated_accounts = \GuzzleHttp\json_decode($associated_accounts);

    foreach ($associated_accounts as $account) {
      if (!is_null($account)) {
        $associated_account = GroupAccountRelations::create([
          'group_id' => $group->id(),
          'account' => $account->account,
          'status' => TRUE,
        ]);

        $associated_account->save();
      }
    }

    // die;.
  }

}
