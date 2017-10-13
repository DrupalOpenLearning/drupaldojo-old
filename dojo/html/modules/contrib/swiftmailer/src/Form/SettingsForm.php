<?php

namespace Drupal\swiftmailer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Swift Mailer settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'swiftmailer_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'swiftmailer.transport',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('swiftmailer.transport');

    // Submitted form values should be nested.
    $form['#tree'] = TRUE;

    // Display a page description.
    $form['description'] = array(
      '#markup' => '<p>' . t('This page allows you to configure settings which determines how e-mail messages are sent.') . '</p>',
    );

    $form['transport'] = array(
      '#id' => 'transport',
      '#type' => 'details',
      '#title' => t('Transport types'),
      '#description' => t('Which transport type should Drupal use to send e-mails?'),
      '#open' => TRUE,
    );

    // Display the currently configured transport type, or alternatively the
    // currently selected transport type if the user has chosen to configure
    // another transport type.
    $transport = $config->get('transport');
    $transport = ($form_state->hasValue(['transport', 'type'])) ? $form_state->getValue(['transport', 'type']) : $transport;

    $form['transport']['type'] = array(
      '#type' => 'radios',
      '#options' => array(
        SWIFTMAILER_TRANSPORT_SMTP => t('SMTP'),
        SWIFTMAILER_TRANSPORT_SENDMAIL => t('Sendmail'),
        SWIFTMAILER_TRANSPORT_NATIVE => t('PHP'),
        SWIFTMAILER_TRANSPORT_SPOOL => t('Spool'),
      ),
      '#default_value' => $transport,
      '#ajax' => array(
        'callback' => array($this, 'ajaxCallback'),
        'wrapper' => 'transport_configuration',
        'method' => 'replace',
        'effect' => 'fade',
      ),
      '#description' => t('Not sure which transport type to choose? The @documentation gives you a good overview of the various transport types.', array('@documentation' => Link::fromTextAndUrl((string) $this->t('Swift Mailer documentation'), Url::fromUri('http://swiftmailer.org/docs/sending.html#transport-types'))->toString())),
    );

    $form['transport']['configuration'] = array(
      '#type' => 'item',
      '#id' => 'transport_configuration',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP] = array(
      '#type' => 'item',
      '#access' => $form['transport']['type']['#default_value'] == SWIFTMAILER_TRANSPORT_SMTP,
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['title'] = array(
      '#markup' => '<h3>' . t('SMTP transport options') . '</h3>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['description'] = array(
      '#markup' => '<p>' . t('This transport type will send all e-mails using a SMTP
      server of your choice. You need to specify which SMTP server
      to use. Please refer to the @documentation for more details
      about this transport type.',
          array('@documentation' => Link::fromTextAndUrl($this->t('Swift Mailer documentation'), Url::fromUri('http://swiftmailer.org/docs/sending.html#the-smtp-transport'))->toString())) . '</p>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['server'] = array(
      '#type' => 'textfield',
      '#title' => t('SMTP server'),
      '#description' => t('The hostname or IP address at which the SMTP server can be reached.'),
      '#required' => TRUE,
      '#default_value' => $config->get('smtp_host'),
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['port'] = array(
      '#type' => 'textfield',
      '#title' => t('Port'),
      '#description' => t('The port at which the SMTP server can be reached (defaults to 25)'),
      '#default_value' => $config->get('smtp_port'),
      '#size' => 10,
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['encryption'] = array(
      '#type' => 'select',
      '#title' => t('Encryption'),
      '#options' => swiftmailer_get_encryption_options(),
      '#description' => t('The type of encryption which should be used (if any)'),
      '#default_value' => $config->get('smtp_encryption'),
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['username'] = array(
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#description' => t('A username required by the SMTP server (leave blank if not required)'),
      '#default_value' => $config->get('smtp_username'),
      '#attributes' => array(
        'autocomplete' => 'off',
      ),
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['password'] = array(
      '#type' => 'password',
      '#title' => t('Password'),
      '#description' => t('A password required by the SMTP server (leave blank if not required)'),
      '#default_value' => $config->get('smtp_password'),
      '#attributes' => array(
        'autocomplete' => 'off',
      ),
    );

    $current_password = $config->get('smtp_password');
    if (!empty($current_password)) {
      $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SMTP]['password']['#description'] = t('A password
      required by the SMTP server. <em>The currently set password is hidden for security reasons</em>.');
    }

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SENDMAIL] = array(
      '#type' => 'item',
      '#access' => $form['transport']['type']['#default_value'] == SWIFTMAILER_TRANSPORT_SENDMAIL,
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SENDMAIL]['title'] = array(
      '#markup' => '<h3>' . t('Sendmail transport options') . '</h3>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SENDMAIL]['description'] = array(
      '#markup' => '<p>' . t('This transport type will send all e-mails using a locally
      installed MTA such as Sendmail. You need to specify which
      locally installed MTA to use by providing a path to the
      MTA. If you do not provide any path then Swift Mailer
      defaults to /usr/sbin/sendmail. You can read more about
      this transport type in the @documentation.',
          array('@documentation' => Link::fromTextAndUrl($this->t('Swift Mailer documentation'), Url::fromUri('http://swiftmailer.org/docs/sending.html#the-sendmail-transport'))->toString())) . '</p>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SENDMAIL]['path'] = array(
      '#type' => 'textfield',
      '#title' => t('MTA path'),
      '#description' => t('The absolute path to the locally installed MTA.'),
      '#default_value' => $config->get('sendmail_path'),
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SENDMAIL]['mode'] = array(
      '#type' => 'radios',
      '#title' => t('Mode'),
      '#options' => array('bs' => 'bs', 't' => 't '),
      '#description' => t('Not sure which option to choose? Go with <em>bs</em>. You can read more about the above two modes in the @documentation.', array('@documentation' => Link::fromTextAndUrl($this->t('Swift Mailer documentation'), Url::fromUri('http://swiftmailer.org/docs/sendmail-transport'))->toString())),
      '#default_value' => $config->get('sendmail_mode'),
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_NATIVE] = array(
      '#type' => 'item',
      '#access' => $form['transport']['type']['#default_value'] == SWIFTMAILER_TRANSPORT_NATIVE,
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_NATIVE]['title'] = array(
      '#markup' => '<h3>' . t('PHP transport options') . '</h3>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_NATIVE]['description'] = array(
      '#markup' => '<p>' . t('This transport type will send all e-mails using the built-in
      mail functionality of PHP. This transport type can not be
      configured here. Please refer to the @documentation if you
      would like to read more about how the built-in mail functionality
      in PHP can be configured.',
          array('@documentation' => Link::fromTextAndUrl($this->t('PHP documentation'), Url::fromUri('http://www.php.net/manual/en/mail.configuration.php'))->toString())) . '</p>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SPOOL] = array(
      '#type' => 'item',
      '#access' => $form['transport']['type']['#default_value'] == SWIFTMAILER_TRANSPORT_SPOOL,
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SPOOL]['title'] = array(
      '#markup' => '<h3>' . t('Spool transport options') . '</h3>',
    );

    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SPOOL]['description'] = array(
      '#markup' => '<p>' . t('This transport does not attempt to send the email
    but instead saves the message to a spool file. Another process can then
    read from the spool and take care of sending the emails.') . '</p>',
    );

    $spool_directory = $config->get('spool_directory');
    $form['transport']['configuration'][SWIFTMAILER_TRANSPORT_SPOOL]['directory'] = array(
      '#type' => 'textfield',
      '#title' => t('Spool directory'),
      '#description' => t('The absolute path to the spool directory.'),
      '#default_value' => !empty($spool_directory) ? $spool_directory : sys_get_temp_dir() . '/swiftmailer-spool',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('swiftmailer.transport');

    if ($form_state->hasValue(['transport', 'type'])) {
      $config->set('transport', $form_state->getValue(['transport', 'type']));

      switch ($form_state->getValue(['transport', 'type'])) {
        case SWIFTMAILER_TRANSPORT_SMTP:
          $config->set('smtp_host', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SMTP, 'server']));
          $config->set('smtp_port', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SMTP, 'port']));
          $config->set('smtp_encryption', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SMTP, 'encryption']));
          $config->set('smtp_username', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SMTP, 'username']));
          $config->set('smtp_password', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SMTP, 'password']));
          $config->save();
          drupal_set_message(t('Drupal has been configured to send all e-mails using the SMTP transport type.'), 'status');
          break;

        case SWIFTMAILER_TRANSPORT_SENDMAIL:
          $config->set('sendmail_path', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SENDMAIL, 'path']));
          $config->set('sendmail_mode', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SENDMAIL, 'mode']));
          $config->save();
          drupal_set_message(t('Drupal has been configured to send all e-mails using the Sendmail transport type.'), 'status');
          break;

        case SWIFTMAILER_TRANSPORT_NATIVE:
          $config->save();
          drupal_set_message(t('Drupal has been configured to send all e-mails using the PHP transport type.'), 'status');
          break;

        case SWIFTMAILER_TRANSPORT_SPOOL:
          $config->set('spool_directory', $form_state->getValue(['transport', 'configuration', SWIFTMAILER_TRANSPORT_SPOOL, 'directory']));
          $config->save();
          drupal_set_message(t('Drupal has been configured to send all e-mails using the Spool transport type.'), 'status');
          break;
      }
    }

  }

  /**
   * Ajax callback for the transport dependent configuration options.
   *
   * @return array
   *   The form element containing the configuration options.
   */
  public static function ajaxCallback($form, &$form_state) {
    return $form['transport']['configuration'];
  }

}
