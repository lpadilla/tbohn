<?php

namespace Drupal\tbo_entities\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Notification entity edit forms.
 *
 * @ingroup tbo_entities
 */
class NotificationEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tbo_entities\Entity\NotificationEntity */
    $form = parent::buildForm($form, $form_state);
    unset($form['roles']['widget']['#options']['anonymous']);
    unset($form['roles']['widget']['#options']['administrator']);

    $entity = $this->entity;
    $type_user = $entity->get('type_user')->getValue();
    $type_user_code = 0;
    if (is_array($type_user)) {
      $type_user_code = $type_user[0]['value'];
    }
    $form['type_user'] = [
      '#type' => 'select',
      '#title' => $this
        ->t('Tipos de usuarios'),
      '#options' => [
        0 => 'Nuevos',
        1 => 'Antiguos',
        2 => 'Todos',
      ],
      '#default_value' => $type_user_code,
      '#weight' => 12,
      '#description' => t('Seleccione los usarios a los que se le enviara la notificación.'),
    ];

    $type_notification = $entity->get('notification_type')->getValue();
    $type_notification_code = 0;
    if (is_array($type_notification)) {
      $type_notification_code = $type_notification[0]['value'];
    }
    $form['notification_type'] = [
      '#type' => 'select',
      '#title' => $this
        ->t('Tipos de notificación'),
      '#options' => [
        0 => 'Verificar cuenta',
        1 => 'Actualización de datos',
        2 => 'Otro',
      ],
      '#default_value' => $type_notification_code,
      '#weight' => 13,
      '#description' => t(
        'Seleccione el tipo de notificación. Tenga en cuenta que esta opcion puede sobreescribir algunas opciones señaladas anteriormente, tomando como base las reglas del negocio definidas en el Control de cambios.'),
    ];

    $text_format = $entity->get('button_url')->getValue();
    if (empty($text_format[0])) {
      $form['text_notification']['widget'][0]['#format'] = 'full_html';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate if exist update user data.
    $entity = $this->buildEntity($form, $form_state);
    // 0 => 'Verificar cuenta', 1 => 'Actualización de datos', 2 => 'Otro'.
    $type = $form_state->getValue('notification_type');
    if ($entity->isNew() && $type == 1) {
      // Get Notification entity type Update user data = 1.
      $repository = \Drupal::service('tbo_services.tbo_services_repository');
      $get_update = $repository->getNotifications([], 1);
      if (!empty($get_update)) {
        $entity->setValidationRequired(FALSE);
        $form_state->setTemporaryValue('entity_validated', TRUE);
        $field = ['notification_type'];
        $form_state->setError($field, t("Ya existe una notificación de tipo actualización de datos."));
        return $entity;
      }
    }

    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Validate type user and get last user.
    // 0 => 'Nuevos', 1 => 'Antiguos', 2 => 'Todos'.
    $last_user = (int) $form_state->getValue('type_user');
    $last_id = TRUE;
    if ($last_user == 2) {
      $last_id = FALSE;
    }

    $roles = $form_state->getValue('roles');
    $roles_array = [];
    foreach ($roles as $key_role => $value_roles) {
      if ($value_roles['target_id'] != 'authenticated') {
        array_push($roles_array, $value_roles['target_id']);
      }
    }
    $repository = \Drupal::service('tbo_services.tbo_services_repository');
    $result = $repository->getUsersByFilterToNotification($roles_array, $last_id);

    if ($last_id) {
      $entity->set('id_last_user', $result['last_id']);
    }

    $all_users = $result['count'];
    if ($last_user == 0) {
      $all_users = 0;
    }
    $entity->set('send_quantity', $all_users);
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Notification entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Notification entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.notification_entity.collection');
  }

}
