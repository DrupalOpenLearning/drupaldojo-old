<?php

/**
 * @file
 * Create a full project backup.
 */

use Drupal\node\Entity\Node;

/**
 * This will backup full project.
 */
function allinone_backup($type, &$context) {

  // Do heavy coding here...
  $message = t('Ready for create files backup...');
  $results = t('Ready for create files backup...');

  switch ($type) {
    case 'fullbackup':
      try {
        $new_folder = 'private://allinonefiles/';
        if (!is_dir(\Drupal::service('file_system')->realpath($new_folder))) {
          file_prepare_directory($new_folder, FILE_CREATE_DIRECTORY);
        }
        // Make sure the script has enough time to run (300 seconds = 5 minutes)
        ini_set('max_execution_time', '300');
        ini_set('set_time_limit', '0');
        $targetname = 'filesbackup' . date("Y-m-d-his") . '.zip';
        // Default to archive.tar.
        $target = \Drupal::service('file_system')->realpath($new_folder) . '/filesbackup' . date("Y-m-d-his") . '.zip';

        // Defaults to all in current dir.
        $dir = './.';

        $exclude = '/^(?!(.*allinonefiles))(.*)$/i';
        // Setup phar.
        $phar = new PharData($target);
        // print_r($phar);
        $phar->buildFromDirectory($dir, $exclude);
        // $phar->compress(Phar::GZ);
        // unlink($target);
        // Change Permission to all user access.
        chmod(\Drupal::service('file_system')->realpath($new_folder), 0777);
        chmod((\Drupal::service('file_system')->realpath($new_folder) . "/" . $target), 0755);
        $message = t('Compressing all files done, backup of files successfully processed.');
        // Add Content For Database Backup History Display.
        $node = Node::create([
          'type' => 'allinone_backup',
          'title' => $targetname,
          'field_message_log' => $_SESSION['formlog'],
          'field_backup_type' => 'Files',
        ]);
        $node->save();
        $context['message'] = $message;
        $context['results'] = $results;
      }
      catch (Exception $e) {
        // Handle errors.
        $message = t('An error has occurred, details: @error', ['error', $e->getMessage()]);
      }
      break;

    default:
      $message = t("Could not create files backup try again");
      $context['message'] = $message;
      $context['results'] = $results;
      break;
  }
}

/**
 * Finished callback.
 */
function allinone_backup_finished_callback($success, $results, $operations) {
  // The 'success' parameter means no fatal PHP errors were detected. All
  // other error management should be handled using 'results'.
  if ($success) {
    $message = t('Backup successfully : Backup of files successfully processed.');
  }
  else {
    $message = t('Could not create files backup try again');
  }
  drupal_set_message($message);
}
