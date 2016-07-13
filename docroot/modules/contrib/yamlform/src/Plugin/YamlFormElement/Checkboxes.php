<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Checkboxes.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'checkboxes' element.
 *
 * @YamlFormElement(
 *   id = "checkboxes",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Checkboxes.php/class/Checkboxes",
 *   label = @Translation("Checkboxes"),
 *   category = @Translation("Options"),
 *   multiple = TRUE
 * )
 */
class Checkboxes extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    $element['#element_validate'][] = [get_class($this), 'validateMultipleOptions'];
  }

}
