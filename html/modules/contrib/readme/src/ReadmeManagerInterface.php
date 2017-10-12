<?php

/**
 * @file
 * Contains \Drupal\readme\ReadmeManagerInterface.
 */

namespace Drupal\readme;

/**
 * Defines the interface for README manager.
 */
interface ReadmeManagerInterface {

  /**
   * Determine if Markdown library is installed.
   *
   * @return bool
   *   TRUE  if Markdown library is installed.
   */
  public function markdownInstalled();

  /**
   * Check if a module has a README file.
   *
   * @param string $module_name
   *   A module name.
   * @param null|string $path
   *   (optional) Path to README file.
   *
   * @return bool
   *   TRUE if the module has a README file.
   */
  public function exists($module_name, $path = NULL);

  /**
   * Check if module's README file uses MarkDown.
   *
   * @param string $module_name
   *   A module name.
   * @param null|string $path
   *   (optional) Path to README file.
   *
   * @return bool
   *   TRUE if a module's README file uses MarkDown.
   */
  public function isMarkDown($module_name, $path = NULL);

  /**
   * Get a module's README file path.
   *
   * @param string $module_name
   *   A module name.
   * @param null|string $path
   *   (optional) Path to README file.
   *
   * @return bool|string
   *   The path to a module's README file. FALSE if the module does not exist
   *   or does not have a README file.
   */
  public function getPath($module_name, $path = NULL);

  /**
   * Get a module's README file as HTML.
   *
   * @param string $module_name
   *   A module name.
   * @param null|string $path
   *   (optional) Path to README file.
   *
   * @return bool|string
   *   module's README file as HTML. FALSE if the module does not exist
   *   or does not have README file.
   */
  public function getHtml($module_name, $path = NULL);

  /**
   * Get a module's README file as Drupal.org project page's HTML.
   *
   * @param string $module_name
   *   A module name.
   * @param null|string $path
   *   (optional) Path to README file.
   *
   * @return bool|string
   *   module's README file as HTML. FALSE if the module does not exist
   *   or does not have README file.
   */
  public function getProjectPageHtml($module_name, $path = NULL);

}
