<?php

namespace Drupal\tbo_groups\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Group entity entity.
 *
 * @ingroup tbo_groups
 *
 * @ContentEntityType(
 *   id = "group_entity",
 *   label = @Translation("Group entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_groups\GroupEntityListBuilder",
 *     "views_data" = "Drupal\tbo_groups\Entity\GroupEntityViewsData",
 *     "translation" = "Drupal\tbo_groups\GroupEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\tbo_groups\Form\GroupEntityForm",
 *       "add" = "Drupal\tbo_groups\Form\GroupEntityForm",
 *       "edit" = "Drupal\tbo_groups\Form\GroupEntityForm",
 *       "delete" = "Drupal\tbo_groups\Form\GroupEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tbo_groups\GroupEntityAccessControlHandler",
 *   },
 *   base_table = "group_entity",
 *   data_table = "group_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer group entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/group_entity/{group_entity}",
 *     "add-form" = "/admin/structure/group_entity/add",
 *     "edit-form" = "/admin/structure/group_entity/{group_entity}/edit",
 *     "delete-form" = "/admin/structure/group_entity/{group_entity}/delete",
 *     "collection" = "/admin/structure/group_entity",
 *   },
 *   field_ui_base_route = "group_entity.settings"
 * )
 */
class GroupEntity extends ContentEntityBase implements GroupEntityInterface {

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
  public function getAssociatedAccounts() {
    return $this->get('associated_accounts')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAssociatedAccounts($associated_accounts) {
    $this->set('associated_accounts', $associated_accounts);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdministrator() {
    return $this->get('administrator')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAdministrator($administrator) {
    $this->set('administrator', $administrator);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    /**
     * content entities at minimum need to set user_id(author) and name(machine_name) fields
     */
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Id del creador'))
      ->setDescription(t('El identificador del usuario que realiza el cambio.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Nombre'))
      ->setDescription(t('Machine name of the Name access entity.'))
      ->setSettings([
        'max_length' => 250,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['administrator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Administrador'))
      ->setDescription(t('Administrador.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Activo'))
      ->setDescription(t('Permite guardar el estado de la empresa activo-inactivo.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
