<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Company entity entity.
 *
 * @ingroup tbo_entities
 *
 * @ContentEntityType(
 *   id = "company_entity",
 *   label = @Translation("Company entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_entities\CompanyEntityListBuilder",
 *     "views_data" = "Drupal\tbo_entities\Entity\CompanyEntityViewsData",
 *     "translation" = "Drupal\tbo_entities\CompanyEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\tbo_entities\Form\CompanyEntityForm",
 *       "add" = "Drupal\tbo_entities\Form\CompanyEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\CompanyEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\CompanyEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tbo_entities\CompanyEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\CompanyEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "company_entity",
 *   data_table = "company_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer company entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/company_entity/{company_entity}",
 *     "add-form" = "/admin/structure/company_entity/add",
 *     "edit-form" = "/admin/structure/company_entity/{company_entity}/edit",
 *     "delete-form" = "/admin/structure/company_entity/{company_entity}/delete",
 *     "collection" = "/admin/structure/company_entity",
 *   },
 *   field_ui_base_route = "company_entity.settings"
 * )
 */
class CompanyEntity extends ContentEntityBase implements CompanyEntityInterface {

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
  public function getDocumentNumber() {
    return $this->get('document_number')->value;
  }

  /**
   *
   */
  public function setDocumentNumber($document_number) {
    $this->set('document_number', $document_number);
  }

  /**
   *
   */
  public function getCompanyName() {
    return $this->get('company_name')->value;
  }

  /**
   *
   */
  public function setCompanyName($company_name) {
    $this->set('company_name', $company_name);
  }

  /**
   *
   */
  public function getCompanySegment() {
    return $this->get('segment')->value;
  }

  /**
   *
   */
  public function setCompanySegment($company_segment) {
    $this->set('segment', $company_segment);
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
      ->setLabel(t('Name'))
      ->setDescription(t('Machine name of the Invitation access entity.'))
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

    $fields['document_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tipo de documento'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'document_type_entity')
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

    $fields['document_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Número de documento'))
      ->setDescription(t('Número de documento.'))
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

    $fields['company_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Empresa'))
      ->setDescription(t('Nombre de la empresa.'))
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

    $fields['segment'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Segmento'))
      ->setDescription(t('Segmento de la empresa.'))
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

    $fields['fixed'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Empresa fija'))
      ->setDescription(t('Permite guardar si la empresa pertence a los servicios fijos.'))
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

    $fields['mobile'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Empresa movil'))
      ->setDescription(t('Permite guardar si la empresa pertence a los servicios moviles.'))
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
