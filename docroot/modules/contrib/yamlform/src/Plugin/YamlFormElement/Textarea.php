<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Textarea.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\Component\Render\HtmlEscapedText;

/**
 * Provides a 'textarea' element.
 *
 * @YamlFormElement(
 *   id = "textarea",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Textarea.php/class/Textarea",
 *   label = @Translation("Textarea"),
 *   category = @Translation("Basic"),
 *   multiline = TRUE
 * )
 */
class Textarea extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'rows' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $build = [
      '#markup' => nl2br(new HtmlEscapedText($value)),
    ];
    return \Drupal::service('renderer')->renderPlain($build);
  }

}
