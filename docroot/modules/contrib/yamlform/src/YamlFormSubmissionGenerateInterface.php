<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionGenerateInterface.
 */

namespace Drupal\yamlform;

/**
 * Defines an interface for YAML form submission generation.
 *
 * @see \Drupal\yamlform\YamlFormSubmissionGenerate
 * @see \Drupal\yamlform\Plugin\DevelGenerate\YamlFormSubmissionDevelGenerate
 */
interface YamlFormSubmissionGenerateInterface {

  /**
   * Generate YAML form submission data.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form this submission will be added to.
   *
   * @return array
   *   An associative array containing YAML form submission data.
   */
  public function getData(YamlFormInterface $yamlform);

  /**
   * Get test value for a YAML form element.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param string $name
   *   The name of the element.
   * @param array $element
   *   The FAPI element.
   *
   * @return array|int|null
   *   An array containing multiple values or a single value.
   */
  public function getTestValue(YamlFormInterface $yamlform, $name, array $element);

}
