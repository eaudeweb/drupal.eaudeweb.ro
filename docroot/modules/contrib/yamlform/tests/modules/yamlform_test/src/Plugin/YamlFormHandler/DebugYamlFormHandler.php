<?php

/**
 * @file
 * Contains \Drupal\yamlform_test\Plugin\YamlFormHandler\DebugYamlFormHandler.
 */

namespace Drupal\yamlform_test\Plugin\YamlFormHandler;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormHandlerBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * YAML form submission debug handler.
 *
 * @YamlFormHandler(
 *   id = "debug",
 *   label = @Translation("Debug"),
 *   description = @Translation("Debug YAML form submission."),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class DebugYamlFormHandler extends YamlFormHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, YamlFormSubmissionInterface $yamlform_submission) {
    $build = ['#markup' => 'Submitted values are:<pre>' . Yaml::encode($yamlform_submission->getData()) . '</pre>'];
    drupal_set_message(\Drupal::service('renderer')->render($build), 'warning');
  }

}
