<?php

/**
 * @file
 * Contains \Drupal\votingapi\VoteStorageInterface.
 */

namespace Drupal\votingapi;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines an interface for vote entity storage classes.
 */
interface VoteStorageInterface extends EntityStorageInterface {

  /**
   * Get votes for a user
   * @param $uid
   * @param string $vote_type_id
   * @param string $entity_type_id
   * @param int $entity_id
   * @return mixed
   */
  function getUserVotes($uid, $vote_type_id = NULL, $entity_type_id = NULL, $entity_id = NULL);

  /**
   * Delete votes for a user
   * @param $uid
   * @param string $vote_type_id
   * @param string $entity_type_id
   * @param int $entity_id
   * @return mixed
   */
  function deleteUserVotes($uid, $vote_type_id = NULL, $entity_type_id = NULL, $entity_id = NULL);

  /**
   * Get votes since a determined moment
   * @return mixed
   */
  function getVotesSinceMoment();

  /**
   * @param $entity_type_id
   * @param $entity_id
   * @return boolean
   */
  function deleteVotesForDeletedEntity($entity_type_id, $entity_id);
}