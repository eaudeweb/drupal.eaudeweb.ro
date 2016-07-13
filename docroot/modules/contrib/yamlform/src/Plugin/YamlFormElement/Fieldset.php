<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Fieldset.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'fieldset' element.
 *
 * @YamlFormElement(
 *   id = "fieldset",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Fieldset.php/class/Fieldset",
 *   label = @Translation("Fieldset"),
 *   category = @Translation("Container")
 * )
 */
class Fieldset extends ContainerBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'title_display' => '',
      'description' => '',
      'required' => FALSE,
      'open' => FALSE,
      'private' => FALSE,
    ];
  }

}
