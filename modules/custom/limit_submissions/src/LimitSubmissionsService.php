<?php

namespace Drupal\limit_submissions;

/**
 * Class LimitSubmissionsService.
 *
 * @package Drupal\limit_submissions
 */
class LimitSubmissionsService {

  /**
   * Constructs a new LimitSubmissionsService object.
   */
  public function __construct() {

  }

  /**
   * Implement of createRow.
   *
   * @param mixed $value
   *   Value.
   * @param mixed $field
   *   Field.
   * @param mixed $form
   *   Form.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function createRow($value, $field, $form) {
    db_insert('limit_submissions')
      ->fields([
        'value' => $value,
        'field' => $field,
        'form' => $form,
        'last_timestamp' => time(),
      ])
      ->execute();
  }

  /**
   * Implement of getRows.
   *
   * @param mixed $value
   *   Value.
   * @param mixed $field
   *   Field.
   * @param mixed $form
   *   Form.
   *
   * @return mixed
   *   Resultado de la solicitud.
   */
  public function getRows($value, $field, $form) {
    $day = time() - 86400;
    $database = \Drupal::database();
    $query = $database->select('limit_submissions', 'limit_submissions');
    $query->fields(
      'limit_submissions',
      [
        'cid',
        'value',
        'field',
        'form',
        'last_timestamp',
      ]
    );

    $query->condition('limit_submissions.value', $value);
    $query->condition('limit_submissions.form', $form);
    $query->condition('limit_submissions.field', $field);
    $query->condition('limit_submissions.last_timestamp', $day, '>=');

    // ORDER BY created.
    $query->orderBy('last_timestamp', 'DESC');
    $result = $query->execute()->fetchAll();

    if ($result) {
      return $result;
    }

    return FALSE;
  }

}
