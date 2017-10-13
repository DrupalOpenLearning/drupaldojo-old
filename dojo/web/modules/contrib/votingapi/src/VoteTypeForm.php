<?php

/**
 * @file
 * Contains \Drupal\votingapi\VoteTypeForm.
 */

namespace Drupal\votingapi;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for vote type forms.
 */
class VoteTypeForm extends EntityForm {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs the VoteTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add vote type');
    }
    else {
      $form['#title'] = $this->t('Edit %label vote type', array('%label' => $type->label()));
    }

    $form['label'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => t('The human-readable name of this vote type. This text will be displayed as part of the list on the <em>Add vote type</em> page. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'exists' => ['Drupal\votingapi\Entity\VoteType', 'load'],
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for this vote type. It must only contain lowercase letters, numbers, and underscores.', array(
        '%vote-add' => t('Add vote type'),
      )),
    );

    $form['value_type'] = array(
      '#title' => t('Value type'),
      '#type' => 'textfield',
      '#default_value' => $type->getValueType() ? $type->getValueType() : 'points',
      '#description' => t('The type of value for this vote (percentage, points, etc.)'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->getDescription(),
      '#description' => t('Describe this vote type. The text will be displayed on administrative pages.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save vote type');
    $actions['delete']['#value'] = t('Delete vote type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('type'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $type->set('id', trim($type->id()));
    $type->set('label', trim($type->label()));
    $type->set('value_type', trim($type->getValueType()));
    $type->set('description', trim($type->getDescription()));

    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The vote type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The vote type %name has been added.', $t_args));
      $context = array_merge($t_args, array('link' => $type->link($this->t('View'), 'collection')));
      $this->logger('vote')->notice('Added vote type %name.', $context);
    }

    $this->entityManager->clearCachedFieldDefinitions();
    $form_state->setRedirectUrl($type->toUrl('collection'));
  }
}