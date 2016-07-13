<?php
/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionForm.
 */

namespace Drupal\yamlform;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\yamlform\Controller\YamlFormController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for controller for YAML form submission forms.
 */
class YamlFormSubmissionForm extends ContentEntityForm {

  /**
   * The YAML form element (plugin) manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * The YAML form submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * YAML form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $yamlFormRequest;

  /**
   * The YAML form third party settings manager.
   *
   * @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface
   */
  protected $thirdPartySettingsManager;

  /**
   * The YAML form message manager.
   *
   * @var \Drupal\yamlform\YamlFormMessageManagerInterface
   */
  protected $messageManager;

  /**
   * The YAML form settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * The YAML form submission.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionInterface
   */
  protected $entity;

  /**
   * The source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The YAML form request handler.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The YAML form element manager.
   * @param \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager
   *   The YAML form third party settings manager.
   * @param \Drupal\yamlform\YamlFormMessageManagerInterface $message_manager
   *   The YAML form message manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, YamlFormRequestInterface $yamlform_request, YamlFormElementManagerInterface $element_manager, YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager, YamlFormMessageManagerInterface $message_manager) {
    parent::__construct($entity_manager);
    $this->yamlFormRequest = $yamlform_request;
    $this->elementManager = $element_manager;
    $this->storage = $this->entityManager->getStorage('yamlform_submission');
    $this->thirdPartySettingsManager = $third_party_settings_manager;
    $this->messageManager = $message_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('yamlform.request'),
      $container->get('plugin.manager.yamlform.element'),
      $container->get('yamlform.third_party_settings_manager'),
      $container->get('yamlform.message_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    $this->sourceEntity = $this->yamlFormRequest->getCurrentSourceEntity(['yamlform', 'yamlform_submission']);
    if ($yamlform_submission_draft = $this->storage->loadDraft($entity->getYamlForm(), $this->sourceEntity, $this->currentUser())) {
      $entity = $yamlform_submission_draft;
    }
    $this->messageManager->setYamlFormSubmission($entity);
    $this->messageManager->setSourceEntity($this->sourceEntity);
    return parent::setEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // This submission form is based on the current URL, and hence it depends
    // on the 'url' cache context.
    $form['#cache']['contexts'][] = 'url';

    // Add this YAML form and the YAML form settings to the cache tags.
    $form['#cache']['tags'][] = 'config:yamlform.settings';

    // Add the YAML form as a cacheable dependency.
    \Drupal::service('renderer')->addCacheableDependency($form, $this->getYamlForm());

    // Display status messages.
    $this->displayMessages($form, $form_state);

    // Build the form.
    $form = parent::buildForm($form, $form_state);

    // Call custom YAML form alter hook.
    $form_id = $this->getFormId();
    $this->thirdPartySettingsManager->alter('yamlform_submission_form', $form, $form_state, $form_id);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Check for a custom form, track it, and return it.
    if ($custom_form = $this->getCustomForm($form, $form_state)) {
      $custom_form['#custom_form'] = TRUE;
      return $custom_form;
    }

    $form = parent::form($form, $form_state);

    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->getEntity();
    $settings = $this->getYamlFormSettings();

    // Prepend YAML form submission data using the default view without the data.
    if (!$yamlform_submission->isNew() && !$yamlform_submission->isDraft()) {
      $form['navigation'] = [
        '#theme' => 'yamlform_submission_navigation',
        '#yamlform_submission' => $yamlform_submission,
        '#source_entity' => $this->sourceEntity,
        '#rel' => 'edit-form',
        '#weight' => -20,
      ];
      $form['information'] = [
        '#theme' => 'yamlform_submission_information',
        '#yamlform_submission' => $yamlform_submission,
        '#source_entity' => $this->sourceEntity,
        '#open' => FALSE,
        '#weight' => -19,
      ];
    }

    // Get YAML form elements.
    $elements = $yamlform_submission->getYamlForm()->getElementsInitialized();

    // Get submission data.
    $data = $yamlform_submission->getData();

    // Prepopulate data using query string parameters.
    $this->prepopulateData($data);

    // Populate YAML form elements with YAML form submission data.
    $this->populateElements($elements, $data);

    // Prepare YAML form elements.
    $this->prepareElements($elements);

    // Handle form with managed file upload but saving of submission is disabled.
    if ($this->getYamlForm()->hasManagedFile() && !empty($settings['results_disabled'])) {
      $this->messageManager->log(YamlFormMessageManagerInterface::FORM_FILE_UPLOAD_EXCEPTION, 'notice');
      $this->messageManager->display(YamlFormMessageManagerInterface::FORM_EXCEPTION, 'warning');
      return $form;
    }

    // Move all $elements properties to the $form.
    $this->setFormPropertiesFromElements($form, $elements);

    // Init wizard.
    $this->initFormWizardState($form, $form_state);

    // Add wizard progress tracker to the form.
    if ($settings['wizard_progress_bar'] || $settings['wizard_progress_pages'] || $settings['wizard_progress_percentage']) {
      $wizard = $form_state->get('wizard');
      $form['progress'] = [
        '#theme' => 'yamlform_progress',
        '#yamlform' => $this->getYamlForm(),
        '#current_page' => $wizard['current'],
      ];
    }

    // Append elements to the form.
    $form['elements'] = $elements;

    // Alter form via YAML form handler.
    $this->getYamlForm()->invokeHandlers('alterForm', $form, $form_state, $yamlform_submission);

    // Add CSS and JS.
    $form['#attached']['library'][] = 'yamlform/yamlform.form';

    // Set current wizard or preview page.
    $this->setFormCurrentPage($form, $form_state);

    return $form;
  }

  /**
   * Get custom form which is displayed instead of the YAML form's elements.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array|bool
   *   A custom form or FALSE if the default form containing the YAML form's
   *   elements should be built.
   */
  protected function getCustomForm(array $form, FormStateInterface $form_state) {
    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->getEntity();
    $yamlform = $this->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Exit if elements are broken, usually occurs when elements YAML is edited
    // directly in the export config file.
    if (!$yamlform_submission->getYamlForm()->getElementsInitialized()) {
      $this->messageManager->display(YamlFormMessageManagerInterface::FORM_EXCEPTION, 'warning');
      return $form;
    }

    // Display inline confirmation message with back to link which is rendered
    // via the controller.
    if ($settings['confirmation_type'] == 'inline' && $this->getRequest()->query->get('yamlform_id') == $yamlform->id()) {
      $yamlform_controller = new YamlFormController($this->yamlFormRequest);
      $form['confirmation'] = $yamlform_controller->confirmation($this->getRequest(), $yamlform);
      return $form;
    }

    // Don't display form if it is closed.
    if ($yamlform_submission->isNew() && $yamlform->isClosed()) {
      // If the current user can update any submission just display the closed
      // message and still allow them to create new submissions.
      if ($yamlform->access('submission_update_any')) {
        $this->messageManager->display(YamlFormMessageManagerInterface::ADMIN_ACCESS, 'warning');
      }
      else {
        $form['closed'] = $this->messageManager->build(YamlFormMessageManagerInterface::FORM_CLOSED_MESSAGE);
        return $form;
      }
    }

    // Disable this form if confidential and user is logged in.
    if ($this->isConfidential() && $this->currentUser()->isAuthenticated() && $this->entity->isNew()) {
      $this->messageManager->display(YamlFormMessageManagerInterface::FORM_CONFIDENTIAL_MESSAGE, 'warning');
      return $form;
    }

    // Disable this form if submissions are not being saved to the database or
    // passed to a YamlFormHandler.
    if (!empty($settings['results_disabled']) && !$yamlform->getHandlers(NULL, TRUE, YamlFormHandlerInterface::RESULTS_PROCESSED)->count()) {
      $this->messageManager->log(YamlFormMessageManagerInterface::FORM_SAVE_EXCEPTION, 'error');
      if ($this->currentUser()->hasPermission('administer yamlform')) {
        // Display error to admin but allow them to submit the broken form.
        $this->messageManager->display(YamlFormMessageManagerInterface::FORM_SAVE_EXCEPTION, 'error');
        $this->messageManager->display(YamlFormMessageManagerInterface::ADMIN_ACCESS, 'warning');
      }
      else {
        // Display exception message to users.
        $this->messageManager->display(YamlFormMessageManagerInterface::FORM_EXCEPTION, 'warning');
        return $form;
      }
    }

    // Check total limit.
    if ($this->checkTotalLimit()) {
      $this->messageManager->display(YamlFormMessageManagerInterface::LIMIT_TOTAL_MESSAGE);
      if ($yamlform->access('submission_update_any')) {
        $this->messageManager->display(YamlFormMessageManagerInterface::ADMIN_ACCESS, 'warning');
      }
      else {
        return $form;
      }
    }

    // Check user limit.
    if ($this->checkUserLimit()) {
      $this->messageManager->display(YamlFormMessageManagerInterface::LIMIT_USER_MESSAGE, 'warning');
      if ($yamlform->access('submission_update_any')) {
        $this->messageManager->display(YamlFormMessageManagerInterface::ADMIN_ACCESS, 'warning');
      }
      else {
        return $form;
      }
    }

    return FALSE;
  }

  /**
   * Display draft and previous submission status messages for this YAML form submission.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function displayMessages(array $form, FormStateInterface $form_state) {
    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->getEntity();
    $yamlform = $this->getYamlForm();

    // Display test message.
    if ($this->isGet() && $this->isRoute('entity.yamlform.test')) {
      $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_TEST, 'warning');
    }

    // Display loaded or saved draft message.
    if ($yamlform_submission->isDraft()) {
      if ($form_state->get('draft_saved')) {
        $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_DRAFT_SAVED);
        $form_state->set('draft_saved', FALSE);
      }
      elseif ($this->isGet()) {
        $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_DRAFT_LOADED);
      }
    }

    // Display link to previous submissions message when user is adding a new
    // submission.
    if ($this->isGet()
      && ($this->isRoute('entity.yamlform.canonical') || $this->isYamlFormEntityReferenceFromSourceEntity())
      && $yamlform->access('submission_view_own')
      && $this->storage->hasPrevious($yamlform, $this->sourceEntity, $this->currentUser())
    ) {
      $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_PREVIOUS);
    }
  }

  /****************************************************************************/
  // Form actions
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    // Custom forms, which completely override the ContentEntityForm, should
    // not return the actions element (aka submit buttons).
    return (!empty($form['#custom_form'])) ? NULL : parent::actionsElement($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    /* @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $settings = $this->getYamlFormSettings();
    $preview_mode = $settings['preview'];
    $wizard = $form_state->get('wizard');

    // Remove the delete button from the YAML form submission form.
    unset($element['delete']);

    // Mark the submit action as the primary action, when it appears.
    $element['submit']['#button_type'] = 'primary';

    // Customize the submit button.
    $element['submit']['#value'] = $settings['form_submit_label'];

    // Add completed validate handler to submit.
    $element['submit']['#validate'][] = '::validateForm';
    $element['submit']['#validate'][] = '::complete';

    // Add confirmation submit handler to submit button.
    $element['submit']['#submit'][] = '::confirmation';

    $wizard_enabled = ($wizard['total']) ? TRUE : FALSE;
    $is_wizard_last_page = ($wizard['total'] == ($wizard['current'] + 2)) ? TRUE : FALSE;
    if ($wizard_enabled) {
      // Only show that save button if this is the last page of the wizard or
      // right before the optional preview.
      $element['submit']['#access'] = $is_wizard_last_page
        || ($this->isPreview($form_state)
          || ($this->isNextPagePreview($form_state) && $preview_mode != DRUPAL_REQUIRED));

      // Get current page which can contain custom prev(ious) and next button
      // labels.
      $current_page = $this->getYamlForm()->getPage($wizard['current']);

      if ($wizard['current']) {
        if ($this->isPreview($form_state)) {
          $previous_label = $settings['preview_prev_button_label'];
        }
        else {
          $previous_label = (isset($current_page['#prev_button_label'])) ? $current_page['#prev_button_label'] : $settings['wizard_prev_button_label'];
        }
        $element['previous'] = [
          '#type' => 'submit',
          '#value' => $previous_label,
          '#submit' => ['::submitForm', '::previous'],
          '#weight' => -1,
        ];
      }

      if (!$is_wizard_last_page) {
        if ($this->isNextPagePreview($form_state)) {
          $next_label = $settings['preview_next_button_label'];
        }
        else {
          $next_label = (isset($current_page['#next_button_label'])) ? $current_page['#next_button_label'] : $settings['wizard_next_button_label'];
        }
        $element['next'] = [
          '#type' => 'submit',
          '#value' => $next_label,
          '#validate' => ['::validateForm'],
          '#submit' => ['::submitForm', '::next'],
          '#weight' => -1,
        ];
      }
    }

    // Draft.
    if ($this->draftEnabled()) {
      $element['draft'] = [
        '#type' => 'submit',
        '#value' => $settings['draft_button_label'],
        '#validate' => ['::draft'],
        '#submit' => ['::submitForm', '::save', '::rebuild'],
        '#weight' => -10,
      ];
    }

    return $element;
  }

  /**
   * Form submission handler for the 'next' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function next(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      // Move wizard forward.
      $wizard = $form_state->get('wizard');
      $wizard['current']++;
      $form_state->set('wizard', $wizard);

      $this->autosave($form, $form_state);

      $form_state->setRebuild();
    }
  }

  /**
   * Form submission handler for the 'previous' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function previous(array &$form, FormStateInterface $form_state) {
    // Move wizard back.
    $wizard = $form_state->get('wizard');
    $wizard['current']--;
    $form_state->set('wizard', $wizard);

    $form_state->setRebuild();
  }

  /**
   * Form submission handler for the 'draft' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function draft(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
    $form_state->setValue('in_draft', TRUE);
    $form_state->set('draft_saved', TRUE);
    $this->entity->validate();
  }

  /**
   * Form submission handler for the 'complete' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function complete(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('in_draft', FALSE);
  }

  /**
   * Form submission handler for the 'rebuild' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function rebuild(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for the 'autosave' action.
   *
   * Autosave is triggered by validation errors and/or a form preview.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function autosave(array &$form, FormStateInterface $form_state) {
    $settings = $this->getYamlFormSettings();
    if ($this->draftEnabled() && $settings['draft_auto_save'] && !$this->entity->isCompleted()) {
      $form_state->setValue('in_draft', TRUE);

      $this->submitForm($form, $form_state);
      $this->save($form, $form_state);
      $this->rebuild($form, $form_state);
    }
  }

  /**
   * Form submission handler for the 'confirmation' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function confirmation(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    $yamlform = $yamlform_submission->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Get route name, parameters, and options.
    $route_name = $this->getRouteMatch()->getRouteName();
    $route_parameters = $this->getRouteMatch()->getRawParameters()->all();
    $route_options = [];
    if ($query = $this->getRequest()->query->all()) {
      $route_options['query'] = $query;
    }

    // Default to displaying a confirmation message on this page.
    $state = $yamlform_submission->getState();
    if ($state == YamlFormSubmissionInterface::STATE_UPDATED) {
      $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_UPDATED);
      $form_state->setRedirect($route_name, $route_parameters, $route_options);
      return;
    }

    // Add token route query options.
    if ($state == YamlFormSubmissionInterface::STATE_COMPLETED) {
      $route_options['query']['token'] = $yamlform_submission->getToken();
    }

    // Handle 'page', 'url', and 'inline' confirmation types.
    switch ($settings['confirmation_type']) {
      case 'page':
        if ($this->sourceEntity) {
          $entity_type = $this->sourceEntity->getEntityTypeId();
          $entity_id = $this->sourceEntity->id();
          $form_state->setRedirect("entity.$entity_type.yamlform.confirmation", [$entity_type => $entity_id], $route_options);
        }
        else {
          $form_state->setRedirect('entity.yamlform.confirmation', ['yamlform' => $yamlform->id()], $route_options);
        }
        return;

      case 'url':
        if ($confirmation_url = \Drupal::pathValidator()->getUrlIfValid(trim($settings['confirmation_url'])) ?: NULL) {
          $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_CONFIRMATION);
          $form_state->setRedirectUrl($confirmation_url);
          return;
        }

      case 'inline':
        $route_options['query']['yamlform_id'] = $yamlform->id();
        break;

      case 'message':
      default:
        if (!$this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_CONFIRMATION)) {
          $this->messageManager->display(YamlFormMessageManagerInterface::SUBMISSION_DEFAULT_CONFIRMATION);
        }
        break;
    }

    $form_state->setRedirect($route_name, $route_parameters, $route_options);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate form via YAML form handler.
    $this->getYamlForm()->invokeHandlers('validateForm', $form, $form_state, $this->entity);

    // Form validate handlers (via form['#validate']) are not called when
    // #validate handlers are attached to the trigger element
    // (ie submit button), so we need to manually call $form['validate']
    // handlers to support the modules that use form['#validate'] like the
    // validators.module.
    // @see \Drupal\yamlform\YamlFormSubmissionForm::actions
    // @see \Drupal\Core\Form\FormBuilder::doBuildForm
    $trigger_element = $form_state->getTriggeringElement();
    if (isset($trigger_element['#validate'])) {
      $handlers = array_filter($form['#validate'], function($callback) {
        // Remove ::validateForm to prevent a recursion.
        return (is_array($callback) || $callback != '::validateForm');
      });
      // @see \Drupal\Core\Form\FormValidator::executeValidateHandlers
      foreach ($handlers as $callback) {
        call_user_func_array($form_state->prepareCallback($callback), [&$form, &$form_state]);
      }
    }

    // If there are validation errors try to autosave this submission.
    if ($form_state->hasAnyErrors()) {
      $this->autosave($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->entity;
    $yamlform = $yamlform_submission->getYamlForm();

    // Get elements values from form submission.
    $values = array_intersect_key(
      $form_state->getValues(),
      $yamlform->getElementsFlattenedAndHasValue()
    );

    // Serialize the values as YAML and merge existing data.
    $yamlform_submission->setData($values + $yamlform_submission->getData());

    parent::submitForm($form, $form_state);

    // Submit form via YAML form handler.
    $this->getYamlForm()->invokeHandlers('submitForm', $form, $form_state, $yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    $settings = $this->getYamlFormSettings();

    // Exit if the saving of results is disabled.
    if ($settings['results_disabled']) {
      return;
    }

    // Set current page.
    $wizard = $form_state->get('wizard');
    if ($wizard['total']) {
      $current_page = $wizard['pages'][$wizard['current']];
      $yamlform_submission->setCurrentPage($current_page);
    }

    // Make sure the uri and remote addr are set correctly because
    // AJAX requests via 'managed_file' uploads can cause these values to be
    // reset.
    if ($yamlform_submission->isNew()) {
      $yamlform_submission->set('uri', preg_replace('#^' . base_path() . '#', '/', $this->getRequest()->getRequestUri()));
      $yamlform_submission->set('remote_addr', ($this->isConfidential()) ? '' : $this->getRequest()->getClientIp());
    }

    // Save and log YAML form submission.
    $yamlform_submission->save();

    // Check limits and invalidate cached and rebuild.
    if ($this->checkTotalLimit() || $this->checkUserLimit()) {
      Cache::invalidateTags(['yamlform:' . $this->getYamlForm()->id()]);
      $form_state->setRebuild();
    }
  }

  /****************************************************************************/
  // Form functions
  /****************************************************************************/

  /**
   * Set the form properties from the elements.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $elements
   *   An associative array containing the elements.
   */
  protected function setFormPropertiesFromElements(array &$form, array &$elements) {
    foreach ($elements as $key => $value) {
      if (is_string($key) && $key[0] == '#') {
        if (isset($form[$key]) && is_array($form[$key]) && is_array($value)) {
          $form[$key] = NestedArray::mergeDeep($form[$key], $value);
        }
        else {
          $form[$key] = $value;
        }
        unset($elements[$key]);
      }
    }
  }

  /**
   * Initialize the form wizard state manager.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function initFormWizardState(array &$form, FormStateInterface $form_state) {
    if ($form_state->get('wizard')) {
      return;
    }

    // Get pages, total, and current.
    $pages = array_keys($this->getYamlForm()->getPages());
    $total = count($pages);
    $current = 0;

    // Get current page from saved draft.
    $current_page = $this->entity->getCurrentPage();
    if ($current_page  && $this->draftEnabled()) {
      $index = array_flip($pages);
      if (isset($index[$current_page])) {
        $current = $index[$current_page];
      }
    }

    $form_state->set('wizard', [
      'total' => $total,
      'current' => $current,
      'pages' => $pages ,
    ]);
  }

  /**
   * Set form wizard current page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function setFormCurrentPage(array &$form, FormStateInterface $form_state) {
    if ($this->isPreview($form_state)) {
      // Hide elements.
      $form['elements']['#access'] = FALSE;

      // Display preview message.
      $this->messageManager->display(YamlFormMessageManagerInterface::FORM_PREVIEW_MESSAGE, 'warning');

      // Build preview.
      $form['preview'] = [
        '#theme' => 'yamlform_submission_html',
        '#yamlform_submission' => $this->entity,
      ];
    }
    else {
      $wizard = $form_state->get('wizard');
      foreach ($wizard['pages'] as $index => $page_key) {
        if ($index != $wizard['current']) {
          $form['elements'][$page_key]['#access'] = FALSE;
        }
        else {
          $form['elements'][$page_key]['#type'] = 'container';
        }
      }
    }
  }

  /****************************************************************************/
  // Elements functions
  /****************************************************************************/

  /**
   * Prepare form elements.
   *
   * @param array $elements
   *   An render array representing elements.
   */
  protected function prepareElements(array &$elements) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      // Replace default_value tokens
      // Invoke YamlFormElement::prepare.
      $this->elementManager->invokeMethod('prepare', $element, $this->entity);

      // Initialize default values.
      // Invoke YamlFormElement::setDefaultValue.
      $this->elementManager->invokeMethod('setDefaultValue', $element);

      // Recurse and prepare nested elements.
      $this->prepareElements($element);
    }
  }

  /**
   * Prepopulate element data.
   *
   * @param array $data
   *   An array of default.
   */
  protected function prepopulateData(array &$data) {
    $settings = $this->settings;
    if (!empty($settings['form_prepopulate'])) {
      $data += $this->getRequest()->query->all();
    }
  }

  /**
   * Populate form elements.
   *
   * @param array $elements
   *   An render array representing elements.
   * @param array $values
   *   An array of values used to populate the elements.
   */
  protected function populateElements(array &$elements, array $values) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      // Populate element if value exists.
      if (isset($element['#type']) && isset($values[$key])) {
        $element['#default_value'] = $values[$key];
      }

      $this->populateElements($element, $values);
    }
  }

  /****************************************************************************/
  // Account related functions
  /****************************************************************************/

  /**
   * Check YAML form submission total limits.
   *
   * @return bool
   *   TRUE if YAML form submission total limit have been met.
   */
  protected function checkTotalLimit() {
    $yamlform = $this->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Check per entity total limit.
    if (!empty($settings['entity_limit_total']) && ($source_entity = $this->getLimitSourceEntity())) {
      if ($this->storage->getTotal($yamlform, $source_entity) >= $settings['entity_limit_total']) {
        return TRUE;
      }
    }

    // Check total limit.
    if (!empty($settings['limit_total'])) {
      if ($this->storage->getTotal($yamlform) >= $settings['limit_total']) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Check YAML form submission user limit.
   *
   * @return bool
   *   TRUE if YAML form submission user limit have been met.
   */
  protected function checkUserLimit() {
    $account = $this->currentUser();
    $yamlform = $this->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Anonymous users can't have limits.
    if ($account->isAnonymous()) {
      return FALSE;
    }

    // Check per entity user limit.
    if (!empty($settings['entity_limit_user']) && ($source_entity = $this->getLimitSourceEntity())) {
      if ($this->storage->getTotal($yamlform, $source_entity, $account) >= $settings['entity_limit_user']) {
        return TRUE;
      }
    }

    // Check user limit.
    if (!empty($settings['limit_user'])) {
      if ($this->storage->getTotal($yamlform, NULL, $account) >= $settings['limit_user']) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Determine if drafts are enabled.
   *
   * @return bool
   *   TRUE if drafts are enabled.
   */
  protected function draftEnabled() {
    $account = $this->currentUser();
    $settings = $this->getYamlFormSettings();
    return ($account->isAuthenticated() && !empty($settings['draft']) && empty($settings['results_disabled'])) ? TRUE : FALSE;
  }

  /**
   * Returns the YAML form confidential indicator.
   *
   * @return bool
   *   TRUE if the YAML form is confidential .
   */
  public function isConfidential() {
    return $this->getYamlForm()->getSetting('form_confidential') ? TRUE : FALSE;
  }

  /**
   * Is the form being initially loaded via GET method.
   *
   * @return bool
   *   TRUE if the form is being initially loaded via GET method.
   */
  protected function isGet() {
    return ($this->getRequest()->getMethod() == 'GET') ? TRUE : FALSE;
  }

  /**
   * Determine if the current request is a specific route (name).
   *
   * @param string $route_name
   *   A route name.
   *
   * @return bool
   *   TRUE if the current request is a specific route (name).
   */
  protected function isRoute($route_name) {
    return ($route_name == $this->getRouteMatch()->getRouteName()) ? TRUE : FALSE;
  }

  /**
   * Determine if the form is in preview mode.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   TRUE if form is in preview.
   */
  protected function isPreview(FormStateInterface $form_state) {
    $wizard = $form_state->get('wizard');
    $page = $this->getYamlForm()->getPage($wizard['current']);
    return ($page['#type'] == 'yamlform_preview') ? TRUE : FALSE;
  }

  /**
   * Determine if the form's next page is preview mode.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   TRUE if the form's next page is preview.
   */
  protected function isNextPagePreview(FormStateInterface $form_state) {
    $wizard = $form_state->get('wizard');
    $page = $this->getYamlForm()->getPage($wizard['current'] + 1);
    return ($page && $page['#type'] == 'yamlform_preview') ? TRUE : FALSE;
  }

  /**
   * Is the current YAML form an entity reference from the source entity.
   *
   * @return bool
   *   TRUE is the current YAML form an entity reference from the source entity.
   */
  protected function isYamlFormEntityReferenceFromSourceEntity() {
    return $this->sourceEntity
    && method_exists($this->sourceEntity, 'hasField')
    && $this->sourceEntity->hasField('yamlform')
    && $this->sourceEntity->yamlform->target_id == $this->getYamlForm()->id();
  }

  /****************************************************************************/
  // Helper functions
  /****************************************************************************/

  /**
   * Get the YAML form submission's YAML form.
   *
   * @return \Drupal\yamlform\Entity\YamlForm
   *   A YAML form.
   */
  protected function getYamlForm() {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    return $yamlform_submission->getYamlForm();
  }

  /**
   * Get source entity for use with entity limit total and user submissions.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The YAML form submission's source entity.
   */
  protected function getLimitSourceEntity() {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();

    $source_entity = $yamlform_submission->getSourceEntity();
    if ($source_entity && $source_entity->getEntityTypeId() != 'yamlform') {
      return $source_entity;
    }
    return NULL;
  }

  /**
   * Get the YAML form submission's YAML form settings.
   *
   * @return array
   *   A specific settings or an associative array of settings.
   */
  protected function getYamlFormSettings() {
    if (empty($this->settings)) {
      // Get YAML form settings with default values.
      $this->settings = $this->getYamlForm()->getSettings();
      $default_settings = $this->config('yamlform.settings')->get('settings');
      foreach ($default_settings as $key => $value) {
        $key = str_replace('default_', '', $key);
        if (empty($this->settings[$key])) {
          $this->settings[$key] = $value;
        }
      }
    }
    return $this->settings;
  }

}
