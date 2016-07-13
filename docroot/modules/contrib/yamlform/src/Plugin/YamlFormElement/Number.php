<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Number.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'number' element.
 *
 * @YamlFormElement(
 *   id = "number",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Number.php/class/Number",
 *   label = @Translation("Number"),
 *   category = @Translation("Advanced")
 * )
 */
class Number extends TextFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'min' => '',
      'max' => '',
      'step' => '',
    ];
  }

}
