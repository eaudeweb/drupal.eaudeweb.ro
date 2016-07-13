<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Color.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'color' element.
 *
 * @YamlFormElement(
 *   id = "color",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Color.php/class/Color",
 *   label = @Translation("Color"),
 *   category = @Translation("Advanced")
 * )
 */
class Color extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    $element['#attached']['library'][] = 'yamlform/yamlform.element.color';
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'swatch':
        return [
          '#theme' => 'yamlform_element_color_value_swatch',
          '#element' => $element,
          '#value' => $value,
          '#options' => $options,
        ];

      default:
        return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'swatch';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'swatch' => $this->t('Color swatch'),
    ];
  }

}
