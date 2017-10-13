<?php

/**
 * @file
 * Contains \Drupal\readme\Form\ReadmeSettingsForm.
 */

namespace Drupal\readme\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures readme settings.
 */
class ReadmeSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'readme_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secure token'),
      '#description' => $this->t('Entering a secure token will allow any external application to access a module\'s README HTML by appending ?token={token} to the  URL. Leave blank to disable token access.'),
      '#default_value' => \Drupal::state()->get('readme.token'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    // By default, render the form using theme_system_config_form().
    $form['#theme'] = 'system_config_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('readme.token', $form_state->getValue('token'));
    drupal_flush_all_caches();
    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
