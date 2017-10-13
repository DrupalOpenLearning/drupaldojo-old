<?php

/**
 * @file
 * Definition of Drupal\readme\ReadmeAccess.
 */

namespace Drupal\readme;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the custom access control handler for README pages.
 */
class ReadmeAccess {

  /**
   * Check whether the user has 'administer module' or the current request includes a valid token.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkAccess(AccountInterface $account) {
    $request_token = \Drupal::request()->get('token');
    $has_valid_token = ($request_token && $request_token == \Drupal::state()->get('readme.token'));
    $has_permission = $account->hasPermission('administer modules');
    return AccessResult::allowedIf($has_permission || $has_valid_token);
  }

}
