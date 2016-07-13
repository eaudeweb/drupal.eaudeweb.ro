<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\YamlFormAddress.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use \Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'yamlform_element_options' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_element_options",
 *   label = @Translation("Element options"),
 *   hidden = FALSE,
 *   multiple = TRUE,
 *   composite = TRUE,
 *   hidden = TRUE,
 * )
 */
class YamlFormElementOptions extends YamlFormElementBase {}
