<?php
/**
 * @file
 * Allows site builders and administrator to view a module's README file.
 */

/**
 * Implements hook_drush_help().
 */
function readme_drush_help($section) {
  switch ($section) {
    case 'drush:readme-export':
      return dt("Exports a modules README.md file as HTML that can be cut-n-pasted into the modules project page.");
  }
}

/**
 * Implements hook_drush_command().
 */
function readme_drush_command() {
  $items = [];
  $items['readme-export'] = [
    'description' => "Exports a modules README.md file as HTML",
    'arguments' => [
      'module' => 'Module name.',
    ],
    'options' => [
      'project' => [
        'description' => "Generates HTML that can cut-n-pasted into a module's Drupal.org project page.",
      ],
      'path' => [
        'description' => 'Custom path to document within a module to be exported.',
      ],
    ],

    'examples' => [
      'drush readme-export example' => "Exports example.module's README.txt file as HTML.",
      'drush readme-export --project example' => "Exports example.module's README.txt file as HTML for the Example module's Drupal.org project page.",
    ],
  ];
  return $items;
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_readme_export_validate($module = NULL) {
  if (!$module) {
    return drush_set_error(dt('A module name is required.'));
  }

  $module_path = drupal_get_path('module', $module);
  if (!$module_path) {
    return drush_set_error(dt("Unable to get '@module' path.", ['@module' => $module]));
  }

  if ($readme_path = drush_get_option('path')) {
    if (!file_exists("$module_path/$readme_path")) {
      return drush_set_error(dt("README file '@path' does not exist in '@module'", ['@module' => $module, '@path' => $readme_path]));
    }
  }
  else {
    /** @var \Drupal\readme\ReadmeManagerInterface $readme_manager */
    $readme_manager = \Drupal::service('readme.manager');
    if (!$readme_manager->exists($module)) {
      return drush_set_error(dt("Module '@module' does not contain a README file.", ['@module' => $module]));
    }
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_readme_export($module) {
  /** @var \Drupal\readme\ReadmeManagerInterface $readme_manager */
  $readme_manager = \Drupal::service('readme.manager');
  $path = drush_get_option('path');
  if (drush_get_option('project')) {
    drush_print($readme_manager->getProjectPageHtml($module, $path));
  }
  else {
    drush_print($readme_manager->getHtml($module, $path));
  }
  drush_print('');
}
