<?php

/**
 * @file
 * Contains \Drupal\votingapi\VoteStorage.
 */

namespace Drupal\votingapi;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Storage class for vote entities.
 */
class VoteStorage extends SqlContentEntityStorage implements VoteStorageInterface {

  function getUserVotes($uid, $vote_type_id = NULL, $entity_type_id = NULL, $entity_id = NULL) {
    $query = \Drupal::entityQuery('vote')
      ->condition('user_id', $uid);
    if ($vote_type_id) {
      $query->condition('type', $vote_type_id);
    }
    if ($entity_type_id) {
      $query->condition('entity_type', $entity_type_id);
    }
    if ($entity_id) {
      $query->condition('entity_id', $entity_id);
    }
    return $query->execute();
  }

  function deleteUserVotes($uid, $vote_type_id = NULL, $entity_type_id = NULL, $entity_id = NULL) {
    $query = \Drupal::entityQuery('vote')
      ->condition('user_id', $uid);
    if ($vote_type_id) {
      $query->condition('type', $vote_type_id);
    }
    if ($entity_type_id) {
      $query->condition('entity_type', $entity_type_id);
    }
    if ($entity_id) {
      $query->condition('entity_id', $entity_id);
    }
    $votes = $query->execute();
    if (!empty($votes)) {
      entity_delete_multiple('vote', $votes);
    }
  }

  function getVotesSinceMoment() {
    $last_cron = \Drupal::state()->get('votingapi.last_cron', 0);
    return \Drupal::entityQueryAggregate('vote')
      ->condition('timestamp', $last_cron, '>')
      ->groupBy('entity_type')
      ->groupBy('entity_id')
      ->groupBy('type')
      ->execute();
  }

  function deleteVotesForDeletedEntity($entity_type_id, $entity_id) {
    $votes = \Drupal::entityQuery('vote')
      ->condition('entity_type', $entity_type_id)
      ->condition('entity_id', $entity_id)
      ->execute();
    if (!empty($votes)) {
      entity_delete_multiple('vote', $votes);
    }
    db_delete('votingapi_result')
      ->condition('entity_type', $entity_type_id)
      ->condition('entity_id', $entity_id)
      ->execute();
  }

}