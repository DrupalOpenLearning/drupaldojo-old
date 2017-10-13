<?php

namespace Drupal\allinone_backup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\Core\File\FileSystem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;

/**
 * Configure allinone_backup settings for this site.
 */
class DatabasebackupForm extends FormBase {

  protected $file;

  /**
   * Create constructor for initialize class.
   */
  public function __construct(FileSystem $fileStorage) {
    $this->file = $fileStorage;
  }

  /**
   * Create a dependency injection.
   */
  public static function create(ContainerInterface $container) {

    return new static(
    $container->get('file_system')
     );
  }

  /**
   * Escape data whene insert query run.
   */
  public function mysqlEscapeNoConn($input) {
    if (is_array($input)) {
      return array_map(__METHOD__, $input);
    }
    if (!empty($input) && is_string($input)) {
      return str_replace([
        '\\',
        "\0",
        "\n",
        "\r",
        "'",
        '"',
        "\x1a",
      ], [
        '\\\\',
        '\\0',
        '\\n',
        '\\r',
        "\\'",
        '\\"',
        '\\Z',
      ], $input);
    }
    return $input;
  }

  /**
   * Get Form ID.
   */
  public function getFormId() {
    return 'allinone_backup_form';
  }

  /**
   * Create form using buildForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $results = db_query("SHOW TABLES");
    $db = Database::getConnectionInfo();
    // Getting The List Of All Table for Exclude.
    while ($tablerow = $results->fetchObject()) {
      $tablename = 'Tables_in_' . $db['default']['database'];
      $tablelist[$tablerow->$tablename] = $tablerow->$tablename;
    }

    $selected = ["cachetags",
      "cache_toolbar",
      "cache_render",
      "cache_migrate",
      "cache_menu",
      "cache_entity",
      "cache_dynamic_page_cache",
      "cache_discovery",
      "cache_default",
      "cache_data",
      "cache_container",
      "cache_config",
      "cache_bootstrap",
    ];
    $form['backupfieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Get backup now'),
      '#open' => TRUE,
    ];
    $form['backupfieldset']['exclude_tablelist'] = [
      '#title' => $this->t('Tables To Exclude'),
      '#type' => 'select',
      '#size' => 10,
      '#multiple' => TRUE,
      '#options' => $tablelist,
      '#default_value' => $selected,
      '#attributes' => ['class' => ['exclude_tablelist']],
    // Add markup after form item.
      '#suffix' => '<a id="tablelist_selectall">Select all</a> / <a id="tablelist_deselecting">Deselecting all</a>',
    ];
    $form['backupfieldset']['message_log'] = [
      '#type' => 'textarea',
      '#title'  => $this->t('Description'),
      '#suffix' => '<p class="message_log">Describe the reasons for creating this backup.</p>',
      '#required' => TRUE,
    ];
    $form['#attached']['library'][] = 'allinone_backup/allinone_backup_default';
    $form['backupfieldset']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Backup Now'),
      '#button_type' => 'primary',
      '#weight' => '100',
    ];
    return $form;
  }

  /**
   * Form submit submitForm.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $limit = 200;
    $db = Database::getConnectionInfo();
    if (!Settings::get('file_private_path')) {
      drupal_set_message($this->t('Please Go to your sites/default/settings.php. Find file_private_path and remove the # from that line. replace line with $settings["file_private_path"] = "sites/default/files/private";'), 'error'); return $form;
    }
    $new_folder = 'private://allinonefiles/';
    if (!is_dir($this->file->realpath($new_folder))) {
      file_prepare_directory($new_folder, FILE_CREATE_DIRECTORY);
    }
    if (!is_dir(drupal_realpath($new_folder))) {
      drupal_set_message($this->t('Please clear all cache all try again.'), 'error');  return $form;
    }
    $excluded = $form_state->getValue('exclude_tablelist');
    $formlog = $form_state->getValue('message_log');
    // Get The excluded Tables List.
    $dbname = $db['default']['database'];
    // Get Current Database authenication for backup.
    $username = $db['default']['username'];
    $password = $db['default']['password'];
    $host = $db['default']['host'];
    $params = [
      'db_host' => $host,
      'db_uname' => $username,
      'db_password' => $password,
      'db_to_backup' => $dbname,
      'db_exclude_tables' => $excluded,
      // Put The Tables Name That We Won't To Backup.
    ];
    // IF exec not working on server.
    if (function_exists('exec')) {
      // ==========    IF exec working on server ========== //.
      $tabs = '';
      foreach ($excluded as $items) {
        $tabs .= ' --ignore-table=' . escapeshellarg($dbname . '.' . $items);
      }
      $datestamp = date("Y-m-d-his");
      // Set The Backup File Name.
      $backup_file_name_create = $dbname . "-" . $datestamp . ".sql";
      $command = "mysqldump " . escapeshellarg("$dbname") . " $tabs -u" . escapeshellarg($username) . " -p" . escapeshellarg($password) . " --host " . escapeshellarg($host) . " > " . escapeshellarg($this->file->realpath($new_folder) . "/" . $backup_file_name_create);

      exec($command);
      // Archive The Created File.
      $zip = new \ZipArchive();

      $backup_file_name = $dbname . "-" . $datestamp . ".zip";

      $fp = $this->file->realpath($new_folder) . "/" . $backup_file_name_create;
      $sqlsize = filesize($fp);
      if ($sqlsize > 30) {

        if ($zip->open($this->file->realpath($new_folder) . '/' . $backup_file_name, \ZIPARCHIVE::CREATE) !== TRUE) {

          drupal_set_message($this->t("cannot open folder"));
        }
        $zip->addFromString($backup_file_name_create, file_get_contents($fp));
        $zip->close();
        unlink($fp);

        // Change Permission to all user access.
        chmod(($this->file->realpath($new_folder) . "/" . $backup_file_name), 0755);
        // Change Permission to all user access.
        chmod($this->file->realpath($new_folder), 0755);

        drupal_set_message($this->t("Backup successfully : Backup of database successfully processed."));
        // Add Content For Database Backup History Display.
        $node = Node::create([
          'type'        => 'allinone_backup',
          'title'       => $backup_file_name,
          'field_message_log' => $formlog,
          'field_backup_type' => 'Database',
        ]);
        $node->save();
      }
      else {
        $this->backformForm($new_folder, $params, $excluded, $limit, $dbname, $db, $formlog);
        unlink($fp);
      }
    }
    else {
      $this->backformForm($new_folder, $params, $excluded, $limit, $dbname, $db, $formlog);
    }

  }

  /**
   * Manually backup backformForm.
   */
  public function backformForm($new_folder, $params, $excluded, $limit, $dbname, $db, $formlog) {
    // print_r($db);die;
    ini_set('memory_limit', '-1');
    $mtables = [];
    $contents = "-- Database: `" . $params['db_to_backup'] . "` --\n";

    $mysqli = new \mysqli($db['default']['host'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
    if ($mysqli->connect_error) {
      die('Error : (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    $results = $mysqli->query("SHOW TABLES");

    while ($row = $results->fetch_array()) {
      if (!in_array($row[0], $params['db_exclude_tables'])) {
        $mtables[] = $row[0];
      }
    }

    foreach ($mtables as $table) {
      $contents .= "-- Table `" . $table . "` --\n";

      $results = $mysqli->query("SHOW CREATE TABLE " . $table);
      while ($row = $results->fetch_array()) {
        $contents .= $row[1] . ";\n\n";
      }

      $results = $mysqli->query("SELECT * FROM " . $table);
      $row_count = $results->num_rows;
      $fields = $results->fetch_fields();
      $fields_count = count($fields);

      $insert_head = "INSERT INTO `" . $table . "` (";
      for ($i = 0; $i < $fields_count; $i++) {
        $insert_head .= "`" . $fields[$i]->name . "`";
        if ($i < $fields_count - 1) {
          $insert_head .= ', ';
        }
      }
      $insert_head .= ")";
      $insert_head .= " VALUES\n";

      if ($row_count > 0) {
        $r = 0;
        while ($row = $results->fetch_array()) {
          if (($r % 400) == 0) {
            $contents .= $insert_head;
          }
          $contents .= "(";
          for ($i = 0; $i < $fields_count; $i++) {
            $row_content = str_replace("\n", "\\n", $mysqli->real_escape_string($row[$i]));

            switch ($fields[$i]->type) {
              case 8: case 3:
                  $contents .= $row_content;
                break;

              default:
                $contents .= "'" . $row_content . "'";
            }
            if ($i < $fields_count - 1) {
              $contents .= ', ';
            }
          }
          if (($r + 1) == $row_count || ($r % 400) == 399) {
            $contents .= ");\n\n";
          }
          else {
            $contents .= "),\n";
          }
          $r++;
        }
      }
    }

    $datestamp = date("Y-m-d-his");
    $backup_file_name_create = $dbname . "-" . $datestamp . ".sql";
    $fp = fopen($this->file->realpath($new_folder) . "/" . $backup_file_name_create, 'w+');

    if (fwrite($fp, $contents)) {
      chmod(($this->file->realpath($new_folder) . "/" . $backup_file_name_create), 0777);
      // Change Permission to all user access.
      chmod($this->file->realpath($new_folder), 0777);
      drupal_set_message($this->t("Backup successfully : Backup of database successfully processed."));
    }
    fclose($fp);

    $zip = new \ZipArchive();
    // Archive The Created File.
    $backup_file_name = $dbname . "-" . $datestamp . ".zip";

    $fp = $this->file->realpath($new_folder) . "/" . $backup_file_name_create;

    if ($zip->open($this->file->realpath($new_folder) . '/' . $backup_file_name, \ZIPARCHIVE::CREATE) !== TRUE) {
      drupal_set_message($this->t("cannot open folder"));
    }
    $zip->addFromString($backup_file_name_create, file_get_contents($fp));
    $zip->close();
    unlink($fp);

    // Add Content For Database Backup History Display.
    $node = Node::create([
      'type' => 'allinone_backup',
      'title' => $backup_file_name,
      'field_message_log' => $formlog,
      'field_backup_type' => 'Database',
    ]);
    $node->save();
  }

}
