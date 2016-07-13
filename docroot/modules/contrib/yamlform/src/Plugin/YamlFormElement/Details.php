<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Details.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'details' element.
 *
 * @YamlFormElement(
 *   id = "details",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Details.php/class/Details",
 *   label = @Translation("Details"),
 *   category = @Translation("Container")
 * )
 */
class Details extends ContainerBase {

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
