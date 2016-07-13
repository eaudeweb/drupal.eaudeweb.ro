<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Checkbox.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'checkbox' element.
 *
 * @YamlFormElement(
 *   id = "checkbox",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Checkbox.php/class/Checkbox",
 *   label = @Translation("Checkbox"),
 *   category = @Translation("Basic"),
 * )
 */
class Checkbox extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = parent::getDefaultProperties();
    $properties['title_display'] = 'after';
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    return $this->t('Yes');
  }

}
