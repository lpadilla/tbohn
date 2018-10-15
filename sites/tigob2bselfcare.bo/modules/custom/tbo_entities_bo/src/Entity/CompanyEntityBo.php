<?php

namespace Drupal\tbo_entities_bo\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\tbo_entities\Entity\CompanyEntity;
use Drupal\tbo_entities\Entity\CompanyEntityInterface;

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
class CompanyEntityBo extends CompanyEntity implements CompanyEntityInterface {
  
  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
  
    /**
     * content entities at minimum need to set user_id(author) and name(machine_name) fields
     */

      $fields['client_code'] = BaseFieldDefinition::create('string')
       ->setLabel(t('Código de Cliente'))
       ->setDescription(t('Código de Cliente.'))
       ->setSettings(array(
           'max_length' => 850,
           'text_processing' => 0,
        ))
       ->setDefaultValue('')
       ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'string',
            'weight' => -4,
           ))
       ->setDisplayOptions('form', array(
           'type' => 'string_textfield',
           'weight' => -4,
           ))
       ->setDisplayConfigurable('form', TRUE)
       ->setDisplayConfigurable('view', TRUE);
      
    return $fields;
  }

}
