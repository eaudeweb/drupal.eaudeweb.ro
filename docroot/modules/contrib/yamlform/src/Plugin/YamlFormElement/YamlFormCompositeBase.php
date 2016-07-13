<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\YamlFormCompositeBase.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element as RenderElement;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\yamlform\Entity\YamlFormOptions;
use Drupal\yamlform\Utility\YamlFormOptionsHelper;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormInterface;

/**
 * Provides a base for composite elements.
 */
abstract class YamlFormCompositeBase extends YamlFormElementBase {

  /**
   * Get composite elements.
   *
   * @return array
   *   An array of composite elements.
   */
  abstract protected function getCompositeElements();

  /**
   * Get initialized composite element.
   *
   * @param array &$element
   *   A composite element.
   *
   * @return array
   *   The initialized composite test element.
   */
  abstract protected function getInitializedCompositeElement(array &$element);

  /**
   * Format composite element value into lines of text.
   *
   * @param array $element
   *   A composite element.
   * @param array $value
   *   Composite element values.
   *
   * @return array
   *   Composite element values converted into lines of text.
   */
  protected function formatLines(array $element, array $value) {
    $items = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      if (isset($value[$composite_key]) && $value[$composite_key] != '') {
        $composite_element = $composite_elements[$composite_key];
        $composite_title = $composite_element['#title'];
        $composite_value = $value[$composite_key];
        $items[$composite_key] = "$composite_title: $composite_value";
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = [
      'title' => '',
      'description' => '',
      'default_value' => [],
      'required' => FALSE,
      'title_display' => '',
      'prefix' => '',
      'suffix' => '',
    ];

    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      // Get #type, #title, and #option from composite elements.
      foreach ($composite_element as $composite_property_key => $composite_property_value) {
        if (in_array($composite_property_key, ['#type', '#title', '#options'])) {
          $property_key = str_replace('#', $composite_key . '__', $composite_property_key);
          if ($composite_property_value instanceof TranslatableMarkup) {
            $properties[$property_key] = (string) $composite_property_value;
          }
          else {
            $properties[$property_key] = $composite_property_value;
          }
        }
      }
      if (isset($properties[$composite_key . '__type'])) {
        $properties[$composite_key . '__description'] = FALSE;
        $properties[$composite_key . '__required'] = FALSE;
      }
      $properties[$composite_key . '__access'] = TRUE;
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');

    $form['validation']['required']['#description'] = $this->t('Check this option if the user must enter a value for all elements.');
    $form['#attached']['library'][] = 'yamlform/yamlform.element.composite';

    $form['composite'] = [
      '#type' => 'details',
      '#title' => $this->t('@title settings', ['@title' => $this->getPluginLabel()]),
      '#open' => TRUE,
    ];

    $header = [
      $this->t('Key'),
      $this->t('Title/Description'),
      $this->t('Type/Options'),
      $this->t('Required'),
      $this->t('Visible'),
    ];

    $rows = [];
    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      $title = (isset($composite_element['#title'])) ? $composite_element['#title'] : $composite_key;
      $type = isset($composite_element['#type']) ? $composite_element['#type'] : NULL;
      $t_args = ['@title' => $title];
      $state_disabled = [
        'disabled' => [
          ':input[name="properties[' . $composite_key . '__access]"]' => [
              'checked' => FALSE,
          ],
        ],
      ];

      $row = [];

      // Key.
      $row[$composite_key . '__key'] = [
        '#markup' => $composite_key,
        '#access' => TRUE,
      ];

      // Title and description.
      if ($type) {
        $row['title_and_description'] = [
          'data' => [
            $composite_key . '__title' => [
              '#title' => $this->t('@title title', $t_args),
              '#title_display' => 'invisible',
              '#type' => 'textfield',
              '#required' => TRUE,
              '#attributes' => ['style' => 'width: 100%; margin-bottom: 5px'],
              '#states' => $state_disabled,
            ],
            $composite_key . '__description' => [
              '#type' => 'textarea',
              '#rows' => 2,
              '#attributes' => ['style' => 'width: 100%; margin-bottom: 5px'],
              '#states' => $state_disabled,
            ],
          ],
        ];
      }
      else {
        $row['title_and_description'] = ['data' => ['']];
      }

      // Type and options.
      $row['type_and_options'] = [];
      switch ($type) {
        case 'select':
          if ($composite_options = $this->getCompositeElementOptions($composite_key)) {
            $row['type_and_options']['data'][$composite_key . '__type'] = [
              '#type' => 'select',
              '#required' => TRUE,
              '#options' => [
                'select' => $this->t('Select'),
                'yamlform_select_other' => $this->t('Select other'),
                'textfield' => $this->t('Text field'),
              ],
              '#attributes' => ['style' => 'width: 100%; margin-bottom: 5px'],
              '#states' => $state_disabled,
            ];
            $row['type_and_options']['data'][$composite_key . '__options'] = [
              '#type' => 'select',
              '#options' => $composite_options,
              '#required' => TRUE,
              '#attributes' => ['style' => 'width: 100%;'],
              '#states' => $state_disabled + [
                  'invisible' => [
                    ':input[name="properties[' . $composite_key . '__type]"]' => [
                      'value' => 'textfield',
                    ],
                  ],
                ]
            ];
          }
          else {
            $row['type_and_options']['data'][$composite_key . '__type'] = [
              '#markup' => $element_manager->getElementInstance($composite_element)->getPluginLabel(),
            ];
          }
          break;

        case 'tel':
          $row['type_and_options']['data'][$composite_key . '__type'] = [
            '#type' => 'select',
            '#required' => TRUE,
            '#options' => [
              'tel' => $this->t('Telephone'),
              'textfield' => $this->t('Text field'),
            ],
            '#attributes' => ['style' => 'width: 100%; margin-bottom: 5px'],
            '#states' => $state_disabled,
          ];
          break;

        default:
          $row['type_and_options']['data'][$composite_key . '__type'] = [
            '#markup' => $element_manager->getElementInstance($composite_element)->getPluginLabel(),
          ];
          break;
      }

      // Required.
      if ($type) {
        $row[$composite_key . '__required'] = [
          '#type' => 'checkbox',
          '#return_value' => TRUE,
          '#states' => [
            'disabled' => [
              ':input[name="properties[required]"]' => [
                'checked' => TRUE,
              ],
            ],
          ],
        ];
      }
      else {
        $row[$composite_key . '__required'] = ['data' => ['']];
      }

      // Access.
      $row[$composite_key . '__access'] = [
        '#type' => 'checkbox',
        '#return_value' => TRUE,
      ];

      $rows[$composite_key] = $row;
    }

    $form['composite']['elements'] = [
      '#type' => 'table',
      '#header' => $header,
    ] + $rows;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiline(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $format = $this->getFormat($element);
    switch ($format) {
      case 'raw':
        $items = [];
        $composite_elements = $this->getInitializedCompositeElement($element);
        foreach (RenderElement::children($composite_elements) as $composite_key) {
          $composite_element = $composite_elements[$composite_key];
          $composite_title = $composite_element['#title'];
          $composite_value = $value[$composite_key];
          if ($composite_value !== '') {
            $items[$composite_key] = ['#markup' => "<b>$composite_title:</b> $composite_value"];
          }
        }
        return [
          '#theme' => 'item_list',
          '#items' => $items,
        ];

      default:
        $lines = $this->formatLines($element, $value);
        foreach ($lines as $key => $line) {
          if ($key == 'email') {
            $lines[$key] = [
              '#type' => 'link',
              '#title' => $line,
              '#url' => \Drupal::pathValidator()->getUrlIfValid('mailto:' . $line),
            ];
          }
          else {
            $lines[$key] = ['#markup' => $line];
          }
          $lines[$key]['#suffix'] = '<br/>';
        }
        return $lines;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    // Return empty value.
    if (is_array($value) && empty($value)) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'raw':
        $items = [];
        $composite_elements = $this->getInitializedCompositeElement($element);
        foreach (RenderElement::children($composite_elements) as $composite_key) {
          $composite_element = $composite_elements[$composite_key];
          $composite_title = $composite_element['#title'];
          $composite_value = $value[$composite_key];
          if ($composite_value !== '') {
            $items[$composite_key] = "$composite_title: $composite_value";
          }
        }
        return implode("\n", $items);

      default:
        $lines = $this->formatLines($element, $value);
        return implode("\n", $lines);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [
      'composite_header_prefix' => TRUE,
      'composite_element_item_format' => 'label',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    $form['composite'] = [
      '#type' => 'details',
      '#title' => $this->t('Composite element'),
      '#open' => TRUE,
      '#weight' => -10,
    ];
    $form['composite']['composite_header_prefix'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Composite header prefix'),
      '#description' => $this->t("Prefix the header title with composite element's name. (ie: key or title)"),
      '#default_value' => $default_values['composite_header_prefix'],
    ];
    $form['composite']['composite_element_item_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Composite element item format'),
      '#options' => [
        'label' => $this->t('Option labels, the human-readable value (label)'),
        'key' => $this->t('Option values, the raw value stored in the database (key)'),
      ],
      '#default_value' => $default_values['composite_element_item_format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    $header = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    if ($options['composite_header_prefix']) {
      $prefix_title = ((!empty($element['#title'])) ? $element['#title'] : $element['#yamlform_key']) . ': ';
      $prefix_key = $element['#yamlform_key'] . '__';
    }
    else {
      $prefix_title = '';
      $prefix_key = '';
    }
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $composite_element = $composite_elements[$composite_key];
      if (isset($composite_element['#access']) && $composite_element['#access']) {
        continue;
      }

      if ($options['header_keys'] == 'label' && !empty($composite_element['#title'])) {
        $header[] = $prefix_title . $composite_element['#title'];
      }
      else {
        $header[] = $prefix_key . $composite_key;
      }
    }
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    $record = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $composite_element = $composite_elements[$composite_key];
      if (isset($composite_element['#access']) && $composite_element['#access']) {
        continue;
      }

      if ($options['composite_element_item_format'] == 'label' && $composite_element['#type'] != 'textfield' && !empty($composite_element['#options'])) {
        $record[] = YamlFormOptionsHelper::getOptionText($value[$composite_key], $composite_element['#options']);
      }
      else {
        $record[] = (isset($value[$composite_key])) ? $value[$composite_key] : NULL;
      }
    }
    return $record;
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    /** @var \Drupal\yamlform\YamlFormSubmissionGenerateInterface $generate */
    $generate = \Drupal::service('yamlform_submission.generate');

    $value = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $value[$composite_key] = $generate->getTestValue($yamlform, $composite_key, $composite_elements[$composite_key]);
    }
    return [$value];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFormProperties(array &$form, FormStateInterface $form_state) {
    $properties = parent::getConfigurationFormProperties($form, $form_state);
    foreach ($properties as $key => $value) {
      // Convert composite element access and required to boolean value.
      if (strpos($key, '__access') || strpos($key, '__required')) {
        $properties[$key] = (boolean) $value;
      }
      // If the entire element is required remove required property for
      // composite elements.
      if (!empty($properties['required']) && strpos($key, '__required')) {
        unset($properties[$key]);
      }
    }
    return $properties;
  }

  /**
   * Get YAML form option keys for composite element based on the composite element's key.
   *
   * @param string $composite_key
   *   A composite element's key.
   *
   * @return array
   *   An array YAML form options.
   */
  protected function getCompositeElementOptions($composite_key) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface[] $yamlform_options */
    $yamlform_options = YamlFormOptions::loadMultiple();
    $options = [];
    foreach ($yamlform_options as $key => $yamlform_option) {
      if (strpos($key, $composite_key) === 0) {
        $options[$key] = $yamlform_option->label();
      }
    }
    return $options;
  }

}
