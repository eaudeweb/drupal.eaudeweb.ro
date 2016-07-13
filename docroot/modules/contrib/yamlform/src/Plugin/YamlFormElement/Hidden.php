<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Hidden.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'hidden' element.
 *
 * @YamlFormElement(
 *   id = "hidden",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Hidden.php/class/Hidden",
 *   label = @Translation("Hidden"),
 *   category = @Translation("Basic")
 * )
 */
class Hidden extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'value' => '',
    ];
  }

}
