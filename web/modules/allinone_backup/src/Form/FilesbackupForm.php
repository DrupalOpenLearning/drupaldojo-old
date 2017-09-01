<?php

namespace Drupal\allinone_backup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure allinone_backup settings for this site.
 */
class FilesbackupForm extends FormBase {

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
    $form['backupfieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Get full backup now'),
      '#open' => TRUE,
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
    $_SESSION['formlog'] = $form_state->getValue('message_log');
    $batch = [
      'title' => $this->t('Exporting'),
      'operations' => [
        ['allinone_backup', ['fullbackup']],
      ],
      'finished' => 'allinone_backup_finished_callback',
      'file' => drupal_get_path('module', 'allinone_backup') . '/allinone_backup.filesbackup.inc',
    ];
    batch_set($batch);
  }

}
