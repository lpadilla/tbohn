<?php

namespace Drupal\tbo_groups;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Group entity.
 *
 * @ingroup tbo_groups
 */
interface GroupInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
