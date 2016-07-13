<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormCheckboxesOther.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'checkboxes_other' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_checkboxes_other",
 *   label = @Translation("Checkboxes other"),
 *   category = @Translation("Options"),
 *   multiple = TRUE
 * )
 */
class YamlFormCheckboxesOther extends Checkboxes {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'other_option_label' => '',
      'other_placeholder' => '',
      'other_description' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    $element['#element_validate'][] = [get_class($this), 'validateMultipleOptions'];
  }

}
