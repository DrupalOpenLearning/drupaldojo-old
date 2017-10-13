<?php
/**
 * @file
 * Contains \Drupal\link_css\LinkCssAdminForm.
 */

namespace Drupal\link_css;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LinkCssAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'link_css_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'link_css.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('link_css.settings');
    $system_css_preprocess = \Drupal::config('system.performance')->get('css.preprocess');

    $form = array();

    if ($system_css_preprocess) {
      drupal_set_message(
        t('<a href="/admin/config/development/performance">Aggregate CSS files</a> is turned on! This development module working when <em>Aggregate CSS files</em> is turned off.', array()), 'warning');
    }

    $form['link_css_skip_system'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Skip system links'),
      '#default_value' => $config->get('link_css_skip_system'),
      '#description' => t('Leave core CSS files loaded with @import. This helps
      avoid hitting IE\'s limit and saves any live refresh scripts monitoring
      files which are unlikely to change'),
    );

    $form['link_css_warn_ie_limit'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Warn if IE limit exceeded'),
      '#default_value' => $config->get('link_css_warn_ie_limit'),
      '#description' => t('Internet Explorer <=7 will not load more than 31 linked styelsheets. Display a warning if this limit is exceeded.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('link_css.settings')
      ->set('link_css_skip_system', $form_state->getValue('link_css_skip_system'))
      ->set('link_css_warn_ie_limit', $form_state->getValue('link_css_warn_ie_limit'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
