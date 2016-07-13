<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormCompositeBase.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element\CompositeFormElementTrait;
use Drupal\yamlform\Entity\YamlFormOptions;

/**
 * Provides an base composite form element.
 */
abstract class YamlFormCompositeBase extends FormElement {

  use CompositeFormElementTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $info = [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processYamlFormComposite'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderCompositeFormElement'],
      ],
      '#theme_wrappers' => ['container'],
      '#required' => FALSE,
    ];
    return $info;
  }

  /**
   * Get a renderable array of form elements.
   *
   * @return array
   *   A renderable array of form elements, containing the base properties
   *   for the composite's form elements.
   */
  public static function getCompositeElements() {
    return [];
  }

  /**
   * Processes a composite form element.
   */
  public static function processYamlFormComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['#initialize'])) {
      return $element;
    }

    $element['#initialize'] = TRUE;
    $element['#tree'] = TRUE;
    $composite_elements = static::getCompositeElements();
    foreach ($composite_elements as $composite_key => &$composite_element) {
      // Transfer '#{composite_key}_{property}' from main element to composite
      // element.
      foreach ($element as $property_key => $property_value) {
        if (strpos($property_key, '#' . $composite_key . '__') === 0) {
          $composite_property_key = str_replace('#' . $composite_key . '__', '#', $property_key);
          $composite_element[$composite_property_key] = $property_value;
        }
      }

      if (isset($element['#value'][$composite_key])) {
        $composite_element['#value'] = $element['#value'][$composite_key];
      }

      // If composite element does not specified #required see if the $element
      // is required.
      if (!isset($composite_element['#required']) && !empty($element['#required'])) {
        $composite_element['#required'] = TRUE;
      }

      // Never required hidden composite elements.
      if (isset($composite_element['#access']) && $composite_element['#access'] == FALSE) {
        unset($composite_element['#required']);
      }

      if (isset($composite_element['#options'])) {
        $composite_element['#options'] = YamlFormOptions::getElementOptions($composite_element);
      }
    }

    $element += $composite_elements;
    $element['#element_validate'] = [[get_called_class(), 'validateYamlFormComposite']];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $composite_elements = static::getCompositeElements();
    $default_value = [];
    foreach ($composite_elements as $composite_key => $composite_element) {
      if (isset($composite_element['#type']) && $composite_element['#type'] != 'label') {
        $default_value[$composite_key] = '';
      }
    }

    if ($input === FALSE) {
      if (empty($element['#default_value']) || !is_array($element['#default_value'])) {
        $element['#default_value'] = [];
      }
      return $element['#default_value'] + $default_value;
    }
    return $input + $default_value;
  }

  /**
   * Validates a composite element.
   */
  public static function validateYamlFormComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    // Validate required composite elements.
    $composite_elements = static::getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      if (!empty($element[$composite_key]['#required']) && $value[$composite_key] == '') {
        if (isset($element[$composite_key]['#title'])) {
          $form_state->setError($element[$composite_key], t('@name field is required.', ['@name' => $element[$composite_key]['#title']]));
        }
      }
    }

    return $element;
  }

}
