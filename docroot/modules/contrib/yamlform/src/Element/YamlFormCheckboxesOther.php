<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormCheckboxesOther.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a form element for checkboxes with an other option.
 *
 * @FormElement("yamlform_checkboxes_other")
 */
class YamlFormCheckboxesOther extends FormElement {

  const OTHER_OPTION = '_other_';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processYamlFormCheckboxesOther'],
      ],
      '#theme_wrappers' => ['form_element'],
      '#options' => [],
      '#other_option_label' => FALSE,
      '#other_placeholder' => FALSE,
      '#other_description' => FALSE,
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

      $default_value = array_combine($element['#default_value'], $element['#default_value']);
      if ($other_options = array_diff_key($default_value, $element['#options'])) {
        $element['checkboxes']['#default_value'] = $element['#default_value'] + [self::OTHER_OPTION => self::OTHER_OPTION];
        $element['other']['#default_value'] = reset($other_options);
      }
      return $element;
    }
    return NULL;
  }

  /**
   * Processes a checkboxes other form element.
   *
   * See checkboxes form element for checkboxes properties.
   * \Drupal\Core\Render\Element\Checkboxes
   */
  public static function processYamlFormCheckboxesOther(&$element, FormStateInterface $form_state, &$complete_form) {
    // Build checkboxes element with selected properties.
    $properties = [
      '#title',
      '#options',
      '#default_value',
      '#attributes',
      '#title_display',
      '#description_display',
      '#required',
      '#ajax',
    ];
    $element['checkboxes']['#type'] = 'checkboxes';
    $element['checkboxes'] += array_intersect_key($element, array_combine($properties, $properties));
    $element['checkboxes']['#options'][self::OTHER_OPTION] = (!empty($element['#other_option_label'])) ? $element['#other_option_label'] : t('Other...');
    $element['checkboxes']['#error_no_message'] = TRUE;

    // Build other textfield.
    $element['other']['#type'] = 'textfield';
    $element['other']['#placeholder'] = (!empty($element['#other_placeholder'])) ? $element['#other_placeholder'] : t('Enter other...');
    $element['other']['#description'] = (!empty($element['#other_description'])) ? $element['#other_description'] : NULL;
    $element['other']['#error_no_message'] = TRUE;

    // Remove title and options since they are being moved the
    // checkboxes element.
    unset($element['#title'], $element['#options']);

    $element['#tree'] = TRUE;
    if (isset($element['#element_validate'])) {
      $element['#element_validate'] = array_merge([[get_called_class(), 'validateYamlFormCheckboxesOther']], $element['#element_validate']);
    }
    else {
      $element['#element_validate'] = [[get_called_class(), 'validateYamlFormCheckboxesOther']];
    }
    $element['#attached']['library'][] = 'yamlform/yamlform.element.other';

    return $element;
  }

  /**
   * Validates a checkboxes other element.
   */
  public static function validateYamlFormCheckboxesOther(&$element, FormStateInterface $form_state, &$complete_form) {
    $checkboxes_value = $element['checkboxes']['#value'];
    $other_value = $element['other']['#value'];

    $value = $checkboxes_value;
    if (isset($checkboxes_value[self::OTHER_OPTION])) {
      unset($value[self::OTHER_OPTION]);
      if ($other_value !== '') {
        $value[$other_value] = $other_value;
      }
    }

    if ($element['#required'] && empty($value)) {
      $form_state->setError($element, t('@name field is required.', ['@name' => $element['checkboxes']['#title']]));
    }

    $form_state->setValueForElement($element['checkboxes'], NULL);
    $form_state->setValueForElement($element['other'], NULL);
    $form_state->setValueForElement($element, $value);

    return $element;
  }

}
