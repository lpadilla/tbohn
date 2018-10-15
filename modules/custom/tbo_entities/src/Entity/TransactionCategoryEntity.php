<?php

namespace Drupal\tbo_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Transaction category entity entity.
 *
 * @ConfigEntityType(
 *   id = "transaction_category_entity",
 *   label = @Translation("Transacción de categoría"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tbo_entities\TransactionCategoryEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\tbo_entities\Form\TransactionCategoryEntityForm",
 *       "edit" = "Drupal\tbo_entities\Form\TransactionCategoryEntityForm",
 *       "delete" = "Drupal\tbo_entities\Form\TransactionCategoryEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\tbo_entities\TransactionCategoryEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "transaction_category_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/transaction_category_entity/{transaction_category_entity}",
 *     "add-form" = "/admin/structure/transaction_category_entity/add",
 *     "edit-form" = "/admin/structure/transaction_category_entity/{transaction_category_entity}/edit",
 *     "delete-form" = "/admin/structure/transaction_category_entity/{transaction_category_entity}/delete",
 *     "collection" = "/admin/structure/transaction_category_entity"
 *   }
 * )
 */
class TransactionCategoryEntity extends ConfigEntityBase implements TransactionCategoryEntityInterface {

  /**
   * The Transaction category entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Transaction category entity label.
   *
   * @var string
   */
  protected $label;
  protected $category;
  protected $card;

  /**
   * @return mixed
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * @param $abbreviated_label
   * @return $this
   */
  public function setTypeCategory($category) {
    $this->set('category', $category);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getCard() {
    return $this->card;
  }

  /**
   * @param $abbreviated_label
   * @return $this
   */
  public function setCard($card) {
    $this->set('card', $card);
    return $this;
  }

}
