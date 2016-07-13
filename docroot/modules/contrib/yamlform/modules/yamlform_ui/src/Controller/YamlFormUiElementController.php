<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormUiElementController.
 */

namespace Drupal\yamlform_ui\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a list of YAML form element plugins to be added a form.
 */
class YamlFormUiElementController extends ControllerBase {

  /**
   * The YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * Constructs a YamlFormUiElementController object.
   *
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The YAML form element manager.
   */
  public function __construct(YamlFormElementManagerInterface $element_manager) {
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * Shows a list of YAML form elements  that can be added to a form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return array
   *   A render array as expected by the renderer.
   */
  public function listElements(Request $request, YamlFormInterface $yamlform) {
    $headers = [
      ['data' => $this->t('Element')],
      ['data' => $this->t('Category')],
      ['data' => $this->t('Operations')],
    ];

    $definitions = $this->elementManager->getDefinitions();

    // Get definitions with basic and advanced first and uncategorized elements
    // last.
    $no_category = '';
    $basic_category = (string) $this->t('Basic');
    $advanced_category = (string) $this->t('Advanced');
    $grouped_definitions = $this->elementManager->getGroupedDefinitions($definitions);
    $uncategorized = $grouped_definitions[$no_category];
    $definitions = [];
    $definitions += $grouped_definitions[$basic_category];
    $definitions += $grouped_definitions[$advanced_category];
    unset($grouped_definitions[$basic_category], $grouped_definitions[$advanced_category], $grouped_definitions[$no_category]);
    foreach ($grouped_definitions as $grouped_definition) {
      $definitions += $grouped_definition;
    }
    $definitions += $uncategorized;

    $parent = $request->query->get('parent');
    $rows = [];
    foreach ($definitions as $plugin_id => $plugin_definition) {
      /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
      // Skip wizard page which has a dedicated URL.
      if ($plugin_id == 'yamlform_wizard_page') {
        continue;
      }
      // Skip hidden plugins.
      if ($plugin_definition['hidden']) {
        continue;
      }

      $row = [];
      $row['title']['data'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="yamlform-ui-filter-text-source">{{ label }}</div>',
        '#context' => [
          'label' => $plugin_definition['label'],
        ],
      ];
      $row['category']['data'] = (isset($plugin_definition['category'])) ? $plugin_definition['category'] : $this->t('Other');
      $links['add'] = [
        'title' => $this->t('Add element'),
        'url' => Url::fromRoute('entity.yamlform_ui.element.add_form', ['yamlform' => $yamlform->id(), 'type' => $plugin_id]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 800,
          ]),
        ],
      ];
      if ($parent) {
        $links['add']['query']['parent'] = $parent;
      }
      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
      $rows[] = $row;
    }

    $build['#attached']['library'][] = 'yamlform_ui/yamlform_ui.admin';

    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by element name'),
      '#attributes' => [
        'class' => ['yamlform-ui-filter-text'],
        'data-element' => '.yamlform-ui-add-table',
        'title' => $this->t('Enter a part of the element name to filter by.'),
      ],
    ];

    $build['elements'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No element available.'),
      '#attributes' => [
        'class' => ['yamlform-ui-add-table'],
      ],
    ];

    return $build;
  }

}
