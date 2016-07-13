<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Captcha.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'captcha' element.
 *
 * @YamlFormElement(
 *   id = "captcha",
 *   api = "https://www.drupal.org/project/captcha",
 *   label = @Translation("CAPTCHA"),
 *   category = @Translation("Advanced")
 * )
 */
class Captcha extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function hasValue(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    if (\Drupal::currentUser()->hasPermission('skip CAPTCHA')) {
      $element['#access'] = FALSE;
      // Setting admin mode to TRUE prevents validation from occurring on
      // the hide element.
      $element['#captcha_admin_mode'] = TRUE;
    }
    parent::prepare($element, $yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Remove all captcha related keys from the YAML form submission's data.
    $key = $element['#yamlform_key'];
    $data = $yamlform_submission->getData();
    unset($data[$key]);
    // @see \Drupal\captcha\Element\Captcha
    $sub_keys = ['sid', 'token', 'response'];
    foreach ($sub_keys as $sub_key) {
      unset($data[$key . '_' . $sub_key]);
    }
    $yamlform_submission->setData($data);
  }

}
