<?php

namespace Drupal\override_node_options\Form;

use Drupal\node\NodeForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Adds additional access checks for node override permissions.
 */
class OverrideNodeOptionsNodeForm extends NodeForm {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $node = $this->entity;
    $preview_mode = $node->type->entity->getPreviewMode();

    $element['submit']['#access'] = $preview_mode != DRUPAL_REQUIRED || $this->hasBeenPreviewed;
    $user = \Drupal::currentUser();
    $has_permissions = $user->hasPermission("override {$node->bundle()} published option") || $user->hasPermission('administer nodes');

    if ($element['submit']['#access'] && $has_permissions) {
      // Add a "Publish" button.
      $element['publish'] = $element['submit'];
      // If the "Publish" button is clicked, we want to update the status to
      // "published".
      $element['publish']['#published_status'] = TRUE;
      $element['publish']['#dropbutton'] = 'save';
      if ($node->isNew()) {
        $element['publish']['#value'] = $this->t('Save and publish');
      }
      else {
        $element['publish']['#value'] = $node->isPublished() ? $this->t('Save and keep published') : $this->t('Save and publish');
      }
      $element['publish']['#weight'] = 0;

      // Add a "Unpublish" button.
      $element['unpublish'] = $element['submit'];
      // If the "Unpublish" button is clicked, we want to update the status to
      // "unpublished".
      $element['unpublish']['#published_status'] = FALSE;
      $element['unpublish']['#dropbutton'] = 'save';
      if ($node->isNew()) {
        $element['unpublish']['#value'] = $this->t('Save as unpublished');
      }
      else {
        $element['unpublish']['#value'] = !$node->isPublished() ? $this->t('Save and keep unpublished') : $this->t('Save and unpublish');
      }
      $element['unpublish']['#weight'] = 10;

      // If already published, the 'publish' button is primary.
      if ($node->isPublished()) {
        unset($element['unpublish']['#button_type']);
      }
      // Otherwise, the 'unpublish' button is primary and should come first.
      else {
        unset($element['publish']['#button_type']);
        $element['unpublish']['#weight'] = -10;
      }

      // Remove the "Save" button.
      $element['submit']['#access'] = FALSE;
    }

    $element['preview'] = [
      '#type' => 'submit',
      '#access' => $preview_mode != DRUPAL_DISABLED && ($node->access('create') || $node->access('update')),
      '#value' => $this->t('Preview'),
      '#weight' => 20,
      '#submit' => ['::submitForm', '::preview'],
    ];

    $element['delete']['#access'] = $node->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

}
