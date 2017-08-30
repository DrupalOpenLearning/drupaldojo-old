<?php

namespace Drupal\group\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the group add and edit forms.
 *
 * @ingroup group
 */
class GroupForm extends ContentEntityForm {

  /**
   * The private store factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStoreFactory;

  /**
   * Constructs a GroupForm object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The private store factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityManagerInterface $entity_manager) {
    $this->privateTempStoreFactory = $temp_store_factory;
    parent::__construct($entity_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    /** @var \Drupal\group\Entity\GroupTypeInterface $group_type */
    $group_type = $this->getEntity()->getGroupType();
    $replace = ['@group_type' => $group_type->label()];

    // We need to adjust the actions when using the group creator wizard.
    if ($form_state->get('group_wizard') && $form_state->get('group_wizard_id') == 'group_creator') {
      // Store a group instead of saving it.
      $actions['submit']['#submit'] = ['::submitForm', '::store'];

      // Update the label to be more user friendly.
      $actions['submit']['#value'] = $this->t('Create @group_type and complete your membership', $replace);

      // Add a cancel button to clear the private temp store.
      $actions['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#submit' => ['::cancel'],
        '#limit_validation_errors' => [],
      ];
    }
    // If we are not in the wizard, but creator memberships are enabled, we need
    // to reflect that on the submit button as well.
    elseif ($group_type->creatorGetsMembership()) {
      $actions['submit']['#value'] = $this->t('Create @group_type and become a member', $replace);
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // We call the parent function first so the entity is saved. We can then
    // read out its ID and redirect to the canonical route.
    $return = parent::save($form, $form_state);

    // Display success message.
    $t_args = [
      '@type' => $this->entity->getGroupType()->label(),
      '%title' => $this->entity->label(),
    ];

    drupal_set_message($this->operation == 'edit'
      ? $this->t('@type %title has been updated.', $t_args)
      : $this->t('@type %title has been created.', $t_args)
    );

    $form_state->setRedirect('entity.group.canonical', ['group' => $this->entity->id()]);
    return $return;
  }

  /**
   * Cancels the wizard for group creator membership.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\group\Entity\Controller\GroupController::addForm()
   */
  public function cancel(array &$form, FormStateInterface $form_state) {
    $store = $this->privateTempStoreFactory->get($form_state->get('group_wizard_id'));
    $store_id = $form_state->get('store_id');
    $store->delete("$store_id:entity");
    $store->delete("$store_id:step");

    // Redirect to the front page if no destination was set in the URL.
    $form_state->setRedirect('<front>');
  }

  /**
   * Stores a group from the wizard step 1 in the temp store.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\group\Entity\Controller\GroupController::addForm()
   */
  public function store(array &$form, FormStateInterface $form_state) {
    // Store the unsaved group in the temp store.
    $store = $this->privateTempStoreFactory->get($form_state->get('group_wizard_id'));
    $store_id = $form_state->get('store_id');
    $store->set("$store_id:entity", $this->getEntity());
    $store->set("$store_id:step", 2);

    // Disable any URL-based redirect until the final step.
    $request = $this->getRequest();
    $form_state->setRedirect('<current>', [], ['query' => $request->query->all()]);
    $request->query->remove('destination');
  }

}
