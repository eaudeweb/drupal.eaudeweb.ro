<?php

/**
 * @file
 * Provides \Drupal\yamlform\YamlFormElementBase.
 */

namespace Drupal\yamlform;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormOptions;
use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\yamlform\Utility\YamlFormElementHelper;

/**
 * Provides a base class for a YAML form element.
 *
 * @see \Drupal\yamlform\YamlFormElementInterface
 * @see \Drupal\yamlform\YamlFormElementManager
 * @see \Drupal\yamlform\YamlFormElementManagerInterface
 * @see plugin_api
 */
class YamlFormElementBase extends PluginBase implements YamlFormElementInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'description' => '',

      'required' => FALSE,
      'default_value' => '',

      'title_display' => '',
      'prefix' => '',
      'suffix' => '',
      'field_prefix' => '',
      'field_suffix' => '',

      'private' => FALSE,
      'unique' => FALSE,

      'format' => $this->getDefaultFormat(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginApiUrl() {
    return (!empty($this->pluginDefinition['api'])) ? Url::fromUri($this->pluginDefinition['api']) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginApiLink() {
    $api_url = $this->getPluginApiUrl();
    return ($api_url) ? Link::fromTextAndUrl($this->getPluginLabel(), $api_url) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function hasValue(array $element) {
    return (!empty($element['#type'])) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isRoot(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiline(array $element) {
    return $this->pluginDefinition['multiline'];
  }

  /**
   * {@inheritdoc}
   */
  public function hasMultipleValues(array $element) {
    return $this->pluginDefinition['multiple'];
  }

  /**
   * {@inheritdoc}
   */
  public function isComposite(array $element) {
    return $this->pluginDefinition['composite'];
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array &$element) {
    // Set #allowed_tags to admin tag list. YAML form builders are
    // to be consider trusted users.
    if (isset($element['#allowed_tags'])) {
      $element['#allowed_tags'] = Xss::getAdminTagList();
    }

    // Set element options.
    if (isset($element['#options'])) {
      $element['#options'] = YamlFormOptions::getElementOptions($element);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Add validation handler for #unique value.
    if (!empty($element['#unique']) && !$this->hasMultipleValues($element)) {
      $element['#element_validate'][] = [get_class($this), 'validateUnique'];
      $element['#yamlform'] = $yamlform_submission->getYamlForm()->id();
      $element['#yamlform_submission'] = $yamlform_submission->id();
    }

    // Replace tokens for all properties.
    $token_data = [
      'yamlform' => $yamlform_submission->getYamlForm(),
      'yamlform_submission' => $yamlform_submission,
    ];
    $token_options = ['clear' => TRUE];
    foreach ($element as $element_property => $element_value) {
      // Most strings won't contain tokens so lets check and return ASAP.
      if (is_string($element_value) && strpos($element_value, '[') !== FALSE) {
        $element[$element_property] = \Drupal::token()->replace($element_value, $token_data, $token_options);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {}

  /**
   * {@inheritdoc}
   */
  public function getLabel(array $element) {
    return (!empty($element['#title'])) ? $element['#title'] : $element['#yamlform_key'];
  }

  /**
   * {@inheritdoc}
   */
  public function getKey(array $element) {
    return $element['#yamlform_key'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildHtml(array &$element, $value, array $options = []) {
    return $this->build('html', $element, $value, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function buildText(array &$element, $value, array $options = []) {
    return $this->build('text', $element, $value, $options);
  }

  /**
   * Build an element as text or HTML.
   *
   * @param string $format
   *   Format of the element, text or html.
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array
   *   A render array represent an element as text or HTML.
   */
  protected function build($format, array &$element, $value, array $options = []) {
    $options['multiline'] = $this->isMultiline($element);

    $format_function = 'format' . ucfirst($format);
    $formatted_value = $this->$format_function($element, $value, $options);
    // Convert string to renderable #markup.
    if (is_string($formatted_value)) {
      $formatted_value = ['#markup' => $formatted_value];
    }

    return [
      '#theme' => 'yamlform_element_base_' . $format,
      '#element' => $element,
      '#value' => $formatted_value,
      '#options' => $options,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    return $this->formatText($element, $value, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    // Return empty value.
    if ($value === '' || $value === NULL) {
      return '';
    }

    // Flatten arrays. Generally, $value is a string.
    if (is_array($value)) {
      $value = implode(', ', $value);
    }

    // Apply XSS filter to value that contains HTML tags and is not formatted as
    // raw.
    $format = $this->getFormat($element);
    if ($format != 'raw' && is_string($value) && strpos($value, '<') !== FALSE) {
      $value = Xss::filter($value);
    }

    // Format value based on the element type using default settings.
    if (isset($element['#type'])) {
      // Apply #field prefix and #field_suffix to value.
      if (isset($element['#field_prefix'])) {
        $value = $element['#field_prefix'] . $value;
      }
      if (isset($element['#field_suffix'])) {
        $value .= $element['#field_suffix'];
      }
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return [
      'value' => $this->t('Value'),
      'raw' => $this->t('Raw value'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'value';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat(array $element) {
    if (isset($element['#format'])) {
      return $element['#format'];
    }
    elseif ($default_format = \Drupal::config('yamlform.settings')->get('format.' . $this->getPluginId())) {
      return $default_format;
    }
    else {
      return $this->getDefaultFormat();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    if ($options['header_keys'] == 'label') {
      return [$this->getLabel($element)];
    }
    else {
      return [$element['#yamlform_key']];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    $element['#format'] = 'raw';
    return [$this->formatText($element, $value, $options)];
  }

  /**
   * Form API callback. Validate #unique value.
   */
  public static function validateUnique(array &$element, FormStateInterface $form_state) {
    $yamlform_id = $element['#yamlform'];
    $sid = $element['#yamlform_submission'];
    $name = $element['#name'];
    $value = $element['#value'];

    // Skip empty unique fields.
    if ($value == '') {
      return;
    }

    // Using range() is more efficient than using countQuery() for data checks.
    $query = db_select('yamlform_submission_data')
      ->fields('yamlform_submission_data', ['sid'])
      ->condition('yamlform_id', $yamlform_id)
      ->condition('name', $name)
      ->condition('value', $value)
      ->range(0, 1);
    if ($sid) {
      $query->condition('sid', $sid, '<>');
    }
    $count = $query->execute()->fetchField();
    if ($count) {
      $form_state->setError($element, t('The value %value has already been submitted once for the %title field. You may have already submitted this form, or you need to use a different value.', ['%value' => $element['#value'], '%title' => $element['#title']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preCreate(array &$element, array $values) {}

  /**
   * {@inheritdoc}
   */
  public function postCreate(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function postLoad(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function preDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function postDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function preSave(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function save(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function postSave(array &$element, YamlFormSubmissionInterface $yamlform_submission, $update = TRUE) {}

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];
    $form['general']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('This is used as a descriptive label when displaying this form element.'),
      '#required' => TRUE,
    ];
    $form['general']['description'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Description'),
      '#description' => $this->t('A short description of the element used as help for the user when he/she uses the form.'),
    ];
    if ($this->isComposite(['#type' => $this->getPluginId()])) {
      $form['general']['default_value'] = [
        '#type' => 'yamlform_codemirror',
        '#mode' => 'yaml',
        '#title' => $this->t('Default value'),
        '#description' => $this->t('The default value of the form element.'),
      ];
    }
    else {
      $form['general']['default_value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Default value'),
        '#description' => $this->t('The default value of the form element.'),
      ];
    }

    $form['general']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#description' => $this->t('The value of the form element.'),
    ];
    $form['general']['markup']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('HTML markup'),
      '#description' => $this->t('Enter custom HTML into your form.'),
    ];

    $form['form'] = [
      '#type' => 'details',
      '#title' => $this->t('Form display'),
      '#open' => TRUE,
    ];
    $form['form']['title_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Title display'),
      '#options' => [
        '' => '',
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
        'invisible' => $this->t('Invisible'),
        'attribute' => $this->t('Attribute'),
      ],
      '#description' => $this->t('Determines the placement of the title.'),
    ];
    $form['form']['prefix'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'text',
      '#title' => $this->t('Prefix'),
      '#description' => $this->t('Text or markup to include after the form element.'),
    ];
    $form['form']['suffix'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'text',
      '#title' => $this->t('Suffix'),
      '#description' => $this->t('Text or markup to include before the form element.'),
    ];
    $form['form']['field_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field prefix'),
      '#description' => $this->t('Text or code that is placed directly in front of the textfield. This can be used to prefix a textfield with a constant string. Examples: $, #, -.'),
      '#size' => 10,
    ];
    $form['form']['field_suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field suffix'),
      '#description' => $this->t('Text or code that is placed directly after a textfield. This can be used to add a unit to a textfield. Examples: lb, kg, %.'),
      '#size' => 10,
    ];

    $form['form']['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size'),
      '#description' => $this->t('Leaving blank will use the default size.'),
      '#size' => 4,
    ];
    $form['form']['maxlength'] = [
      '#type' => 'number',
      '#title' => $this->t('Maxlength'),
      '#description' => $this->t('Leaving blank will use the default maxlength.'),
      '#size' => 4,
    ];

    $form['form']['rows'] = [
      '#type' => 'number',
      '#title' => $this->t('Rows'),
      '#description' => $this->t('Leaving blank will use the default rows.'),
      '#size' => 4,
    ];

    $form['form']['min'] = [
      '#type' => 'number',
      '#title' => $this->t('Min'),
      '#description' => $this->t('Specifies the minimum value.'),
      '#size' => 4,
    ];
    $form['form']['max'] = [
      '#type' => 'number',
      '#title' => $this->t('Max'),
      '#description' => $this->t('Specifies the maximum value.'),
      '#size' => 4,
    ];
    $form['form']['step'] = [
      '#type' => 'number',
      '#title' => $this->t('Steps'),
      '#description' => $this->t('Specifies the legal number intervals.'),
      '#size' => 4,
    ];

    $form['form']['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#description' => $this->t('The placeholder will be shown in the element until the user starts entering a value.'),
    ];

    $form['form']['open'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open'),
      '#description' => $this->t('Contents should be visible (open) to the user.'),
      '#return_value' => TRUE,
    ];
    $form['form']['private'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Private'),
      '#description' => $this->t('Private elements are shown only to users with results access.'),
      '#weight' => 50,
      '#return_value' => TRUE,
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#open' => TRUE,
    ];
    $form['options']['options'] = [
      '#title' => $this->t('Options'),
      '#type' => 'yamlform_element_options',
      '#required' => TRUE,
    ];
    $form['options']['empty_option'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty option label'),
      '#description' => $this->t('The label to show for the initial option denoting no selection in a select element.'),
    ];
    $form['options']['empty_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty option value'),
      '#description' => $this->t('The value for the initial option denoting no selection in a select element, which is used to determine whether the user submitted a value or not.'),
    ];
    $form['options']['other_option_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other label'),
    ];
    $form['options']['other_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other placeholder'),
    ];
    $form['options']['other_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other description'),
    ];

    $form['validation'] = [
      '#type' => 'details',
      '#title' => $this->t('Form validation'),
      '#open' => TRUE,
    ];
    $form['validation']['required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required'),
      '#description' => $this->t('Check this option if the user must enter a value.'),
      '#return_value' => TRUE,
    ];
    $form['validation']['unique'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unique'),
      '#description' => $this->t('Check that all entered values for this element are unique. The same value is not allowed to be used twice.'),
      '#return_value' => TRUE,
    ];

    $form['display'] = [
      '#type' => 'details',
      '#title' => $this->t('Submission display'),
    ];
    $form['display']['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Format'),
      '#options' => $this->getFormats(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $default_properties = $this->getDefaultProperties();
    $element = YamlFormElementHelper::removePrefix($this->configuration) + $default_properties;

    $form = $this->form($form, $form_state);

    $this->setConfigurationFormDefaultValueRecursive($form, $element);

    // Store 'type' as a hardcoded value and make sure it is always first.
    // Also always remove the 'yamlform_*' prefix from the type name.
    if (isset($element['type'])) {
      $form['type'] = [
        '#type' => 'value',
        '#value' => preg_replace('/^yamlform_/', '', $element['type']),
        '#parents' => ['properties', 'type'],
      ];
      unset($element['type']);
    }

    // Allow custom properties (ie #states and #attributes) to be added to the
    // element.
    $form['custom'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom settings'),
      '#open' => $element ? TRUE : FALSE,
      '#access' => \Drupal::currentUser()->hasPermission('edit yamlform source'),
    ];
    if ($api_url = $this->getPluginApiUrl()) {
      $t_args = [
        '@href' => $api_url->toString(),
        '%label' => $this->getPluginLabel(),
      ];
      $form['custom']['#description'] = $this->t('Read the the %label element\'s <a href="@href">API documentation</a>.', $t_args);
    }

    $form['custom']['custom'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Custom properties'),
      '#description' => $this->t('Properties can include <a href=":state_href">#states</a> and <a href=":attributes_href">#attributes</a>.', [':states_href' => 'https://api.drupal.org/api/drupal/core%21includes%21common.inc/function/drupal_process_states', ':attributes_href' => 'https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/7.x#attributes']) .
        ' ' .
        $this->t('Properties do not have to prepended with hash (#) character, the hash character will be automatically added upon submission.') .
        '<br/>' .
        $this->t('These properties and callbacks are not allowed: @properties', ['@properties' => YamlFormArrayHelper::toString(YamlForm::getIgnoredProperties())]),
      '#default_value' => Yaml::encode($element),
      '#parents' => ['properties', 'custom'],
    ];

    $form['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['yamlform'],
      '#click_insert' => FALSE,
      '#dialog' => TRUE,
    ];

    return $form;
  }

  /**
   * Set configuration form default values recursively.
   *
   * @param array $form
   *   A form render array.
   * @param array $element
   *   The YAML form element.
   */
  protected function setConfigurationFormDefaultValueRecursive(array &$form, array &$element) {
    foreach ($form as $container_name => &$container_element) {
      if (Element::property($container_name)) {
        continue;
      }

      foreach ($container_element as $property_name => &$property_element) {
        if (Element::property($property_name)) {
          continue;
        }

        if (isset($element[$property_name])) {
          $this->setConfigurationFormDefaultValue($form, $element, $property_element, $property_name);
        }
        elseif (is_array($container_element[$property_name]) && Element::children($container_element[$property_name])) {
          $this->setConfigurationFormDefaultValueRecursive($container_element[$property_name], $element);
        }
        elseif (!isset($container_element[$property_name]['#access'])) {
          unset($container_element[$property_name]);
        }
      }

      // Remove empty containers, except the general container, which will
      // always include the element type.
      if ($container_name != 'general' && !Element::children($container_element)) {
        unset($form[$container_name]);
      }
    }
  }

  /**
   * Set an element's configuration form element default value.
   *
   * @param array $form
   *   An element's configuration form.
   * @param array $element
   *   The element.
   * @param array $property_element
   *   The form input used to set an element's property.
   * @param string $property_name
   *   THe property's name.
   */
  protected function setConfigurationFormDefaultValue(array &$form, array &$element, array &$property_element, $property_name) {
    if (is_array($element[$property_name])) {
      if ($property_name == 'default_value' && !$this->isComposite($element)) {
        $property_element['#default_value'] = implode(', ', $element[$property_name]);
      }
      else {
        $property_element['#default_value'] = Yaml::encode($element[$property_name]);
      }
    }
    else {
      $property_element['#default_value'] = $element[$property_name];
    }
    $property_element['#parents'] = ['properties', $property_name];
    unset($element[$property_name]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $properties = $this->getConfigurationFormProperties($form, $form_state);
    if ($ignored_properties = array_intersect_key(YamlForm::getIgnoredProperties(), $properties)) {
      $t_args = [
        '@properties' => YamlFormArrayHelper::toString($ignored_properties),
      ];
      $form_state->setErrorByName('custom', t('Element contains ignored/unsupported properties: @properties.', $t_args));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Generally, elements will not be processing any submitted properties.
    // It is possible that a custom element might need to call a third-party API
    // to 'register' the element.
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFormProperties(array &$form, FormStateInterface $form_state) {
    $properties = $form_state->getValues();

    if (isset($properties['custom'])) {
      // Decode and append custom properties.
      if ($properties['custom']) {
        $properties += Yaml::decode($properties['custom']);
      }
      unset($properties['custom']);
    }

    // Get default properties so that they can be unset below.
    $default_properties = $this->getDefaultProperties();

    // Remove all hash prefixes so that we can filter out any default
    // properties.
    YamlFormElementHelper::removePrefix($properties);

    // Build a temp element used to see if the mutliple value and/or composite
    // elements need to be supported.
    $element = YamlFormElementHelper::addPrefix($properties);
    foreach ($properties as $property_name => $property_value) {
      if (!isset($default_properties[$property_name])) {
        continue;
      }

      $this->getConfigurationFormProperty($properties, $property_name, $property_value, $element);

      if ($default_properties[$property_name] == $properties[$property_name]) {
        unset($properties[$property_name]);
      }
    }

    return YamlFormElementHelper::addPrefix($properties);
  }

  /**
   * Get configuration property value.
   *
   * @param array $properties
   *   An associative array of submitted properties.
   * @param string $property_name
   *   The property's name.
   * @param mixed $property_value
   *   The property's value.
   * @param array $element
   *   The element whose properties are being updated.
   */
  protected function getConfigurationFormProperty(array &$properties, $property_name, $property_value, array $element) {
    if ($property_name == 'default_value' && trim($property_value) && $this->hasMultipleValues($element)) {
      if ($this->isComposite($element)) {
        $properties[$property_name] = Yaml::decode($property_value);
      }
      else {
        $properties[$property_name] = preg_split('/\s*,\s*/', $property_value);
      }
    }
    else {
      // Decode raw YAML into an associative array.
      if ($this->isPropertyArray($property_name) && is_string($property_value)) {
        // Handle rare case where single array value is not parsed correctly.
        if (preg_match('/^- (.*?)\s*$/', $property_value, $match)) {
          $property_value = [$match[1]];
        }
        else {
          try {
            $property_value = Yaml::decode($property_value);
          }
          catch (\Exception $exception) {
            // Do nothing with YAML decoding exception.
          }
        }
      }

      $properties[$property_name] = $property_value;
    }
  }

  /**
   * Determine is an element's property should be an associative array.
   *
   * @param string $property_name
   *   An element's property name.
   *
   * @return bool
   *   TRUE is the element's default property value is an array.
   */
  protected function isPropertyArray($property_name) {
    $default_properties = $this->getDefaultProperties();
    return is_array($default_properties[$property_name]);
  }

}
