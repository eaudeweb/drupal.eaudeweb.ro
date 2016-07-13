<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Value.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'value' element.
 *
 * @YamlFormElement(
 *   id = "value",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Value.php/class/Value",
 *   label = @Translation("Value"),
 *   category = @Translation("Advanced")
 * )
 */
class Value extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'value' => '',
    ];
  }

}
