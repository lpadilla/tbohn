<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Tipo de documento entity.
 *
 * @ConfigEntityType(
 *   id = "document_type_entity",
 *   label = @Translation("Tipo de documento"),
 *   handlers = {
 *     "list_builder" = "Drupal\tbo_entities\DocumentTypeEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\tbo_entities\Form\DocumentTypeEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\DocumentTypeEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\DocumentTypeEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\DocumentTypeEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "document_type_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/document_type_entity/{document_type_entity}",
 *     "add-form" = "/admin/structure/document_type_entity/add",
 *     "edit-form" = "/admin/structure/document_type_entity/{document_type_entity}/edit",
 *     "delete-form" = "/admin/structure/document_type_entity/{document_type_entity}/delete",
 *     "collection" = "/admin/structure/document_type_entity"
 *   }
 * )
 */
class DocumentTypeEntity extends ConfigEntityBase implements DocumentTypeEntityInterface {

  /**
   * The Tipo de documento ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Tipo de documento label.
   *
   * @var string
   */
  protected $label;

  /**
   * @return mixed
   */
  public function getAbbDocType() {
    return $this->get('abbreviated_label')->value;
  }

  /**
   * @param $abbreviated_label
   * @return $this
   */
  public function setAbbDocType($abbreviated_label) {
    $this->set('abbreviated_label', $abbreviated_label);
    return $this;
  }

}
