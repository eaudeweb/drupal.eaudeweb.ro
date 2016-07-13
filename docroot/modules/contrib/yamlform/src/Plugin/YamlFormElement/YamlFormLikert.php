<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\YamlFormLikert.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Entity\YamlFormOptions;
use Drupal\yamlform\Utility\YamlFormOptionsHelper;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormInterface;

/**
 * Provides a 'likert' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_likert",
 *   label = @Translation("Likert"),
 *   category = @Translation("Options"),
 *   multiline = TRUE,
 *   multiple = TRUE,
 *   composite = TRUE,
 * )
 */
class YamlFormLikert extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'description' => '',
      'required' => FALSE,
      'default_value' => '',
      'title_display' => '',
      'prefix' => '',
      'suffix' => '',
      'private' => FALSE,
      'format' => $this->getDefaultFormat(),
      'questions' => [],
      'answers' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array &$element) {
    parent::initialize($element);

    // Set element answers.
    if (isset($element['#answers'])) {
      $element['#answers'] = YamlFormOptions::getElementOptions($element, '#answers');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $answers = []) {
    $format = $this->getFormat($element);
    switch ($format) {
      case 'list':
        $items = [];
        foreach ($element['#questions'] as $question_key => $question_label) {
          $answer_value = (isset($value[$question_key])) ? $value[$question_key] : NULL;
          $answer_text = YamlFormOptionsHelper::getOptionText($answer_value, $element['#answers']);
          $items[$question_key] = ['#markup' => "<b>$question_label:</b> $answer_text"];
        }
        return [
          '#theme' => 'item_list',
          '#items' => $items,
        ];

      case 'table':
        // NOTE: Including inline align attributes to help style the table for
        // HTML emails.
        $header = [];
        $header['question'] = [
          'data' => '',
          'align' => 'left',
          'width' => '40%',
        ];
        foreach ($element['#answers'] as $answer_value => $answer_text) {
          $header[$answer_value] = [
            'data' => $answer_text,
            'align' => 'center',
          ];
        }

        // Calculate answers width.
        $width = number_format((60 / count($element['#answers'])), 2, '.', '') . '%';

        $rows = [];
        foreach ($element['#questions'] as $question_key => $question_label) {
          $question_value = (isset($value[$question_key])) ? $value[$question_key] : NULL;
          $row = [];
          $row[] = [
            'data' => $question_label,
            'align' => 'left',
            'width' => '40%',
          ];
          foreach ($element['#answers'] as $answer_value => $answer_text) {
            $row[$answer_value] = [
              'data' => ($question_value == $answer_value) ? ['#markup' => '&#10007;'] : '',
              'align' => 'center',
              'width' => $width,
            ];
          }
          $rows[$question_key] = $row;
        }
        return [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#attributes' => [
            'class' => ['yamlform-likert-table'],
          ],
          '#attached' => ['library' => ['yamlform/yamlform.element.likert']],
        ];

      default:
        return parent::formatHtml($element, $value, $answers);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $answers = []) {
    // Return empty value.
    if ($value === '' || $value === NULL || (is_array($value) && empty($value))) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'table':
      case 'list':
        $list = [];
        foreach ($element['#questions'] as $question_key => $question_label) {
          $answer_value = (isset($value[$question_key])) ? $value[$question_key] : NULL;
          $answer_text = YamlFormOptionsHelper::getOptionText($answer_value, $element['#answers']);
          $list[] = "$question_label: $answer_text";
        }
        return implode("\n", $list);

      default:
        return parent::formatText($element, $value, $answers);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [
      'likert_questions_format' => 'label',
      'likert_answers_format' => 'label',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    $form['likert'] = [
      '#type' => 'details',
      '#title' => $this->t('Likert questions and answers'),
      '#open' => TRUE,
      '#weight' => -10,
    ];
    $form['likert']['likert_questions_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Questions format'),
      '#options' => [
        'label' => $this->t('Question labels, the human-readable value (label)'),
        'key' => $this->t('Question keys, the raw value stored in the database (key)'),
      ],
      '#default_value' => $default_values['likert_questions_format'],
    ];
    $form['likert']['likert_answers_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Answers format'),
      '#options' => [
        'label' => $this->t('Answer labels, the human-readable value (label)'),
        'key' => $this->t('Answer keys, the raw value stored in the database (key)'),
      ],
      '#default_value' => $default_values['likert_answers_format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    $header = [];
    foreach ($element['#questions'] as $key => $label) {
      $header[] = ($options['likert_questions_format'] == 'key') ? $key : $label;
    }
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    $record = [];
    foreach ($element['#questions'] as $question_key => $question_label) {
      $answer_value = (isset($value[$question_key])) ? $value[$question_key] : NULL;
      if ($options['likert_answers_format'] == 'key') {
        $record[] = $answer_value;
      }
      else {
        $record[] = YamlFormOptionsHelper::getOptionText($answer_value, $element['#answers']);
      }
    }
    return $record;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'list';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'list' => $this->t('List'),
      'table' => $this->t('Table'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    $value = [];
    foreach ($element['#questions'] as $key => $question) {
      $keys = array_keys($element['#answers']);
      $value[$key] = $keys[array_rand($keys)];
    }
    return [$value];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['likert'] = [
      '#type' => 'details',
      '#title' => $this->t('Likert settings'),
      '#open' => TRUE,
    ];
    $form['likert']['questions'] = [
      '#title' => $this->t('Questions (YAML)'),
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#description' => $this->t('Key-value pairs MUST be specified as "safe_key: \'Some readable @type\'". Use of only alphanumeric characters and underscores is recommended in keys. One @type per line.', ['@type' => 'question']),
      '#required' => TRUE,
    ];
    $form['likert']['answers'] = [
      '#title' => $this->t('Answers'),
      '#type' => 'yamlform_element_options',
      '#likert' => TRUE,
      '#required' => TRUE,
    ];
    return $form;
  }

}
