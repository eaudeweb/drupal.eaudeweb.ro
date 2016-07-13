<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\DateTime.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'datetime' element.
 *
 * @YamlFormElement(
 *   id = "datetime",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Datetime!Element!Datetime.php/class/Datetime",
 *   label = @Translation("Date/time"),
 *   category = @Translation("Date/time elements")
 * )
 */
class DateTime extends DateBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    // Must define a '#default_value' for Datetime element to prevent the
    // below error.
    // Notice: Undefined index: #default_value in Drupal\Core\Datetime\Element\Datetime::valueCallback().
    if (!isset($element['#default_value'])) {
      $element['#default_value'] = NULL;
    }
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (is_string($element['#default_value']) && !empty($element['#default_value'])) {
      $element['#default_value'] = ($element['#default_value']) ? DrupalDateTime::createFromTimestamp(strtotime($element['#default_value'])) : NULL;
    }
  }

}
