<?php

/**
 * @file
 * Contains \Drupal\votingapi\VoteTypeAccessControlHandler.
 */

namespace Drupal\votingapi;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the vote type entity type.
 *
 * @see \Drupal\votingapi\Entity\VoteType
 */
class VoteTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
//    if ($operation == 'delete') {
//      if ($entity->isLocked()) {
//        return AccessResult::forbidden()->cacheUntilEntityChanges($entity);
//      }
//      else {
//        return parent::checkAccess($entity, $operation, $langcode, $account)
//          ->cacheUntilEntityChanges($entity);
//      }
//    }
    return parent::checkAccess($entity, $operation, $account);
  }

}
