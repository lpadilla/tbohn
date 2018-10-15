<?php

namespace Drupal\adf_import_data\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Log import data entity entity.
 *
 * @ingroup adf_import_data
 *
 * @ContentEntityType(
 *   id = "log_import_data_entity",
 *   label = @Translation("Log import data entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\adf_import_data\LogImportDataEntityListBuilder",
 *     "views_data" = "Drupal\adf_import_data\Entity\LogImportDataEntityViewsData",
 *     "translation" = "Drupal\adf_import_data\LogImportDataEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\adf_import_data\Form\LogImportDataEntityForm",
 *       "add" = "Drupal\adf_import_data\Form\LogImportDataEntityForm",
 *       "edit" = "Drupal\adf_import_data\Form\LogImportDataEntityForm",
 *       "delete" = "Drupal\adf_import_data\Form\LogImportDataEntityDeleteForm",
 *     },
 *     "access" = "Drupal\adf_import_data\LogImportDataEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\adf_import_data\LogImportDataEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "log_import_data_entity",
 *   data_table = "log_import_data_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer log import data entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/log_import_data_entity/{log_import_data_entity}",
 *     "add-form" = "/admin/structure/log_import_data_entity/add",
 *     "edit-form" = "/admin/structure/log_import_data_entity/{log_import_data_entity}/edit",
 *     "delete-form" = "/admin/structure/log_import_data_entity/{log_import_data_entity}/delete",
 *     "collection" = "/admin/structure/log_import_data_entity",
 *   },
 *   field_ui_base_route = "log_import_data_entity.settings"
 * )
 */
class LogImportDataEntity extends ContentEntityBase implements LogImportDataEntityInterface {

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
   *
   */
  public function setCustomId($custom_id) {
    $this->set('custom_id', $custom_id);
    return $this;
  }

  /**
   *
   */
  public function getCustomId() {
    return $this->get('custom_id')->value;
  }

  /**
   *
   */
  public function setStatusImport($status_import) {
    $this->set('status_import', $status_import);
    return $this;
  }

  /**
   *
   */
  public function getStatusImport() {
    return $this->get('status_import')->value;
  }

  /**
   *
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   *
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['custom_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Id empresa'))
      ->setDescription(t('Identificador de la empresa'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 150,
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

    $fields['status_import'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Estado de importación'))
      ->setDescription(t('Estado de la importación'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 20,
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
    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Descripción del estado'))
      ->setDescription('')
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 800,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string_textarea',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Log import data entity entity.'))
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
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Log import data entity entity.'))
      ->setSettings([
        'max_length' => 50,
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

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Log import data entity is published.'))
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
