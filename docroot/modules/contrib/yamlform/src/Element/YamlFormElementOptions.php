<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormElementOptions.
 */

namespace Drupal\yamlform\Element;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlFormOptions;

/**
 * Provides a form element for managing YAML form element options.
 *
 * This element is used by select, radios, checkboxes, and likert elements.
 *
 * @FormElement("yamlform_element_options")
 */
class YamlFormElementOptions extends FormElement {

  const CUSTOM_OPTION = '';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#likert' => FALSE,
      '#process' => [
        [$class, 'processYamlFormElementOptions'],
        [$class, 'processAjaxForm'],
      ],
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {

      $default_value = isset($element['#default_value']) ? $element['#default_value'] : NULL;

      if (!$default_value) {
        return $element;
      }

      if (is_string($default_value) && YamlFormOptions::load($default_value)) {
        $element['options']['#default_value'] = $default_value;
      }
      else {
        $element['options']['#default_value'] = self::CUSTOM_OPTION;
        $element['custom']['#default_value'] = (is_array($default_value)) ? Yaml::encode($default_value) : $default_value;
      }
      return $element;
    }
    return NULL;
  }

  /**
   * Processes a YAML form element options element.
   */
  public static function processYamlFormElementOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    // Predefined options.
    // @see (/admin/structure/yamlform/settings/options/manage)
    $options = [];
    $yamlform_options = YamlFormOptions::loadMultiple();
    foreach ($yamlform_options as $id => $yamlform_option) {
      // Filter likert options for answers to the likert element.
      if ($element['#likert'] && strpos($id, 'likert_') !== 0) {
        continue;
      }
      $options[$id] = $yamlform_option->label();
    }
    asort($options);

    $element['options']['#type'] = 'select';
    $element['options']['#options'] = [
      self::CUSTOM_OPTION => t('Custom...'),
    ] + $options;
    $element['options']['#attributes']['class'][] = 'js-' . $element['#id'] . '-options';
    $element['options']['#error_no_message'] = TRUE;
    $t_args = [
      '@type' => ($element['#likert']) ? t('answers') : t('options'),
      ':href' => Url::fromRoute('entity.yamlform_options.collection')->toString(),
    ];
    $element['options']['#description'] = t('Please select <a href=":href">predefined @type</a> or enter custom @type.', $t_args);

    // Custom options.
    $element['custom']['#title'] = $element['#title'];
    $element['custom']['#title_display'] = 'invisible';
    $element['custom']['#type'] = 'yamlform_codemirror';
    $element['custom']['#mode'] = 'yaml';
    $element['custom']['#states'] = [
      'visible' => [
        'select.js-' . $element['#id'] . '-options' => ['value' => ''],
      ],
    ];
    $element['custom']['#error_no_message'] = TRUE;
    $t_args = [
      '@type' => ($element['#likert']) ? t('answer') : t('option'),
    ];
    $element['custom']['#description'] = t('Key-value pairs MUST be specified as "safe_key: \'Some readable @type\'". Use of only alphanumeric characters and underscores is recommended in keys. One @type per line.', $t_args);
    if (!$element['#likert']) {
      $element['custom']['#description'] .= t('Option groups can be created by using just the group name followed by indented group options.');
    }

    $element['#element_validate'] = [[get_called_class(), 'validateYamlFormElementOptions']];
    return $element;

  }

  /**
   * Validates a YAML form element options element.
   */
  public static function validateYamlFormElementOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $options_value = $element['options']['#value'];
    $custom_value = $element['custom']['#value'];
    $value = $options_value;
    if ($options_value == self::CUSTOM_OPTION) {
      try {
        $value = Yaml::decode($custom_value);
      }
      catch (\Exception $exception) {
        // Do nothing since the 'yamlform_codemirror' element will have already
        // captured the validation error.
      }
    }

    $is_empty = ($value === '' || $value === NULL) ? TRUE : FALSE;

    if ($element['#required'] && $is_empty) {
      $form_state->setError($element, t('@name field is required.', ['@name' => $element['#title']]));
    }

    $form_state->setValueForElement($element['options'], NULL);
    $form_state->setValueForElement($element['custom'], NULL);
    $form_state->setValueForElement($element, $value);

    return $element;
  }

}
