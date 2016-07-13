<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormPluginElementController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Utility\YamlFormElementHelper;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for all YAML form elements.
 */
class YamlFormPluginElementController extends ControllerBase {

  /**
   * A element manager.
   *
   * @var \Drupal\Core\Render\ElementInfoManagerInterface
   */
  protected $elementManager;

  /**
   * A YAML form element plugin manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $yamlFormElementManager;

  /**
   * Constructs a YamlFormPluginBaseController object.
   *
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_manager
   *   A element plugin manager.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $yamlform_element_manager
   *   A YAML form element plugin manager.
   */
  public function __construct(ElementInfoManagerInterface $element_manager, YamlFormElementManagerInterface $yamlform_element_manager) {
    $this->elementManager = $element_manager;
    $this->yamlFormElementManager = $yamlform_element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.element_info'),
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function index() {
    $yamlform_form_element_rows = [];
    $element_rows = [];

    $default_properties = [
      '#title',
      '#description',
      '#required',
      '#default_value',
      '#title_display',
      '#prefix',
      '#suffix',
      '#field_prefix',
      '#field_suffix',
      '#private',
      '#unique',
      '#format',
    ];
    $default_properties = array_combine($default_properties, $default_properties);
    $test_enabled = (YamlForm::load('test_ui_element')) ? TRUE : FALSE;

    // Define a default element used to get default properties.
    $element = ['#type' => 'element'];

    $element_plugin_definitions = $this->elementManager->getDefinitions();
    foreach ($element_plugin_definitions as $element_plugin_id => $element_plugin_definition) {
      if ($this->yamlFormElementManager->hasDefinition($element_plugin_id)) {

        /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
        $yamlform_element = $this->yamlFormElementManager->createInstance($element_plugin_id);
        $yamlform_element_plugin_definition = $this->yamlFormElementManager->getDefinition($element_plugin_id);

        // Get the element's class hierachy.
        $class = get_class($yamlform_element);
        $parent_classes = [
          $this->getClassName($class),
        ];
        do {
          $parent_class = get_parent_class($class);
          $parent_class_name = $this->getClassName($parent_class);
          $parent_classes[] = $parent_class_name;
          $class = $parent_class;
        } while ($parent_class_name != 'YamlFormElementBase' && $class);
        $parent_classes = array_reverse($parent_classes);

        $default_format = $yamlform_element->getDefaultFormat();
        $format_names = array_keys($yamlform_element->getFormats());
        $formats = array_combine($format_names, $format_names);
        if (isset($formats[$default_format])) {
          $formats[$default_format] = '<b>' . $formats[$default_format] . '</b>';
        }

        $definitions = [
          'value' => $yamlform_element->hasValue($element),
          'container' => $yamlform_element->isContainer($element),
          'root' => $yamlform_element->isRoot($element),
          'hidden' => $yamlform_element_plugin_definition['hidden'],
          'multiline' => $yamlform_element_plugin_definition['multiline'],
          'multiple' => $yamlform_element_plugin_definition['multiple'],
        ];
        $settings = [];
        foreach ($definitions as $key => $value) {
          $settings[] = '<b>' . $key . '</b>: ' . ($value ? t('Yes') : t('No'));
        }

        $properties = array_keys(YamlFormElementHelper::addPrefix($yamlform_element->getDefaultProperties()));
        foreach ($properties as &$property) {
          if (!isset($default_properties[$property])) {
            $property = '<b>' . $property . '</b>';
          }
        }
        if (count($properties) >= 20) {
          $properties = array_slice($properties, 0, 20) + ['...' => '...'];
        }
        $operations = [];
        if ($test_enabled) {
          $operations['test'] = [
            'title' => $this->t('Test'),
            'url' => new Url('yamlform.element_plugins.test', ['type' => $element_plugin_id]),
          ];
        }
        if ($api_url = $yamlform_element->getPluginApiUrl()) {
          $operations['documentation'] = [
            'title' => $this->t('API Docs'),
            'url' => $api_url,
          ];
        }
        $yamlform_form_element_rows[$element_plugin_id] = [
          'data' => [
            $element_plugin_id,
            $yamlform_element->getPluginLabel(),
            ['data' => ['#markup' => implode('<br/> → ', $parent_classes)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $settings)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $properties)]],
            $formats ? ['data' => ['#markup' => '• ' . implode('<br/>• ', $formats)], 'nowrap' => 'nowrap'] : '',
            $element_plugin_definition['provider'],
            $yamlform_element_plugin_definition['provider'],
            $operations ? ['data' => ['#type' => 'operations', '#links' => $operations]] : '',
          ],
        ];
      }
      else {
        $element_rows[$element_plugin_id] = [
          $element_plugin_id,
          $element_plugin_definition['provider'],
        ];
      }
    }

    $build = [];

    ksort($yamlform_form_element_rows);
    $build['yamlform_elements'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Label'),
        $this->t('Class hierarchy'),
        $this->t('Definition'),
        $this->t('Properties'),
        $this->t('Formats'),
        $this->t('Provided by'),
        $this->t('Integrated by'),
        $this->t('Operations'),
      ],
      '#rows' => $yamlform_form_element_rows,
    ];

    ksort($element_rows);
    $build['elements'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional elements'),
      '#description' => t('Below are element that are available to a YAML form but do not have YAML form element plugin and/or require any additional integration'),
      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Provided by'),
        ],
        '#rows' => $element_rows,
        '#sticky' => TRUE,
      ],
    ];

    $build['#attached']['library'][] = 'yamlform/yamlform';

    return $build;
  }

  /**
   * Get a class's name without its namespace.
   *
   * @param string $class
   *   A class.
   *
   * @return string
   *   The class's name without its namespace.
   */
  protected function getClassName($class) {
    $parts = preg_split('#\\\\#', $class);
    return end($parts);
  }

}
