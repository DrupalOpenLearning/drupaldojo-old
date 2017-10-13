<?php

namespace Drupal\social_post\Plugin\Block;

/**
 * Provides a 'PostProfileBlock' block.
 *
 * @Block(
 *  id = "post_profile_block",
 *  admin_label = @Translation("Post on profile of others block"),
 * )
 */
class PostProfileBlock extends PostBlock {

  public $entityType;
  public $bundle;
  public $formDisplay;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityType = 'post';
    $this->bundle = 'post';
    $this->formDisplay = 'profile';

    // Check if current user is the same as the profile.
    // In this case use the default form display.
    $uid = \Drupal::currentUser()->id();
    $account_profile = \Drupal::routeMatch()->getParameter('user');
    if (isset($account_profile) && ($account_profile === $uid || (is_object($account_profile) && $uid === $account_profile->id()))) {
      $this->formDisplay = 'default';
    }

  }

}
