<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Cache\Cache;

/**
 * Defines the Notification entity entity.
 *
 * @ingroup tbo_entities
 *
 * @ContentEntityType(
 *   id = "notification_entity",
 *   label = @Translation("Notification entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_entities\NotificationEntityListBuilder",
 *     "views_data" = "Drupal\tbo_entities\Entity\NotificationEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\tbo_entities\Form\NotificationEntityForm",
 *       "add" = "Drupal\tbo_entities\Form\NotificationEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\NotificationEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\NotificationEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tbo_entities\NotificationEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\NotificationEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "notification_entity",
 *   admin_permission = "administer notification entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/notification_entity/{notification_entity}",
 *     "add-form" = "/admin/structure/notification_entity/add",
 *     "edit-form" = "/admin/structure/notification_entity/{notification_entity}/edit",
 *     "delete-form" = "/admin/structure/notification_entity/{notification_entity}/delete",
 *     "collection" = "/admin/structure/notification_entity",
 *   },
 *   field_ui_base_route = "notification_entity.settings"
 * )
 */
class NotificationEntity extends ContentEntityBase implements NotificationEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Notification entity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\tbo_entities\Entity\NotificationEntity::getCurrentUserId')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Estado de la notificación'))
      ->setDescription(t('Solo se mostrara la notificación a los usuarios cuando este campo este seleccionado.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'checkbox',
        'weight' => -1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('Nombre de la notificación.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['text_notification'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Texto'))
      ->setDescription(t('Texto o cuerpo de la notificacion.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 1,
        'format' => 'full_html',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['button_text'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Texto del boton'))
      ->setDescription(t('Texto del boton.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['button_url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Url del boton'))
      ->setDescription(t('Url del boton. example /validar-mi-notificacion'))
      ->setSettings([
        'max_length' => 200,
        'text_processing' => 0,
      ])
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['button_target'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Abrir en nueva pestaña'))
      ->setDescription(t('seleccione si desea que la notificación se abra en una nueva pestaña.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'checkbox',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['button_show'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Mostrar boton'))
      ->setDescription(t('Seleccion para mostrar el boton.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'checkbox',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['button_validate'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Remover al hacer click'))
      ->setDescription(t('Seleccione para remover la notificacion del usuario una vez hace click en el boton, solo aplica para notificaciones que en el tipo de notificación esta marcado como Otro.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'checkbox',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $roles = array_map(['\Drupal\Component\Utility\Html', 'escape'], user_role_names(TRUE));

    $fields['roles'] = BaseFieldDefinition::create('entity_reference')
      ->setSetting('allowed_values', $roles)
      ->setLabel(t('Roles'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDescription(t('Los roles a los que se debe enviar la notificación.'))
      ->setSetting('target_type', 'user_role')
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => 9
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['send_quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Cantidad de envios'))
      ->setDescription(t('Cantidad de envios.'))
      ->setRequired(TRUE)
      ->setDefaultValue(0)
      ->setSettings([
        'max_length' => 20,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'integer',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['accepted_quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Cantidad realizada'))
      ->setDescription(t('Cantidad de usuarios que han realizado la notificación.'))
      ->setRequired(TRUE)
      ->setDefaultValue(0)
      ->setSettings([
        'max_length' => 20,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'integer',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['type_user'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tipo de usuario'))
      ->setDescription(t('Seleccione el tipo de los usarios a los que se le enviara la notificación.'))
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['id_last_user'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ultimo usuario'))
      ->setDescription(t('Ultimo usuario como base para los usuarios nuevos y antiguos.'))
      ->setRequired(TRUE)
      ->setDefaultValue(0)
      ->setSettings([
        'max_length' => 20,
        'text_processing' => 0,
      ]);

    $fields['notification_type'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tipo de notificación'))
      ->setDescription(t('Seleccione el tipo de notificación.'))
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Orden de la notificación'))
      ->setDescription(t('Indique en que posición se debe cargar esta notificación.'))
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setSettings([
        'min' => 1,
        'max' => 100,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'integer',
        'weight' => 12,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 12,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * @return array
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    //print "voy a ejecutar el job app:send-video:vimeo";
    //$jobUploadVideo = new Job('entity:notification-detail:create', [$this->id()]);
    // Entity::postSave() calls Entity::invalidateTagsOnSave(), which only
    // handles the regular cases. The Block entity has one special case: a
    // newly created block may *also* appear on any page in the current theme,
    // so we must invalidate the associated block's cache tag (which includes
    // the theme cache tag).
    if (!$update) {
      Cache::invalidateTags($this
        ->getCacheTagsToInvalidate());
    }

    // Save audit log.
    // 0 => 'Verificar cuenta', 1 => 'Actualización de datos', 2 => 'Otro'.
    $get_type = $this->get('notification_type')->getValue()[0]['value'];

    if ($get_type == 1) {
      $action = 'generado';
      if ($update) {
        $action = 'modificado';
      }

      $roles_string = '';
      $roles = $this->get('roles')->getValue();
      $system_roles = user_roles();
      foreach ($roles as $key => $rol) {
        $roles_string .= $system_roles[$rol['target_id']]->label() . ', ';
      }
      $roles_string = substr($roles_string, 0, -2);
      $service = \Drupal::service('tbo_core.audit_log_service');
      $service->loadName();
      // Create array data_log.
      $data_log = [
        'companyName' => isset($_SESSION['company']['name']) ? $_SESSION['company']['name'] : '',
        'companyDocument' => isset($_SESSION['company']['nit']) ? $_SESSION['company']['nit'] : '',
        'companySegment' => isset($_SESSION['company']['segment']) ? $_SESSION['company']['segment'] : '',
        'event_type' => 'Cuenta',
        'description' => t('Usuario genera alertas masivas'),
        'details' => t('Usuario @userName ha @action alerta masiva de invitación de datos para todos los usuarios @roles del sitio',
          [
            '@userName' => $service->getName(),
            '@action' => $action,
            '@roles' => $roles_string,
          ]
        ),
        'old_value' => 'No disponible',
        'new_value' => 'No disponible',
      ];

      // Save audit log.
      $service->insertGenericLog($data_log);
    }
  }

  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    // Delete detail entity.
    $entity_type_id = $storage->getEntityTypeId();

    if ($entity_type_id == 'notification_entity') {
      $repository = \Drupal::service('tbo_services.tbo_services_repository');
      foreach ($entities as $key => $value) {
        // Delete relation.
        $deleteRelation = $repository->deleteRelationNotificationDetail($key);
      }
    }
  }

}
