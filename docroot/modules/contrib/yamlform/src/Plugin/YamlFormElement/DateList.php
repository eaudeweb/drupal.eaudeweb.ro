<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\DateList.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'datelist' element.
 *
 * @YamlFormElement(
 *   id = "datelist",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Datetime!Element!Datelist.php/class/Datelist",
 *   label = @Translation("Date list"),
 *   category = @Translation("Date/time elements")
 *
 * )
 */
class DateList extends DateBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'date_part_order' => ['year', 'month', 'day', 'hour', 'minute'],
      'date_text_parts' => [
        'year',
      ],
      'date_year_range' => '1900:2050',
      'date_increment' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (!empty($element['#default_value']) && is_string($element['#default_value'])) {
      $element['#default_value'] = ($element['#default_value']) ? DrupalDateTime::createFromTimestamp(strtotime($element['#default_value'])) : NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['date']['date_part_order'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Date part and order'),
      '#description' => $this->t("Array of date parts indicating the parts and order that should be used in the selector. Includes 'year', 'month', 'day', 'hour', 'minute', 'seconds', and 'ampm' (for 12 hour time)."),
    ];
    $form['date']['date_text_parts'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Date text parts'),
      '#description' => $this->t("Date parts that should be presented as text fields instead of drop-down selectors. Include 'year', 'month', 'day', 'hour', 'minute', and 'seconds'"),
    ];
    $form['date']['date_year_range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date year range'),
      '#description' => $this->t("A description of the range of years to allow, like '1900:2050', '-3:+3' or '2000:+3', where the first value describes the earliest year and the second the latest year in the range."),
    ];
    $form['date']['date_increment'] = [
      '#type' => 'number',
      '#title' => $this->t('Date increment'),
      '#description' => $this->t('The increment to use for minutes and seconds'),
      '#size' => 4,
    ];
    return $form;
  }

}
