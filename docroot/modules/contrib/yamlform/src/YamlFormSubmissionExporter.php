<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionExporter.
 */

namespace Drupal\yamlform;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * YAML form submission exporter.
 */
class YamlFormSubmissionExporter implements YamlFormSubmissionExporterInterface {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * YAML form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $entityStorage;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $yamlformElementManager;

  /**
   * The YAML form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * Default export options.
   *
   * @var array
   */
  protected $defaultOptions;

  /**
   * YAML form element types.
   *
   * @var array
   */
  protected $elementTypes;

  /**
   * Constructs a YamlFormSubmissionExporter object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $yamlform_element_manager
   *   The YAML form element manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, QueryFactory $query_factory, YamlFormElementManagerInterface $yamlform_element_manager) {
    $this->configFactory = $config_factory;
    $this->entityStorage = $entity_manager->getStorage('yamlform_submission');
    $this->queryFactory = $query_factory;
    $this->yamlformElementManager = $yamlform_element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL) {
    $this->yamlform = $yamlform;
    $this->defaultOptions = NULL;
    $this->elementTypes = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlForm() {
    return $this->yamlform;
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceEntity(EntityInterface $entity = NULL) {
    $this->sourceEntity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceEntity() {
    return $this->sourceEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormOptions() {
    $name = $this->getYamlFormOptionsName();
    return \Drupal::state()->get($name, []);
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlFormOptions(array $options = []) {
    $name = $this->getYamlFormOptionsName();
    \Drupal::state()->set($name, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteYamlFormOptions() {
    $name = $this->getYamlFormOptionsName();
    \Drupal::state()->delete($name);
  }

  /**
   * Get options name for current YAML form and source entity.
   *
   * @return string
   *   Settings name as 'yamlform.export.{entity_type}.{entity_id}.
   */
  protected function getYamlFormOptionsName() {
    if ($entity = $this->getSourceEntity()) {
      return 'yamlform.export.' . $entity->getEntityTypeId() . '.' . $entity->id();
    }
    else {
      return 'yamlform.export.yamlform.' . $this->yamlform->id();
    }
  }

  /****************************************************************************/
  // Default options and form.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getDefaultExportOptions() {
    if (isset($this->defaultOptions)) {
      return $this->defaultOptions;
    }

    $this->defaultOptions = [
      'delimiter' => ',',
      'header_keys' => 'label',
      'excluded_columns' => [
        'uuid' => 'uuid',
        'token' => 'token',
        'changed' => 'changed',
        'data' => 'data',
        'yamlform_id' => 'yamlform_id',
      ],
      'entity_type' => '',
      'entity_id' => '',
      'range_type' => 'all',
      'range_latest' => '',
      'range_start' => '',
      'range_end' => '',
      'state' => 'all',
      'download' => TRUE,
    ];

    // Append element handler default options.
    $element_types = $this->getYamlFormElementTypes();
    $element_handlers = $this->yamlformElementManager->getInstances();
    foreach ($element_handlers as $element_type => $element_handler) {
      if (empty($element_types) || isset($element_types[$element_type])) {
        $this->defaultOptions += $element_handler->getExportDefaultOptions();
      }
    }

    return $this->defaultOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface $form_state) {
    $yamlform = $this->getYamlForm();
    $default_options = $this->getDefaultExportOptions();
    $values = $form_state->getValues();
    $default_values = NestedArray::mergeDeep($default_options, $values);

    $form['export']['#tree'] = TRUE;

    $form['export']['format'] = [
      '#type' => 'details',
      '#title' => $this->t('Format options'),
      '#open' => TRUE,
    ];
    $form['export']['format']['delimiter'] = [
      '#type' => 'select',
      '#title' => $this->t('Delimiter text format'),
      '#description' => $this->t('This is the delimiter used in the CSV/TSV file when downloading YAML form results. Using tabs in the export is the most reliable method for preserving non-latin characters. You may want to change this to another character depending on the program with which you anticipate importing results.'),
      '#options' => [
        ','  => $this->t('Comma (,)'),
        '\t' => $this->t('Tab (\t)'),
        ';'  => $this->t('Semicolon (;)'),
        ':'  => $this->t('Colon (:)'),
        '|'  => $this->t('Pipe (|)'),
        '.'  => $this->t('Period (.)'),
        ' '  => $this->t('Space ( )'),
      ],
      '#default_value' => $default_values['delimiter'],
    ];

    $form['export']['format']['header_keys'] = [
      '#type' => 'radios',
      '#title' => $this->t('Column header format'),
      '#description' => $this->t('Choose whether to show the label or field key in each column header.'),
      '#options' => [
        'label' => $this->t('Element titles (label)'),
        'key' => $this->t('Element names (key)'),
      ],
      '#default_value' => $default_values['header_keys'],
    ];

    // Build element specific export forms.
    // Grouping everything in $form['export']['elements'] so that element handlers can
    // assign #weight to its export options form.
    $form['export']['elements'] = [];
    $element_types = $this->getYamlFormElementTypes();
    $element_handlers = $this->yamlformElementManager->getInstances();
    foreach ($element_handlers as $element_type => $element_handler) {
      if (empty($element_types) || isset($element_types[$element_type])) {
        $element_handler->buildExportOptionsForm($form['export']['elements'], $form_state, $default_values);
      }
    }

    // All the remain options are only applicable to a YAML form's export.
    // @see Drupal\yamlform\Form\YamlFormResultsExportForm
    if (!$yamlform) {
      return;
    }

    // Elements.
    $form['export']['columns'] = [
      '#type' => 'details',
      '#title' => t('Column options'),
    ];
    $form['export']['columns']['excluded_columns'] = [
      '#type' => 'yamlform_excluded_columns',
      '#description' => $this->t('The selected columns will be included in the export.'),
      '#yamlform' => $yamlform,
      '#default_value' => $default_values['excluded_columns'],
    ];

    // Download options.
    $form['export']['download'] = [
      '#type' => 'details',
      '#title' => $this->t('Download options'),
      '#open' => TRUE,
    ];
    $form['export']['download']['download'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Download CSV'),
      '#description' => $this->t('If checked the CSV file will be automatically download to your local machine. If unchecked CSV file will be displayed as plain text within your browser.'),
      '#default_value' => $default_values['download'],
      '#access' => !$this->requiresBatch($yamlform),
    ];

    $source_entity = $this->getSourceEntity();
    if (!$source_entity) {
      $entity_types = $this->entityStorage->getSourceEntityTypes($yamlform);
      if ($entity_types) {
        $form['export']['download']['submitted'] = [
          '#type' => 'item',
          '#title' => $this->t('Submitted to'),
          '#description' => $this->t('Select the entity type and then enter the entity id.'),
          '#field_prefix' => '<div class="container-inline">',
          '#field_suffix' => '</div>',
        ];
        $form['export']['download']['submitted']['entity_type'] = [
          '#type' => 'select',
          '#title' => $this->t('Entity type'),
          '#title_display' => 'Invisible',
          '#options' => ['' => $this->t('All')] + $entity_types,
          '#default_value' => $default_values['entity_type'],
        ];
        $form['export']['download']['submitted']['entity_id'] = [
          '#type' => 'number',
          '#title' => $this->t('Entity id'),
          '#title_display' => 'Invisible',
          '#default_value' => $default_values['entity_id'],
          '#size' => 10,
          '#states' => [
            'invisible' => [
              ':input[name="export[download][submitted][entity_type]"]' => ['value' => ''],
            ],
          ],
        ];
      }
    }

    $form['export']['download']['range_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit to'),
      '#options' => [
        'all' => t('All'),
        'latest' => t('Latest'),
        'sid' => t('Submission ID'),
        'date' => t('Date'),
      ],
      '#default_value' => $default_values['range_type'],
    ];
    $form['export']['download']['latest'] = [
      '#type' => 'container',
      '#attributes' => ['class' => 'container-inline'],
      '#states' => [
        'visible' => [
          ':input[name="export[download][range_type]"]' => ['value' => 'latest'],
        ],
      ],
      'range_latest' => [
        '#type' => 'number',
        '#title' => $this->t('Number of submissions'),
        '#default_value' => $default_values['range_latest'],
      ],
    ];
    $ranges = [
      'sid' => ['#type' => 'number'],
      'date' => ['#type' => 'date'],
    ];
    foreach ($ranges as $key => $range_element) {
      $form['export']['download'][$key] = [
        '#type' => 'container',
        '#attributes' => ['class' => 'container-inline'],
        '#states' => [
          'visible' => [
            ':input[name="export[download][range_type]"]' => ['value' => $key],
          ],
        ],
      ];
      $form['export']['download'][$key]['range_start'] = $range_element + [
        '#title' => $this->t('From'),
        '#default_value' => $default_values['range_start'],
      ];
      $form['export']['download'][$key]['range_end'] = $range_element + [
        '#title' => $this->t('To'),
        '#default_value' => $default_values['range_end'],
      ];
    }

    // If drafts are allowed, provide options to filter download based on
    // submission state.
    $form['export']['download']['state'] = [
      '#type' => 'radios',
      '#title' => t('Submission state'),
      '#default_value' => $default_values['state'],
      '#options' => [
        'all' => t('Completed and draft submissions'),
        'completed' => t('Completed submissions only'),
        'draft' => t('Drafts only'),
      ],
      '#access' => $yamlform->getSetting('draft'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormValues(FormStateInterface $form_state) {
    $export_values = $form_state->getValue('export');
    $values = [];

    // Append download/range type and submitted.
    if (isset($export_values['download'])) {
      if (isset($export_values['download']['range_type'])) {
        $range_type = $export_values['download']['range_type'];
        $values['range_type'] = $range_type;
        if (isset($export_values['download'][$range_type])) {
          $values += $export_values['download'][$range_type];
        }
      }
      $values['state'] = $export_values['download']['state'];
      $values['download'] = $export_values['download']['download'];

      if (!empty($export_values['download']['submitted']['entity_type'])) {
        $values += $export_values['download']['submitted'];
      }
    }

    // Append format.
    if (isset($export_values['format'])) {
      $values += $export_values['format'];
    }

    // Append columns.
    if (isset($export_values['columns'])) {
      $values += $export_values['columns'];
    }

    // Append (and flatten) elements.
    // http://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array
    $default_options = $this->getDefaultExportOptions();
    array_walk_recursive($export_values['elements'], function($item, $key) use (&$values, $default_options) {
      if (isset($default_options[$key])) {
        $values[$key] = $item;
      }
    });

    return $values;
  }

  /****************************************************************************/
  // Generate and write.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function generate(YamlFormInterface $yamlform, array $export_options) {
    $field_definitions = $this->getFieldDefinitions($export_options);
    $elements = $this->getElements($yamlform, $export_options);

    // Convert tabs delimiter.
    if ($export_options['delimiter'] == '\t') {
      $export_options['delimiter'] = "\t";
    }

    // Build header and add to CSV.
    $this->writeHeader($yamlform, $field_definitions, $elements, $export_options);

    // Build records and add to CSV.
    $entity_ids = $this->getQuery($yamlform, $export_options)->execute();
    $yamlform_submissions = YamlFormSubmission::loadMultiple($entity_ids);
    $this->writeRecords($yamlform, $yamlform_submissions, $field_definitions, $elements, $export_options);
  }

  /**
   * {@inheritdoc}
   */
  public function writeHeader(YamlFormInterface $yamlform, array $field_definitions, array $elements, array $export_options) {
    $file_path = $this->getFilePath($yamlform, $export_options);

    $handle = fopen($file_path, 'w');
    $header = $this->buildHeader($field_definitions, $elements, $export_options);
    fputcsv($handle, $header, $export_options['delimiter']);
    fclose($handle);
  }

  /**
   * {@inheritdoc}
   */
  public function writeRecords(YamlFormInterface $yamlform, array $yamlform_submissions, array $field_definitions, array $elements, array $export_options) {
    $file_path = $this->getFilePath($yamlform, $export_options);

    $handle = fopen($file_path, 'a');
    foreach ($yamlform_submissions as $yamlform_submission) {
      $record = $this->buildRecord($yamlform_submission, $field_definitions, $elements, $export_options);
      fputcsv($handle, $record, $export_options['delimiter']);
    }
    fclose($handle);
  }

  /****************************************************************************/
  // Field definitions, elements, and query.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinitions(array $export_options) {
    $field_definitions = $this->entityStorage->getFieldDefinitions();
    $field_definitions = array_diff_key($field_definitions, $export_options['excluded_columns']);

    // Add custom entity reference field definitions which rely on the
    // entity type and entity id.
    if ($export_options['entity_reference_format'] == 'link' && isset($field_definitions['entity_type']) && isset($field_definitions['entity_id'])) {
      $field_definitions['entity_title'] = [
        'name' => 'entity_title',
        'title' => t('Submitted to: Entity title'),
        'type' => 'entity_title',
      ];
      $field_definitions['entity_url'] = [
        'name' => 'entity_url',
        'title' => t('Submitted to: Entity URL'),
        'type' => 'entity_url',
      ];
    }
    return $field_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getElements(YamlFormInterface $yamlform, array $export_options) {
    $element_columns = $yamlform->getElementsFlattenedAndHasValue();
    return array_diff_key($element_columns, $export_options['excluded_columns']);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(YamlFormInterface $yamlform, array $export_options) {
    $query = $this->queryFactory->get('yamlform_submission')->condition('yamlform_id', $yamlform->id());

    // Filter by source entity or submitted to.
    if ($source_entity = $this->getSourceEntity()) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    elseif ($export_options['entity_type']) {
      $query->condition('entity_type', $export_options['entity_type']);
      if ($export_options['entity_id']) {
        $query->condition('entity_id', $export_options['entity_id']);
      }
    }

    // Filter by sid or date range.
    switch ($export_options['range_type']) {
      case 'sid':
        if ($export_options['range_start']) {
          $query->condition('sid', $export_options['range_start'], '>=');
        }
        if ($export_options['range_end']) {
          $query->condition('sid', $export_options['range_end'], '<=');
        }
        break;

      case 'date':
        if ($export_options['range_start']) {
          $query->condition('created', strtotime($export_options['range_start']), '>=');
        }
        if ($export_options['range_end']) {
          $query->condition('created', strtotime($export_options['range_end']), '<=');
        }
        break;
    }

    // Filter by (completion) state.
    switch ($export_options['state']) {
      case 'draft';
        $query->condition('in_draft', 1);
        break;

      case 'completed';
        $query->condition('in_draft', 0);
        break;

    }

    // Filter by latest.
    if ($export_options['range_type'] == 'latest' && $export_options['range_latest']) {
      $query->range(0, (int) $export_options['range_latest']);
    }

    // Sort by sid with the latest one first.
    $query->sort('sid', 'DESC');

    return $query;
  }

  /****************************************************************************/
  // Header.
  /****************************************************************************/

  /**
   * Build CSV header using YAML form submission field definitions and YAML form element columns.
   *
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the CSV header.
   */
  protected function buildHeader(array $field_definitions, array $elements, array $export_options) {
    $header = [];
    foreach ($field_definitions as $field_definition) {
      // Build a YAML form element for each field definition so that we can
      // use YamlFormElement::buildExportHeader(array $element, $export_options).
      $element = [
        '#type' => ($field_definition['type'] == 'entity_reference') ? 'entity_autocomplete' : 'element',
        '#title' => (string) $field_definition['title'],
        '#yamlform_key' => (string) $field_definition['name'],
      ];
      $header = array_merge($header, $this->yamlformElementManager->invokeMethod('buildExportHeader', $element, $export_options));
    }

    // Build element columns headers.
    foreach ($elements as $element) {
      $header = array_merge($header, $this->yamlformElementManager->invokeMethod('buildExportHeader', $element, $export_options));
    }
    return $header;
  }

  /****************************************************************************/
  // Record.
  /****************************************************************************/

  /**
   * Build CSV record using YAML form submission, field definitions, and element columns.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the CSV record.
   */
  protected function buildRecord(YamlFormSubmissionInterface $yamlform_submission, array $field_definitions, array $elements, array $export_options) {
    $record = [];

    // Build record field definition columns.
    foreach ($field_definitions as $field_definition) {
      $this->formatRecordFieldDefinitionValue($record, $yamlform_submission, $field_definition, $export_options);
    }

    // Build record element columns.
    $data = $yamlform_submission->getData();
    foreach ($elements as $column_name => $element) {
      $value = (isset($data[$column_name])) ? $data[$column_name] : '';
      $record = array_merge($record, $this->yamlformElementManager->invokeMethod('buildExportRecord', $element, $value, $export_options));
    }
    return $record;
  }

  /**
   * Get the field definition value from a YAML form submission entity.
   *
   * @param array $record
   *   The record to be added to the CSV export.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param array $field_definition
   *   The field definition for the value.
   * @param array $export_options
   *   An associative array of export options.
   */
  protected function formatRecordFieldDefinitionValue(array &$record, YamlFormSubmissionInterface $yamlform_submission, array $field_definition, array $export_options) {
    $field_name = $field_definition['name'];
    $field_type = $field_definition['type'];
    switch ($field_type) {
      case 'created':
      case 'changed':
        $record[] = date('c', $yamlform_submission->get($field_name)->value);
        break;

      case 'entity_reference':
        $element = [
          '#type' => 'entity_autocomplete',
          '#target_type' => $field_definition['target_type'],
        ];
        $value = $yamlform_submission->get($field_name)->target_id;
        $record = array_merge($record, $this->yamlformElementManager->invokeMethod('buildExportRecord', $element, $value, $export_options));
        break;

      case 'entity_url':
      case 'entity_title':
        if (empty($yamlform_submission->entity_type->value) || empty($yamlform_submission->entity_id->value)) {
          $record[] = '';
          break;
        }

        $entity = entity_load($yamlform_submission->entity_type->value, $yamlform_submission->entity_id->value);
        if ($entity) {
          $record[] = ($field_type == 'entity_url') ? $entity->toUrl()->setOption('absolute', TRUE)->toString() : $entity->label();
        }
        else {
          $record[] = '';
        }
        break;

      default:
        $record[] = $yamlform_submission->get($field_name)->value;
        break;
    }
  }

  /**
   * Get element types from a YAML form.
   *
   * @return array
   *   An array of element types from a YAML form.
   */
  protected function getYamlFormElementTypes() {
    if (isset($this->elementTypes)) {
      return $this->elementTypes;
    }
    // If the YAML form is not set which only occurs on the admin settings form,
    // return an empty array.
    if (!isset($this->yamlform)) {
      return [];
    }

    $this->elementTypes = [];
    $elements = $this->yamlform->getElementsDecodedAndFlattened();
    // Alway include 'entity_autocomplete' export settings which is used to
    // expand a YAML form submission's entity references.
    $this->elementTypes['entity_autocomplete'] = 'entity_autocomplete';
    foreach ($elements as $element) {
      if (isset($element['#type'])) {
        $type = $this->yamlformElementManager->getElementPluginId($element);
        $this->elementTypes[$type] = $type;
      }
    }
    return $this->elementTypes;
  }

  /****************************************************************************/
  // Summary and download.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getTotal(YamlFormInterface $yamlform, array $export_options) {
    return $this->getQuery($yamlform, $export_options)->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchLimit() {
    return $this->configFactory->get('yamlform.settings')->get('batch.default_batch_export_size') ?: 500;
  }

  /**
   * {@inheritdoc}
   */
  public function requiresBatch(YamlFormInterface $yamlform) {
    return ($this->getTotal($yamlform, $this->getDefaultExportOptions()) > $this->getBatchLimit()) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilePath(YamlFormInterface $yamlform, array $export_options) {
    return $this->getFileTempDirectory() . '/' . $this->getFileName($yamlform, $export_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getFileTempDirectory() {
    return file_directory_temp();
  }

  /**
   * {@inheritdoc}
   */
  public function getFileName(YamlFormInterface $yamlform, array $export_options) {
    switch ($export_options['delimiter']) {
      case '\t':
      case "\t":
        $extension = 'tsv';
        break;

      default:
        $extension = 'csv';
        break;
    }
    return $yamlform->id() . '.' . $extension;
  }

}
