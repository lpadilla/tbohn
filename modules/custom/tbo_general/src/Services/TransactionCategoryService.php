<?php

namespace Drupal\tbo_general\Services;

/**
 *
 */
class TransactionCategoryService {

  /**
   * The options of the select categories.
   *
   * @return array
   */
  public function optionsSelectCategory() {
    $categories = $this->allEntity('category_services_entity');
    $selectCategory = [];
    foreach ($categories as $key => $entity) {
      $labelCategory = $entity->get('label');
      $selectCategory[$labelCategory] = $labelCategory;
    }
    return $selectCategory;
  }

  /**
   * Default configuration for the card.
   *
   * @return array
   */
  public function transactionIndexByCategory() {
    $result = [];
    $arrayPos = [];
    $transactionIds = \Drupal::entityQuery('transaction_category_entity')
      ->execute();
    $transactions = \Drupal::entityTypeManager()
      ->getStorage('transaction_category_entity')
      ->loadMultiple($transactionIds);
    foreach ($transactions as $key => $transaction) {
      $category = $transaction->get('category');
      $pos = isset($arrayPos[$category]) ? 0 : $arrayPos[$category];
      $result["fields_$category"][$transaction->get('label')] = [
        'title' => $transaction->get('label'),
        'service_field' => 'label',
        'show' => 0,
        'weight' => 1,
        'id' => $transaction->get('id'),
      ];
      $arrayPos[$category] = $pos + 1;
    }
    return $result;
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
   * Render Blocks.
   *
   * @param $idBlock
   *
   * @return array
   */
  public function viewBlocks($idBlocks) {
    $blocks = [];
    foreach ($idBlocks as $id) {
      $render = $this->getRenderBlock($id);
      $blocks[$id] = $render;
    }
    return $blocks;
  }

  /**
   *
   */
  public function getRenderBlock($id) {
    $blockManager = \Drupal::service('plugin.manager.block');
    $pluginBlock = $blockManager->createInstance($id, []);

    $blockForm = $pluginBlock->cardBlockForm([], []);
    // $pluginBlock->cardBuildConfigBlock($blockForm,$stage)
    $render = $pluginBlock->build();
    return $render;
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
