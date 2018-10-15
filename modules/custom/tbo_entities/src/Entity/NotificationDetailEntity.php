<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Notification detail entity entity.
 *
 * @ingroup tbo_entities
 *
 * @ContentEntityType(
 *   id = "notification_detail_entity",
 *   label = @Translation("Notification detail entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_entities\NotificationDetailEntityListBuilder",
 *     "views_data" = "Drupal\tbo_entities\Entity\NotificationDetailEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\tbo_entities\Form\NotificationDetailEntityForm",
 *       "add" = "Drupal\tbo_entities\Form\NotificationDetailEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\NotificationDetailEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\NotificationDetailEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tbo_entities\NotificationDetailEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\NotificationDetailEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "notification_detail_entity",
 *   admin_permission = "administer notification detail entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "notification" = "notification_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/notification_detail_entity/{notification_detail_entity}",
 *     "add-form" = "/admin/structure/notification_detail_entity/add",
 *     "edit-form" = "/admin/structure/notification_detail_entity/{notification_detail_entity}/edit",
 *     "delete-form" = "/admin/structure/notification_detail_entity/{notification_detail_entity}/delete",
 *     "collection" = "/admin/structure/notification_detail_entity",
 *   },
 *   field_ui_base_route = "notification_detail_entity.settings"
 * )
 */
class NotificationDetailEntity extends ContentEntityBase implements NotificationDetailEntityInterface {

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
      ->setDescription(t('The user ID of author of the Notification detail entity entity.'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['notification_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Notification detail entity entity.'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['pending'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Pendiente'))
      ->setDescription(t('Verificar si el usuario aun tiene pendiente la notificación. Aplica para valiar si el usuario ya valido su cuenta'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
