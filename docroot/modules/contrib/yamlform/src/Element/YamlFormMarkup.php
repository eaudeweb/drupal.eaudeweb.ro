<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormMarkup.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for YAML form markup.
 *
 * @FormElement("yamlform_markup")
 */
class YamlFormMarkup extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [];
  }

}
