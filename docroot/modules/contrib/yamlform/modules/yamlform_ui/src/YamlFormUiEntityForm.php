<?php

/**
 * @file
 * Contains \Drupal\yamlform_ui\YamlFormUiEntityForm.
 */

namespace Drupal\yamlform_ui;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\yamlform\YamlFormEntityForm;

/**
 * Base for controller for YAML form UI.
 */
class YamlFormUiEntityForm extends YamlFormEntityForm {

  /**
   * {@inheritdoc}
   */
  public function editForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();

    if ($yamlform->isNew()) {
      return $form;
    }

    $dialog_attributes = [
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => 800,
      ]),
    ];

    // Build table header.
    $header = [];
    $header['title'] = $this->t('Title');
    $header['add'] = [
      'data' => '',
      'class' => [RESPONSIVE_PRIORITY_MEDIUM, 'yamlform-ui-element-operations'],
    ];
    $header['key'] = [
      'data' => $this->t('Key'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['type'] = [
      'data' => $this->t('Type'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['weight'] = $this->t('Weight');
    $header['parent'] = $this->t('Parent');
    if (!$yamlform->isNew()) {
      $header['operations'] = [
        'data' => $this->t('Operations'),
        'class' => ['yamlform-ui-element-operations'],
      ];
    }

    // Build table rows from elements.
    $rows = [];
    $elements = $this->getOrderableElements();
    foreach ($elements as $element) {
      $key = $element['#yamlform_key'];

      $plugin_id = $this->elementManager->getElementPluginId($element);

      /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
      $yamlform_element = $this->elementManager->createInstance($plugin_id);

      $is_container = $yamlform_element->isContainer($element);
      $is_root = $yamlform_element->isRoot($element);

      // Get row class names.
      $row_class = ['draggable'];
      if ($is_root) {
        $row_class[] = 'tabledrag-root';
        $row_class[] = 'yamlform-ui-element-root';
      }
      if (!$is_container) {
        $row_class[] = 'tabledrag-leaf';
      }
      if ($is_container) {
        $row_class[] = 'yamlform-ui-element-container';
      }
      if (!empty($element['#type'])) {
        $row_class[] = 'yamlform-ui-element-type-' . $element['#type'];
      }
      $row_class[] = 'yamlform-ui-element-container';

      $rows[$key]['#attributes']['class'] = $row_class;

      $indentation = NULL;
      if ($element['#yamlform_depth']) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $element['#yamlform_depth'],
        ];
      }

      $rows[$key]['title'] = [
        '#markup' => $element['#title'],
        '#prefix' => !empty($indentation) ? drupal_render($indentation) : '',
      ];
      if ($is_container && !$yamlform->hasTranslations()) {
        $route_parameters = [
          'yamlform' => $yamlform->id(),
        ];
        $route_options = ['query' => ['parent' => $key]];
        $rows[$key]['add'] = [
          '#type' => 'link',
          '#title' => $this->t('Add element'),
          '#url' => new Url('entity.yamlform_ui.element', $route_parameters, $route_options),
          '#attributes' => [
            'class' => ['use-ajax', 'button', 'button-action', 'button--primary', 'button--small'],
          ] + $dialog_attributes ,
        ];
      }
      else {
        $rows[$key]['add'] = ['#markup' => ''];
      }

      $rows[$key]['name'] = [
        '#markup' => $element['#yamlform_key'],
      ];

      $rows[$key]['type'] = [
        '#markup' => $yamlform_element->getPluginLabel(),
      ];

      $rows[$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for ID @id', ['@id' => $key]),
        '#title_display' => 'invisible',
        '#default_value' => $element['#weight'],
        '#attributes' => [
          'class' => ['row-weight'],
        ],
        '#delta' => 50,
      ];

      $rows[$key]['parent']['key'] = [
        '#parents' => ['elements_reordered', $key, 'key'],
        '#type' => 'hidden',
        '#value' => $key,
        '#attributes' => [
          'class' => ['row-key'],
        ],
      ];
      $rows[$key]['parent']['parent_key'] = [
        '#parents' => ['elements_reordered', $key, 'parent_key'],
        '#type' => 'textfield',
        '#size' => 20,
        '#title' => $this->t('Parent'),
        '#title_display' => 'invisible',
        '#default_value' => $element['#yamlform_parent_key'],
        '#attributes' => [
          'class' => ['row-parent-key'],
          'readonly' => 'readonly',
        ],
      ];

      if (!$yamlform->isNew()) {
        $rows[$key]['operations'] = [
          '#type' => 'operations',
        ];
        $rows[$key]['operations']['#links']['edit'] = [
          'title' => $this->t('Edit'),
          'url' => new Url('entity.yamlform_ui.element.edit_form', ['yamlform' => $yamlform->id(), 'key' => $key]),
          'attributes' => ['class' => ['use-ajax']] + $dialog_attributes,
        ];
        if (!$yamlform->hasTranslations()) {
          $rows[$key]['operations']['#links']['duplicate'] = [
            'title' => $this->t('Duplicate'),
            'url' => new Url('entity.yamlform_ui.element.duplicate_form', [
              'yamlform' => $yamlform->id(),
              'key' => $key,
            ]),
            'attributes' => ['class' => ['use-ajax']] + $dialog_attributes,
          ];
          $rows[$key]['operations']['#links']['delete'] = [
            'title' => $this->t('Delete'),
            'url' => new Url('entity.yamlform_ui.element.delete_form', [
              'yamlform' => $yamlform->id(),
              'key' => $key
            ]),
          ];
        }
      }
    }

    // Must manually add local actions to the form because we can't alter local
    // actions and add the needed dialog attributes.
    // @see https://www.drupal.org/node/2585169
    if (!$yamlform->hasTranslations()) {
      $form['local_actions'] = [
        'add_element' => [
          '#type' => 'link',
          '#title' => $this->t('Add element'),
          '#url' => new Url('entity.yamlform_ui.element', ['yamlform' => $yamlform->id()]),
          '#attributes' => [
              'class' => ['use-ajax', 'button', 'button-action', 'button--primary', 'button--small'],
            ] + $dialog_attributes,
          'add_page' => [
            '#type' => 'link',
            '#title' => $this->t('Add page'),
            '#url' => new Url('entity.yamlform_ui.element.add_page_form', ['yamlform' => $yamlform->id()]),
            '#attributes' => [
                'class' => ['use-ajax', 'button', 'button-action', 'button--primary', 'button--small'],
              ] + $dialog_attributes,
          ],
        ],
      ];
    }

    $form['elements_reordered'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('Please add elements to this form.'),
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'row-parent-key',
          'source' => 'row-key',
          'hidden' => TRUE, /* hides the WEIGHT & PARENT tree columns below */
          'limit' => FALSE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'row-weight',
        ],
      ],
    ] + $rows;

    $form['#attached']['library'][] = 'yamlform_ui/yamlform_ui';

    // Must preload CodeMirror libarary so that the window.dialog:aftercreate
    // trigger is set before any dialogs are opened.
    // @see js/yamlform.element.codemirror.js
    $form['#attached']['library'][] = 'yamlform/codemirror.yaml';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();

    // Don't validate new YAML forms because they don't have any initial
    // elements.
    if ($yamlform->isNew()) {
      return;
    }

    // Get raw flattened elements that will be used to rebuild element's YAML
    // hierarchy.
    $elements_flattened = $yamlform->getElementsDecodedAndFlattened();

    // Get the reordered elements and sort them by weight.
    $elements_reordered = $form_state->getValue('elements_reordered');
    uasort($elements_reordered, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    // Make sure the reordered element keys and match the existing element keys.
    if (array_diff_key($elements_reordered, $elements_flattened)) {
      $form_state->setError($form['elements_reordered'], $this->t('The elements have been unexpectedly altered. Please try again'));
    }

    // Validate parent key and add children to ordered elements.
    foreach ($elements_reordered as $key => $table_element) {
      $parent_key = $table_element['parent_key'];

      // Validate the parent key.
      if ($parent_key && !isset($elements_flattened[$parent_key])) {
        $form_state->setError($form['elements_reordered'], $this->t('Parent %parent_key does not exist.', ['%parent_key' => $parent_key]));
        return;
      }

      // Add this key to the parent's children.
      $elements_reordered[$parent_key]['children'][$key] = $key;
    }

    // Rebuild elements to reflect new hierarchy.
    $elements_updated = [];
    // Preserve the original elements root properties.
    $elements_original = Yaml::decode($yamlform->get('elements'));
    foreach ($elements_original as $key => $value) {
      if (Element::property($key)) {
        $elements_updated[$key] = $value;
      }
    }
    $this->buildUpdatedElementsRecursive($elements_updated, '', $elements_reordered, $elements_flattened);

    // Update the YAML form's elements.
    $yamlform->setElements($elements_updated);

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save elements');
    return $actions;
  }

  /**
   * Build updated elements using the new parent child relationship.
   *
   * @param array $elements
   *   An associative array that will be populated with updated elements
   *   hierarchy.
   * @param string $key
   *   The current element key.  The blank empty key represents the elements root.
   * @param array $elements_reordered
   *   An associative array contain the reordered elements parent child
   *   relationship.
   * @param array $elements_flattened
   *   An associative array containing the raw flattened elements that will copied
   *   into the updated elements hierarchy.
   */
  protected function buildUpdatedElementsRecursive(array &$elements, $key, array $elements_reordered, array $elements_flattened) {
    if (!isset($elements_reordered[$key]['children'])) {
      return;
    }

    foreach ($elements_reordered[$key]['children'] as $key) {
      $elements[$key] = $elements_flattened[$key];
      $this->buildUpdatedElementsRecursive($elements[$key], $key, $elements_reordered, $elements_flattened);
    }
  }

  /**
   * Get YAML form's elements as an associative array of orderable elements.
   *
   * @return array
   *   An associative array of orderable elements.
   */
  protected function getOrderableElements() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();

    $elements = $yamlform->getElementsInitializedAndFlattened();
    $weights = [];
    foreach ($elements as &$element) {
      $parent_key = $element['#yamlform_parent_key'];
      if (!isset($weights[$parent_key])) {
        $element['#weight'] = $weights[$parent_key] = -50;
      }
      else {
        $element['#weight'] = $weights[$parent_key]++;
      }

      if (empty($element['#type'])) {
        if (isset($element['#theme'])) {
          $element['#type'] = $element['#theme'];
        }
        elseif (isset($element['#markup'])) {
          $element['#type'] = 'markup';
        }
        else {
          $element['#type'] = '';
        }
      }

      if (empty($element['#title'])) {
        $element['#title'] = '&lt;' . ((string) t('blank')) . '&gt;';
      }
    }
    return $elements;
  }

}
