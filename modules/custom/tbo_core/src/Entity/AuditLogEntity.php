<?php

namespace Drupal\tbo_core\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Audit log entity entity.
 *
 * @ingroup tbo_core
 *
 * @ContentEntityType(
 *   id = "audit_log_entity",
 *   label = @Translation("Audit log entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_core\AuditLogEntityListBuilder",
 *     "views_data" = "Drupal\tbo_core\Entity\AuditLogEntityViewsData",
 *     "translation" = "Drupal\tbo_core\AuditLogEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\tbo_core\Form\AuditLogEntityForm",
 *       "add" = "Drupal\tbo_core\Form\AuditLogEntityForm",
 *       "edit" = "Drupal\tbo_core\Form\AuditLogEntityForm",
 *       "delete" = "Drupal\tbo_core\Form\AuditLogEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tbo_core\AuditLogEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_core\AuditLogEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "audit_log_entity",
 *   data_table = "audit_log_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer audit log entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/audit_log_entity/{audit_log_entity}",
 *     "add-form" = "/admin/structure/audit_log_entity/add",
 *     "edit-form" = "/admin/structure/audit_log_entity/{audit_log_entity}/edit",
 *     "delete-form" = "/admin/structure/audit_log_entity/{audit_log_entity}/delete",
 *     "collection" = "/admin/structure/audit_log_entity",
 *   },
 *   field_ui_base_route = "audit_log_entity.settings"
 * )
 */
class AuditLogEntity extends ContentEntityBase implements AuditLogEntityInterface {

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
  public function getUserNames() {
    return $this->get('user_names')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUserNames($user_names) {
    $this->set('user_names', $user_names);
  }

  /**
   * {@inheritdoc}
   */
  public function getCompanyName() {
    return $this->get('company_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCompanyName($company_name) {
    $this->set('company_name', $company_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCompanyDocumentNumber() {
    return $this->get('company_document_number')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCompanyDocumentNumber($company_document_number) {
    $this->set('company_document_number', $company_document_number);
  }

  /**
   * {@inheritdoc}
   */
  public function getCompanySegment() {
    return $this->get('company_segment')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCompanySegment($company_segment) {
    $this->set('company_segment', $company_segment);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserRole() {
    return $this->get('user_role')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUserRole($user_role) {
    $this->set('user_role', $user_role);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getDetails() {
    return $this->get('details')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDetails($details) {
    $this->set('details', $details);
  }

  /**
   * {@inheritdoc}
   */
  public function getEventType() {
    return $this->get('event_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEventType($event) {
    $this->set('event_type', $event);
  }

  /**
   * {@inheritdoc}
   */
  public function getOldValues() {
    return $this->get('old_values')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setOldValues($old_values) {
    $this->set('old_values', $old_values);
  }

  /**
   * {@inheritdoc}
   */
  public function getNewValues() {
    return $this->get('new_values')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewValues($new_values) {
    $this->set('new_values', $new_values);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['user_names'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Nombres'))
      ->setDescription(t('Nombres del usuario que realiza la acción.'))
      ->setSettings([
        'max_length' => 130,
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

    $fields['company_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Empresa'))
      ->setDescription(t('Nombre de la empresa que realiza la acción.'))
      ->setSettings([
        'max_length' => 130,
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

    $fields['company_document_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Nit'))
      ->setDescription(t('Nit de la empresa que realiza la acción.'))
      ->setSettings([
        'max_length' => 130,
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

    $fields['company_segment'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Segmento'))
      ->setDescription(t('Segmento al que pertenece la empresa.'))
      ->setSettings([
        'max_length' => 130,
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

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Id del usuario que realiza la acción'))
      ->setDescription(t('The user ID of author of the Log entity entity.'))
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

    $fields['user_role'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Rol'))
      ->setDescription(t('Rol(es) al que pertenece el usuario.'))
      ->setSettings([
        'max_length' => 130,
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
      ->setLabel(t('Descripción'))
      ->setDescription(t('Descripción de la acción realizada.'))
      ->setSettings([
        'max_length' => 300,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string_textarea',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['event_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tipo de evento'))
      ->setDescription(t('Tipo de evento que se esta realizando.'))
      ->setSettings([
        'max_length' => 30,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['details'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Detalle'))
      ->setDescription(t('Detalle de la acción realizada.'))
      ->setSettings([
        'max_length' => 350,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['old_values'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Valores anteriores'))
      ->setDescription(t('Valores antes de realizar la acción.'))
      ->setSettings([
        'max_length' => 130,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['new_values'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Valores nuevos'))
      ->setDescription(t('Valores despúes de la acción.'))
      ->setSettings([
        'max_length' => 130,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['error_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Error Code'))
      ->setDescription(t('Codigo del error.'))
      ->setSettings([
        'max_length' => 30,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['error_message'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Error Message'))
      ->setDescription(t('Mensaje de error.'))
      ->setSettings([
        'max_length' => 500,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['technical_detail'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Detalle técnico:'))
      ->setDescription(t('Resultado del o de los servicios invocados en la transacción.'))
      ->setSettings([
        'max_length' => 500,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Log entity is published.'))
      ->setDefaultValue(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
