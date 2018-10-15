<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Company user relations entity.
 *
 * @ingroup tbo_entities
 *
 * @ContentEntityType(
 *   id = "company_user_relations",
 *   label = @Translation("Company user relations"),
 *   handlers = {
 *     "storage" = "Drupal\tbo_entities\CompanyUserRelationsStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_entities\CompanyUserRelationsListBuilder",
 *     "views_data" = "Drupal\tbo_entities\Entity\CompanyUserRelationsViewsData",
 *     "translation" = "Drupal\tbo_entities\CompanyUserRelationsTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\tbo_entities\Form\CompanyUserRelationsForm",
 *       "add" = "Drupal\tbo_entities\Form\CompanyUserRelationsForm",
 *       "edit" = "Drupal\tbo_entities\Form\CompanyUserRelationsForm",
 *       "delete" = "Drupal\tbo_entities\Form\CompanyUserRelationsDeleteForm",
 *     },
 *     "access" = "Drupal\tbo_entities\CompanyUserRelationsAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\CompanyUserRelationsHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "company_user_relations",
 *   data_table = "company_user_relations_field_data",
 *   revision_table = "company_user_relations_revision",
 *   revision_data_table = "company_user_relations_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer company user relations entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/company_user_relations/{company_user_relations}",
 *     "add-form" = "/admin/structure/company_user_relations/add",
 *     "edit-form" = "/admin/structure/company_user_relations/{company_user_relations}/edit",
 *     "delete-form" = "/admin/structure/company_user_relations/{company_user_relations}/delete",
 *     "version-history" = "/admin/structure/company_user_relations/{company_user_relations}/revisions",
 *     "revision" = "/admin/structure/company_user_relations/{company_user_relations}/revisions/{company_user_relations_revision}/view",
 *     "revision_revert" = "/admin/structure/company_user_relations/{company_user_relations}/revisions/{company_user_relations_revision}/revert",
 *     "translation_revert" = "/admin/structure/company_user_relations/{company_user_relations}/revisions/{company_user_relations_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/company_user_relations/{company_user_relations}/revisions/{company_user_relations_revision}/delete",
 *     "collection" = "/admin/structure/company_user_relations",
 *   },
 *   field_ui_base_route = "company_user_relations.settings"
 * )
 */
class CompanyUserRelations extends RevisionableContentEntityBase implements CompanyUserRelationsInterface {

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

    $fields['associated_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tigo Admin'))
      ->setDescription(t('Id del usuario tigo admin de la entidad.'))
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
      ->setLabel(t('Machine name'))
      ->setDescription(t('Nombre maquina de la entidad.'))
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

    $fields['users'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Usuarios asociados a la empresa'))
      ->setDescription(t('Usuario asociado a la empresa'))
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

    $fields['company_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Empresa'))
      ->setDescription(t('Id de la empresa.'))
      ->setSetting('target_type', 'company_entity')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Company user role entity is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    if ($rel === 'revision_revert' && $this instanceof RevisionableContentEntityBase) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableContentEntityBase) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    return $uri_route_parameters;
  }

}
