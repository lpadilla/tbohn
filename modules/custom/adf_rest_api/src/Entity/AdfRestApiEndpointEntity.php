<?php

namespace Drupal\adf_rest_api\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Adf rest api endpoint entity entity.
 *
 * @ConfigEntityType(
 *   id = "adf_rest_api_endpoint_entity",
 *   label = @Translation("Adf rest api endpoint entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\adf_rest_api\AdfRestApiEndpointEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\adf_rest_api\Form\AdfRestApiEndpointEntityForm",
 *       "edit" = "Drupal\adf_rest_api\Form\AdfRestApiEndpointEntityForm",
 *       "delete" = "Drupal\adf_rest_api\Form\AdfRestApiEndpointEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\adf_rest_api\AdfRestApiEndpointEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "adf_rest_api_endpoint_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/adf_rest_api_endpoint_entity/{adf_rest_api_endpoint_entity}",
 *     "add-form" = "/admin/structure/adf_rest_api_endpoint_entity/add",
 *     "edit-form" = "/admin/structure/adf_rest_api_endpoint_entity/{adf_rest_api_endpoint_entity}/edit",
 *     "delete-form" = "/admin/structure/adf_rest_api_endpoint_entity/{adf_rest_api_endpoint_entity}/delete",
 *     "collection" = "/admin/structure/adf_rest_api_endpoint_entity"
 *   }
 * )
 */
class AdfRestApiEndpointEntity extends ConfigEntityBase implements AdfRestApiEndpointEntityInterface {

  /**
   * The Adf rest api endpoint entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Adf rest api endpoint entity label.
   *
   * @var string
   */
  protected $label;

}
