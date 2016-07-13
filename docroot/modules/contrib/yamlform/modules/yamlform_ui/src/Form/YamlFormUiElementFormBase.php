<?php

/**
 * @file
 * Contains \Drupal\yamlform_ui\Form\YamlFormUiElementFormBase.
 */

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Drupal\yamlform\YamlFormEntityElementsValidator;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base form for YAML form elements.
 */
abstract class YamlFormUiElementFormBase extends FormBase {

  /**
   * YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * YAML form element validator.
   *
   * @var \Drupal\yamlform\YamlFormEntityElementsValidator
   */
  protected $elementsValidator;

  /**
   * The YAML form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * A YAML form element.
   *
   * @var \Drupal\yamlform\YamlFormElementInterface
   */
  protected $yamlformElement;

  /**
   * The YAML form element.
   *
   * @var array
   */
  protected $element = [];

  /**
   * The action of the current form.
   *
   * @var string
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_ui_element_form';
  }

  /**
   * Constructs a new YamlFormUiElementFormBase.
   *
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The YAML form element manager.
   * @param \Drupal\yamlform\YamlFormEntityElementsValidator $elements_validator
   *   YAML form element validator.
   */
  public function __construct(YamlFormElementManagerInterface $element_manager, YamlFormEntityElementsValidator $elements_validator) {
    $this->elementManager = $element_manager;
    $this->elementsValidator = $elements_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.element'),
      $container->get('yamlform.elements_validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $key = NULL, $parent_key = '') {
    $plugin_id = $this->elementManager->getElementPluginId($this->element);

    $this->yamlform = $yamlform;
    $this->yamlformElement = $this->elementManager->createInstance($plugin_id, $this->element);

    $form['parent_key'] = [
      '#type' => 'value',
      '#value' => $parent_key,
    ];

    $form['key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Key'),
      '#default_value' => $key,
      '#machine_name' => [
        'label' => $this->t('Key'),
        'exists' => [$this, 'exists'],
        'source' => ['title'],
      ],
      '#disabled' => $key,
      '#required' => TRUE,
    ];

    $form['properties'] = $this->yamlformElement->buildConfigurationForm([], $form_state);

    // Add type to the general details.
    $form['properties']['general']['type'] = [
      '#type' => 'item',
      '#title' => $this->t('Type'),
      '#markup' => $this->yamlformElement->getPluginLabel(),
      '#weight' => -100,
      '#parents' => ['type'],
    ];

    // Use title for key (machine_name).
    if (isset($form['properties']['general']['title'])) {
      $form['key']['#machine_name']['source'] = ['properties', 'general', 'title'];
      $form['properties']['general']['title']['#id'] = 'title';
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::submitForm',
      'event' => 'click',
    ];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#prefix'] = '<div id="yamlform-ui-element-dialog-form">';
    $form['#suffix'] = '</div>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // The YAML form element configuration is stored in the 'properties' key in
    // the form, pass that through for validation.
    $element_form_state = (new FormState())->setValues($form_state->getValue('properties') ?: []);
    $element_form_state->setFormObject($this);

    // Validate configuration form and set form errors.
    $this->yamlformElement->validateConfigurationForm($form, $element_form_state);
    $element_errors = $element_form_state->getErrors();
    foreach ($element_errors as $element_error) {
      $form_state->setErrorByName(NULL, $element_error);
    }

    // Stop validation is the element properties has any errors.
    if ($form_state->hasAnyErrors()) {
      return;
    }

    // Set element properties.
    $properties = $this->yamlformElement->getConfigurationFormProperties($form, $element_form_state);
    $parent_key = $form_state->getValue('parent_key');
    $key = $form_state->getValue('key');
    $this->yamlform->setElementProperties($key, $properties, $parent_key);

    // Validate elements.
    if ($messages = $this->elementsValidator->validate($this->yamlform)) {
      $t_args = [':href' => Url::fromRoute('entity.yamlform.source_form', ['yamlform' => $this->yamlform->id()])->toString()];
      $form_state->setErrorByName('elements', $this->t('There has been error validating the elements. You may need to edit the <a href=":href">YAML source</a> to resolve the issue.', $t_args));
      foreach ($messages as $message) {
        drupal_set_message($message, 'error');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display validation error messages in modal dialog.
    if ($this->isModalDialog() && $form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response = new AjaxResponse();
      $response->addCommand(new HtmlCommand('#yamlform-ui-element-dialog-form', $form));
      return $response;
    }

    // Display status message.
    if (!$form_state->hasAnyErrors()) {
      $properties = $form_state->getValue('properties');
      $t_args = [
        '%title' => (!empty($properties['title'])) ? $properties['title'] : $form_state->getValue('key'),
        '@action' => $this->action,
      ];
      drupal_set_message($this->t('%title has been @action.', $t_args));
    }

    // The YAML form element configuration is stored in the 'properties' key in
    // the form, pass that through for submission.
    $element_data = (new FormState())->setValues($form_state->getValue('properties'));
    $this->yamlformElement->submitConfigurationForm($form, $element_data);
    $this->yamlform->save();

    $redirect_url = $this->yamlform->urlInfo('edit-form');
    if ($this->isModalDialog()) {
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand($redirect_url->toString()));
      return $response;
    }
    else {
      $form_state->setRedirectUrl($redirect_url);
    }
  }

  /**
   * Determines if the YAML form element key already exists.
   *
   * @param string $key
   *   The YAML form element key.
   *
   * @return bool
   *   TRUE if the YAML form element key, FALSE otherwise.
   */
  public function exists($key) {
    $elements = $this->yamlform->getElementsInitializedAndFlattened();
    return (isset($elements[$key])) ? TRUE : FALSE;
  }

  /**
   * Return the YAML form associated with this form.
   *
   * @return \Drupal\yamlform\YamlFormInterface
   *   A YAML form
   */
  public function getYamlForm() {
    return $this->yamlform;
  }

  /**
   * Return the YAML form element associated with this form.
   *
   * @return \Drupal\yamlform\YamlFormElementInterface
   *   A YAML form element.
   */
  public function getYamlFormElement() {
    return $this->yamlformElement;
  }

  /**
   * Is the current request for an AJAX modal dialog.
   *
   * @return bool
   *   TRUE is the current request if for an AJAX modal dialog.
   */
  protected function isModalDialog() {
    $wrapper_format = $this->getRequest()->get(MainContentViewSubscriber::WRAPPER_FORMAT);
    return (in_array($wrapper_format, ['drupal_ajax', 'drupal_modal'])) ? TRUE : FALSE;
  }

}
