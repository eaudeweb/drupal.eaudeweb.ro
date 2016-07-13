<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Search.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'search' element.
 *
 * @YamlFormElement(
 *   id = "search",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Search.php/class/Search",
 *   label = @Translation("Search"),
 *   category = @Translation("Advanced")
 * )
 */
class Search extends TextFieldBase {

}
