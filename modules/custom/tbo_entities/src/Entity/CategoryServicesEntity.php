<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Categoria de servicios entity.
 *
 * @ConfigEntityType(
 *   id = "category_services_entity",
 *   label = @Translation("Categoria de servicios"),
 *   handlers = {
 *     "list_builder" = "Drupal\tbo_entities\CategoryServicesEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\tbo_entities\Form\CategoryServicesEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\CategoryServicesEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\CategoryServicesEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\CategoryServicesEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "category_services_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "icon" = "icon",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/category_services_entity/{category_services_entity}",
 *     "add-form" = "/admin/structure/category_services_entity/add",
 *     "edit-form" = "/admin/structure/category_services_entity/{category_services_entity}/edit",
 *     "delete-form" = "/admin/structure/category_services_entity/{category_services_entity}/delete",
 *     "collection" = "/admin/structure/category_services_entity"
 *   }
 * )
 */
class CategoryServicesEntity extends ConfigEntityBase implements CategoryServicesEntityInterface {

  /**
   * The Categi ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Categoria de servicios label.
   *
   * @var string
   */
  protected $label;

  protected $icon;
  protected $url;
  protected $type_category;
  protected $parameter;
  protected $invitation_popup;

  /**
   * @return mixed
   */
  public function getValues() {
    return [
      'label'                 => $this->get('label'),
      'icon'                  => $this->icon,
      'url'                   => $this->url,
      'type_category'         => $this->type_category,
      'parameter'             => $this->parameter,
      'invitation_popup'      => $this->invitation_popup,
    ];
  }

  /**
   * @return mixed
   */
  public function getIcon() {
    return $this->icon;
  }

  /**
   * @param $icon
   * @return $this
   */
  public function setIcon($icon) {
    $this->set('icon', $icon);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @param $url
   * @return $this
   */
  public function setUrl($url) {
    $this->set('url', $url);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getInvitationPopup() {
    return $this->invitation_popup;
  }

  /**
   * @param $invitation_popup
   * @return $this
   */
  public function setInvitationPopup($invitation_popup) {
    $this->set('invitation_popup', $invitation_popup);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getTypeCategory() {
    return $this->type_category;
  }

  /**
   * @param $abbreviated_label
   * @return $this
   */
  public function setTypeCategory($type_category) {
    $this->set('type_category', $type_category);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getParameter() {
    return $this->parameter;
  }

  /**
   * @param $parameter
   * @return $this
   */
  public function setParameter($parameter) {
    $this->set('parameter', $parameter);
    return $this;
  }

}
