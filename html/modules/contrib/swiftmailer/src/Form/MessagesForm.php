<?php

namespace Drupal\swiftmailer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Configuration form for SwiftMailer message settings.
 */
class MessagesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'swiftmailer_messages_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'swiftmailer.message',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('swiftmailer.message');

    $form['#tree'] = TRUE;

    $form['description'] = array(
      '#markup' => '<p>' . t('This page allows you to configure settings which determines how e-mail messages are created.') . '</p>',
    );

    $form['format'] = array(
      '#type' => 'fieldset',
      '#title' => t('Message format'),
      '#description' => t('You can set the default message format which should be applied to e-mail
        messages.'),
    );

    $form['format']['type'] = array(
      '#type' => 'radios',
      '#options' => array(SWIFTMAILER_FORMAT_PLAIN => t('Plain Text'), SWIFTMAILER_FORMAT_HTML => t('HTML')),
      '#default_value' => $config->get('format'),
    );

    $form['format']['respect'] = array(
      '#type' => 'checkbox',
      '#title' => t('Respect provided e-mail format.'),
      '#default_value' => $config->get('respect_format'),
      '#description' => t('The header "Content-Type", if available, will be respected if you enable this setting.
        Settings such as e-mail format ("text/plain" or "text/html") and character set may be provided through this
        header. Unless your site somehow alters e-mails, enabling this setting will result in all e-mails to be sent
        as plain text as this is the content type Drupal by default will apply to all e-mails.'),
    );

    $form['convert'] = array(
      '#type' => 'fieldset',
      '#title' => t('Plain Text Version'),
      '#description' => t('An alternative plain text version can be generated based on the HTML version if no plain text version
        has been explicitly set. The plain text version will be used by e-mail clients not capable of displaying HTML content.'),
      '#states' => array(
        'visible' => array(
          'input[type=radio][name=format[type]]' => array('value' => SWIFTMAILER_FORMAT_HTML),
        ),
      ),
    );

    $form['convert']['mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Generate alternative plain text version.'),
      '#default_value' => $config->get('convert_mode'),
      '#description' => t('Please refer to @link for more details about how the alternative plain text version will be generated.', array('@link' => Link::fromTextAndUrl('html2text', Url::fromUri('http://www.chuggnutt.com/html2text')))),
    );

    $form['character_set'] = array(
      '#type' => 'fieldset',
      '#title' => t('Character Set'),
      '#description' => '<p>' . t('E-mails need to carry details about the character set which the
        receiving client should use to understand the content of the e-mail.
        The default character set is UTF-8.') . '</p>',
    );

    $form['character_set']['type'] = array(
      '#type' => 'select',
      '#options' => swiftmailer_get_character_set_options(),
      '#default_value' => $config->get('character_set'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('swiftmailer.message');
    $config->set('format', $form_state->getValue(['format', 'type']));
    $config->set('respect_format', $form_state->getValue(['format', 'respect']));
    $config->set('convert_mode', $form_state->getValue(['convert', 'mode']));
    $config->set('character_set', $form_state->getValue(['character_set', 'type']));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
