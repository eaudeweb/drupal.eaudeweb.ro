<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\TextField.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;


/**
 * Provides a 'textfield' element.
 *
 * @YamlFormElement(
 *   id = "textfield",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Textfield.php/class/Textfield",
 *   label = @Translation("Text field"),
 *   category = @Translation("Basic"),
 * )
 */
class TextField extends TextFieldBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    $element['#maxlength'] = (!isset($element['#maxlength'])) ? 255 : $element['#maxlength'];
  }

}
