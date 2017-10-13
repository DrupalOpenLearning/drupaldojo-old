<?php

namespace Drupal\allinone_backup\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure allinone_backup settings for this site.
 */
class BackupsettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'allinone_backup_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'allinone_backup.formsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('allinone_backup.formsettings');

    $form['number_record'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number Record Display in log'),
      '#default_value' => $config->get('record'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config('allinone_backup.formsettings')
      // Set the submitted configuration setting.
      ->set('record', $form_state->getValue('number_record'))
      // You can set multiple configurations at once by making.
      // multiple calls to set().
      ->save();

    parent::submitForm($form, $form_state);
  }

}
