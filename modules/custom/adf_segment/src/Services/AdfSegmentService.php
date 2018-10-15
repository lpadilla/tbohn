<?php

namespace Drupal\adf_segment\Services;

use Segment;

/**
 * Class AdfSegmentService.
 *
 * @package Drupal\tbo_segment
 */
class AdfSegmentService {

  public $forms = [];
  private $current_user;
  protected $segment;

  /**
   * AdfSegmentService constructor.
   */
  public function __construct() {
    $this->current_user = \Drupal::currentUser();
    $this->segment = new Segment();
  }

  /**
   * Use segment init to create instance of Segment Class and initialize segment with config key.
   */
  public function segmentPhpInit() {
    $api_key = \Drupal::config('adf_segment.adf_segment_form_config')->get('api_key');
    $this->segment->init($api_key);
  }

  /**
   * @return mixed
   */
  public function getSegmentPhp() {
    return $this->segment;
  }

  /**
   * @param $event
   * @param string $category
   * @param string $action
   * @param string $label
   * @param string $value
   */
  public function pushUserLogin($event, $category = 'undefined', $action = 'undefined', $label = 'undefined', $value = 'undefined') {

    $action_id = strtolower($action);
    $action_id = str_replace(" ", "_", $action_id);

    $eventData = [
      'event' => $event,
      'category' => $category,
      'action' => $action,
      'actionId' => $action_id,
      'label' => $label,
      'value' => $value,
    ];

    $_SESSION['adf_segment']['user'] = $eventData;

  }

  /**
   *
   */
  public function getCurrentAccount() {
    return $this->current_user;
  }

  // Save segment track.
  public function sendSegmentTrack($event = '', $category = '', $label = '', $value = '') {
    // Set segment variable.
    $this->segmentPhpInit();
    $segment = $this->getSegmentPhp();

    $tigoId = \Drupal::service('tigoid.repository')->getTigoId($this->current_user->id());
    if (isset($tigoId)) {
      try {
        $segment_track = [
          'event' => $event,
          'userId' => $tigoId,
          'properties' => [
            'category' => $category,
            'label' => $label,
            'site' => 'NEW',
          ],
        ];

        if ($value != '') {
          $segment_track['properties']['value'] = $value;
        }

        $segment->track($segment_track);
      }
      catch (\Exception $e) {
        // send message exception.
      }
    }
  }

}
