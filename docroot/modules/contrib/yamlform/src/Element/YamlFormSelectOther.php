<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormSelectOther.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\yamlform\Utility\YamlFormOptionsHelper;

/**
 * Provides a form element for a select menu with an other option.
 *
 * See #empty_option and #empty_value for an explanation of various settings for
 * a select element, including behavior if #required is TRUE or FALSE.
 *
 * @FormElement("yamlform_select_other")
 */
class YamlFormSelectOther extends FormElement {

  const OTHER_OPTION = '_other_';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#process' => [
        [$class, 'processYamlFormSelectOther'],
        [$class, 'processAjaxForm'],
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

      if (isset($element['#multiple']) && $element['#multiple']) {
        if (is_array($default_value)) {
          foreach ($default_value as $option_value) {
            if (!YamlFormOptionsHelper::hasOption($option_value, $element['#options'])) {
              $element['select']['#default_value'] = $default_value + [self::OTHER_OPTION => self::OTHER_OPTION];
              $element['other']['#default_value'] = $option_value;
              return $element;
            }
          }
        }

      }
      elseif (!YamlFormOptionsHelper::hasOption($default_value, $element['#options'])) {
        $element['select']['#default_value'] = self::OTHER_OPTION;
        $element['other']['#default_value'] = $default_value;
        return $element;
      }
      else {
        return $element;
      }
    }
    return NULL;
  }

  /**
   * Processes a select other list form element.
   *
   * See select list form element for select list properties.
   * \Drupal\Core\Render\Element\Select
   */
  public static function processYamlFormSelectOther(&$element, FormStateInterface $form_state, &$complete_form) {
    // Build select element with selected properties.
    $properties = [
      '#title',
      '#options',
      '#default_value',
      '#multiple',
      '#attributes',
      '#empty_value',
      '#empty_option',
      '#title_display',
      '#description_display',
      '#required',
    ];
    $element['select']['#type'] = 'select';
    $element['select'] += array_intersect_key($element, array_combine($properties, $properties));
    $element['select']['#options'][self::OTHER_OPTION] = (!empty($element['#other_option_label'])) ? $element['#other_option_label'] : t('Other...');
    $element['select']['#error_no_message'] = TRUE;

    // Build other textfield.
    $element['other']['#type'] = 'textfield';
    $element['other']['#placeholder'] = (!empty($element['#other_placeholder'])) ? $element['#other_placeholder'] : t('Enter other...');
    $element['other']['#description'] = (!empty($element['#other_description'])) ? $element['#other_description'] : NULL;
    $element['other']['#error_no_message'] = TRUE;

    // Remove title and options since they are being moved the select element.
    unset($element['#title'], $element['#options']);

    $element['#tree'] = TRUE;
    if (isset($element['#element_validate'])) {
      $element['#element_validate'] = array_merge([[get_called_class(), 'validateYamlFormSelectOther']], $element['#element_validate']);
    }
    else {
      $element['#element_validate'] = [[get_called_class(), 'validateYamlFormSelectOther']];
    }
    $element['#attached']['library'][] = 'yamlform/yamlform.element.other';

    return $element;

  }

  /**
   * Validates a select other element.
   */
  public static function validateYamlFormSelectOther(&$element, FormStateInterface $form_state, &$complete_form) {
    $select_value = $element['select']['#value'];
    $other_value = $element['other']['#value'];

    if (isset($element['#multiple']) && $element['#multiple']) {
      $value = $select_value;
      if (isset($select_value[self::OTHER_OPTION])) {
        unset($value[self::OTHER_OPTION]);
        if ($other_value !== '') {
          $value[$other_value] = $other_value;
        }
      }
      $is_empty = (empty($value)) ? TRUE : FALSE;
    }
    else {
      $value = $select_value;
      if ($select_value == self::OTHER_OPTION) {
        $value = $other_value;
      }
      $is_empty = ($value === '' || $value === NULL) ? TRUE : FALSE;
    }

    if ($element['#required'] && $is_empty) {
      $form_state->setError($element, t('@name field is required.', ['@name' => $element['select']['#title']]));
    }

    $form_state->setValueForElement($element['select'], NULL);
    $form_state->setValueForElement($element['other'], NULL);
    $form_state->setValueForElement($element, $value);

    return $element;
  }

}
