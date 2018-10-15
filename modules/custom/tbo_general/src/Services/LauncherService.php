<?php

namespace Drupal\tbo_general\Services;

/**
 *
 */
class LauncherService {

  /**
   * The options of the select categories.
   *
   * @return array
   */
  public function optionsSelectCategory() {
    $categories = $this->allEntity('category_services_entity');
    $selectCategory = ['empty' => t('Sin asociar')];
    foreach ($categories as $key => $entity) {
      $labelCategory = $entity->get('label');
      $selectCategory[$labelCategory] = $labelCategory;
    }
    return $selectCategory;
  }

  /**
   * All entities.
   *
   * @param $entity
   * @param null $conditions
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function allEntity($entity, $conditions = NULL) {
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
   *
   */
  public function categoryByParanater($parameter) {
    $entityId = \Drupal::entityQuery('category_services_entity')->condition('parameter', $parameter)->execute();
    $keyId = array_keys($entityId);
    $entityCategory = \Drupal::entityTypeManager()
      ->getStorage('category_services_entity')
      ->load($keyId[0]);
    return $entityCategory;
  }

}
