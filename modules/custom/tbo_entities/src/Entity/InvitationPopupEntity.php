<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Invitación en Popup entity.
 *
 * @ConfigEntityType(
 *   id = "invitation_popup_entity",
 *   label = @Translation("Invitación en Popup"),
 *   handlers = {
 *     "list_builder" = "Drupal\tbo_entities\InvitationPopupEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\tbo_entities\Form\InvitationPopupEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\InvitationPopupEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\InvitationPopupEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\InvitationPopupEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "invitation_popup_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/invitation_popup_entity/{invitation_popup_entity}",
 *     "add-form" = "/admin/structure/invitation_popup_entity/add",
 *     "edit-form" = "/admin/structure/invitation_popup_entity/{invitation_popup_entity}/edit",
 *     "delete-form" = "/admin/structure/invitation_popup_entity/{invitation_popup_entity}/delete",
 *     "collection" = "/admin/structure/invitation_popup_entity"
 *   }
 * )
 */
class InvitationPopupEntity extends ConfigEntityBase implements InvitationPopupEntityInterface {

  /**
   * The Categi ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Invitación en Popup label.
   *
   * @var string
   */
  protected $label;

  protected $icon;
  protected $description;
  protected $actions_popup;

  /**
   * @return mixed
   */
  public function getValues() {
    return [
      'label'         => $this->get('label'),
      'icon'          => $this->icon,
      'description'   => $this->description,
      'actions_popup' => $this->actions_popup,
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
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param $description
   * @return $this
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getActionsPopup() {
    return $this->actions_popup;
  }

  /**
   * @param $actions
   * @return $this
   */
  public function setActionsPopup($actions_popup) {
    $this->set('actions_popup', $actions_popup);
    return $this;
  }

}
