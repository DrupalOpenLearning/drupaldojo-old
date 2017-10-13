<?php

/**
 * @file
 * Allinone backup import.
 */

use Drupal\Core\Database\Database;

/**
 * Do backup.
 */
function allinone_backup($type, &$context) {

  // Do heavy coding here...
  $message = t('Ready for import database backup...');
  switch ($type) {
    case 'importbackup':
      try {
        $results = db_query("SELECT * FROM batch ORDER BY `bid` DESC LIMIT 1");
        foreach ($results as $result) {
          $bid = $result->bid;
          $token = $result->token;
          $timestamp = $result->timestamp;
          $batch = $result->batch;
        }
        $db = Database::getConnectionInfo();
        $allinonefiles = 'private://allinonefiles/';
        // Unzip the zip file.
        $zip = new \ZipArchive();

        if (!$zip->open(\Drupal::service('file_system')->realpath($allinonefiles . $_SESSION['select_backup'])) == TRUE) {
          return FALSE;
        }

        $zip->extractTo(\Drupal::service('file_system')->realpath($allinonefiles));
        $zip->close();
        $file = substr($_SESSION['select_backup'], 0, -4);

        // Name of the file.
        $filename = \Drupal::service('file_system')->realpath($allinonefiles . $file . '.sql');

        // MySQL host.
        $mysql_host = $db['default']['host'];
        // MySQL username.
        $mysql_username = $db['default']['username'];
        // MySQL password.
        $mysql_password = $db['default']['password'];
        // Database name.
        $mysql_database = $db['default']['database'];

        // Export and import batch.
        $mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);
        $mysqli->query('SET foreign_key_checks = 0');

        if ($result = $mysqli->query("SHOW TABLES")) {
          while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $mysqli->query('DROP TABLE IF EXISTS ' . $row[0]);
          }
        }

        $mysqli->query('SET foreign_key_checks = 1');

        // Temporary variable, used to store current query.
        $templine = '';
        // Read in entire file.
        $lines = file($filename);
        // Loop through each line.
        foreach ($lines as $line) {
          // Skip it if it's a comment.
          if (substr($line, 0, 2) == '--' || $line == '') {
            continue;
          }
          // Add this line to the current segment.
          $templine .= $line;
          // If it has a semicolon at the end, it's the end of the query.
          if (substr(trim($line), -1, 1) == ';') {
            // Perform the query.
            $mysqli->query($templine) or print ('Error performing query, please go back and select any other backup for import.');
            // Reset temp variable to empty.
            $templine = '';
          }
        }
        $mysqli->close();
        db_insert('batch')
          ->fields([
            'bid' => $bid,
            'token' => $token,
            'timestamp' => $timestamp,
            'batch' => $batch,
          ])->execute();
        $context['message'] = t("Import database successfully...");
        $context['results'] = $results;

        // Remove sql file after imported.
        unlink($filename);
      }
      catch (Exception $e) {
        // Handle errors.
        $message = t('An error has occurred, details: @error', ['error', $e->getMessage()]);
      }
      break;

    default:
      $message = t("Could not Import backup");
      $context['message'] = $message;
      $context['results'] = $results;
      break;
  }
}

/**
 * Callback finish.
 */
function allinone_backup_finished_callback($success, $results, $operations) {
  $message = t('Import successfully : Import of database successfully processed.');
  drupal_set_message($message);
}
