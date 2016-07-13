<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Select.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'select' element.
 *
 * @YamlFormElement(
 *   id = "select",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Select.php/class/Select",
 *   label = @Translation("Select"),
 *   category = @Translation("Options"),
 * )
 */
class Select extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'multiple' => FALSE,
      'empty_option' => '',
      'empty_value' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    if (empty($element['#multiple'])) {
      if (!isset($element['#empty_option'])) {
        $element['#empty_option'] = empty($element['#required']) ? $this->t('- Select -') : $this->t('- None -');
      }
    }
    else {
      if (!isset($element['#empty_option'])) {
        $element['#empty_option'] = empty($element['#required']) ? $this->t('- None -') : NULL;
      }
      $element['#element_validate'][] = [get_class($this), 'validateMultipleOptions'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['options']['multiple'] = [
      '#title' => $this->t('Multiple'),
      '#type' => 'checkbox',
      '#return_value' => TRUE,
      '#description' => $this->t('Check this option if the user should be allowed to choose multiple values.'),
    ];
    return $form;
  }

}
