<?php

namespace Drupal\adf_tabs\Repository;

/**
 * Class AdfTabsRepository.
 *
 * @package Drupal\adf_tabs\Repository
 */
class AdfTabsRepository {

  /**
   * Storage the conexion service to database.
   */
  protected $database;

  /**
   *
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Implements function hasRole for validate users roles.
   *
   * @param $rid
   *
   * @return bool
   */
  public function hasRole($rid) {
    return in_array($rid, \Drupal::currentUser()->getRoles());
  }

  /**
   * Get all items on the menu adf_tabs.
   *
   * @param $colums_table
   * @param array $conditions
   * @param array $orderBy
   * @param string $limit
   *
   * @return array
   */
  public function getAllItemsMenu($colums_table, $conditions = [], $orderBy = [], $limit = '') {
    $result = [];

    // Create Query.
    $query = $this->database->select('menu_item_adf_tabs', 'items');
    $query->join('menu_tab_entity', 'menu', 'items.id_menu = menu.id');

    // Add fields to query.
    if (count($colums_table) > 0) {
      foreach ($colums_table as $key => $data) {
        foreach ($data as $column) {
          $query->addField($key, $column);
        }
      }
    }

    // Add conditions to query.
    if (count($conditions) > 0) {
      foreach ($conditions as $key => $condition) {
        $query->condition($key, $condition);
      }
    }

    if (!empty($orderBy)) {
      $query->orderBy($orderBy['key'], $orderBy['order']);
    }

    // Add limit to query.
    if (!empty($limit)) {
      $query->range(0, $limit);
    }

    $result = $query->execute()->fetchAll();

    return $result;
  }

  /**
   * Get item by id_row.
   *
   * @param $id_row
   *
   * @return array
   */
  public function getItemMenu($id_row) {
    $result = [];

    // Create Query.
    $query = $this->database->select('menu_item_adf_tabs', 'items');
    $query->distinct();
    $query->join('menu_tab_entity', 'menu', 'items.id_menu = menu.id');
    $query->addField('items', 'id');
    // Add conditions to query.
    $query->condition('items.id', $id_row);

    $result = $query->execute()->fetchField();

    return $result;
  }

  /**
   * @param $id_row
   * @param $options
   */
  public function updateItem($id_row, $options) {
    $updated = $this->database->update('menu_item_adf_tabs')
      ->fields($options)
      ->condition('id', $id_row)
      ->execute();
  }

  /**
   * @param $options
   */
  public function insertItem($options) {
    $insert = $this->database->insert('menu_item_adf_tabs')
      ->fields($options)
      ->execute();
  }

  /**
   * @param $id_row
   */
  public function deleteItem($id_row) {
    $delete = $this->database->delete('menu_item_adf_tabs')
      ->condition('id', $id_row)
      ->execute();
  }

  /**
   * @param $id_menu
   */
  public function deleteAllItemsByMenu($id_menu) {
    $delete = $this->database->delete('menu_item_adf_tabs')
      ->condition('id_menu', $id_menu)
      ->execute();
  }

  /**
   * Get all the menu adf_tabs.
   */
  public function optionsMenu() {
    $query = \Drupal::database()->select('menu_tab_entity', 'm');
    $query->addField('m', 'id');
    $query->addField('m', 'name');

    return $query->execute()->fetchAll();
  }

  /**
   * Get all category.
   *
   * @param parameter $parameter
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function categoryByParameter($parameter) {
    $entityId = \Drupal::entityQuery('category_services_entity')
      ->condition('parameter', $parameter)
      ->execute();
    $keyId = array_keys($entityId);
    $entityCategory = \Drupal::entityTypeManager()
      ->getStorage('category_services_entity')
      ->load($keyId[0]);
    return $entityCategory;
  }

  /**
   * Get all items on menu.
   *
   * @param $menuId
   *   menu id
   * @param category $category
   *
   * @return mixed
   */
  public function itemsByIdMenu($menuId, $category) {
    $query = \Drupal::database()->select('menu_item_adf_tabs', 'm');
    $query->addField('m', 'block_config');
    $query->addField('m', 'name');
    $query->addField('m', 'id');
    $query->addField('m', 'category');
    $query->addField('m', 'block_id');
    $query->condition('id_menu', $menuId);
    $query->condition('to_show', '1');
    $query->orderBy('order_by', 'ASC');
    return $query->execute()->fetchAll();
  }

  /**
   * Get all category.
   *
   * @param table $entity
   * @param null $conditions
   *   contitions.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function allCategories($entity, $conditions = NULL) {
    $entityIds = \Drupal::entityQuery($entity);
    if ($conditions !== NULL) {
      foreach ($conditions as $condition) {
        $entityIds->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }
    $entityIds = $entityIds->execute();
    $entities = \Drupal::entityTypeManager()
      ->getStorage($entity)
      ->loadMultiple($entityIds);
    return $entities;
  }

  /**
   * Get de block configuracion.
   *
   * @param $idBlock
   *   items menu id
   *
   * @return mixed
   */
  public function configBlockById($idBlock) {
    $query = \Drupal::database()->select('menu_item_adf_tabs', 'm');
    $query->addField('m', 'block_config');
    $query->addField('m', 'block_id');
    $query->condition('id', $idBlock);
    return $query->execute()->fetchAll();
  }

}
