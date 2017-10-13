<?php

namespace Drupal\swiftmailer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Swift Mailer test form.
 */
class TestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'swiftmailer_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;

    $form['description'] = array(
      '#markup' => '<p>' . t('This page allows you to send a test e-mail to a recipient of your choice.') . '</p>',
    );

    $form['test'] = array(
      '#type' => 'fieldset',
      '#title' => t('Recipient'),
      '#description' => '<p>' . t('You can send a test e-mail to a recipient of your choice. The e-mail will be sent using the default values as provided by the Swift Mailer module or as configured by you.') . '</p>',
    );

    $form['test']['recipient'] = array(
      '#title' => t('E-mail'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => \Drupal::currentUser()->getEmail(),
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('plugin.manager.mail')->mail('swiftmailer', 'test', $form_state->getValue(['test', 'recipient']), \Drupal::languageManager()->getDefaultLanguage()->getId());
    drupal_set_message(t('An attempt has been made to send an e-mail to @email.', ['@email' => $form_state->getValue(['test', 'recipient'])]));
  }

}
