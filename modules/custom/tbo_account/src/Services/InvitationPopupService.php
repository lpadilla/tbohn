<?php

namespace Drupal\tbo_account\Services;

use Drupal\adf_core\Base\BaseApiCache;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Class InvitationPopupService.
 *
 * @package Drupal\tbo_account\Services
 */
class InvitationPopupService {

  /**
   * @return array|int|object
   */
  public function getInvitationsPopup() {

    $service = __FUNCTION__;

    $invitations = BaseApiCache::get("entity", $service, array_merge([], []));
    if (empty($invitations)) {
      $invitations = \Drupal::entityQuery('invitation_popup_entity')->execute();

      $options = [];

      foreach ($invitations as $category => $value) {
        $entity = \Drupal::entityTypeManager()
          ->getStorage('invitation_popup_entity')
          ->load($category);

        $options[$entity->get('id')] = $entity->getValues();
      }

      $this->categories = $options;

      $invitations = [];

      foreach ($this->categories as $key => $category) {
        $file = File::load($category['icon']);
        $style = ImageStyle::load('thumbnail');
        if ($file) {
          // Generates file url.
          $category['icon_url'] = $style->buildUrl($file->getFileUri());
        }
        $invitations[$key] = $category;
      }

      // Save categories in cache.
      BaseApiCache::set("entity", $service, array_merge([], []), $invitations, 180);
    }

    return $invitations;
  }

  /**
   * @param $category
   * @return mixed
   */
  public function getInvitationPopupByCategory($category) {
    $other_invitation = BaseApiCache::get("entity", 'getCategories', array_merge([], []));
    $entity = $other_invitation[$category];

    $invitations = $this->getInvitationsPopup();
    foreach ($invitations as $key => $c) {
      if ($key === $entity['invitation_popup']) {
        return $c;
      }
    }

    $data['description'] = t('No existe la invitación.');
    return $data;
  }

}
