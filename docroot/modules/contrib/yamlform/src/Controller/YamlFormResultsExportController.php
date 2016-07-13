<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormResultsExportController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Drupal\yamlform\YamlFormSubmissionExporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for YAML form submission export.
 */
class YamlFormResultsExportController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The YAML form submission exporter.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionExporterInterface
   */
  protected $exporter;

  /**
   * YAML form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $yamlFormRequest;

  /**
   * Constructs a new YamlFormResultsExportController object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionExporterInterface $yamlform_submission_exporter
   *   The YAML form submission exported.
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The YAML form request handler.
   */
  public function __construct(YamlFormSubmissionExporterInterface $yamlform_submission_exporter, YamlFormRequestInterface $yamlform_request) {
    $this->exporter = $yamlform_submission_exporter;
    $this->yamlFormRequest = $yamlform_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform_submission.exporter'),
      $container->get('yamlform.request')
    );
  }

  /**
   * Returns YAML form submission as a CSV.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
   *   A response that renders or redirects to the CSV file.
   */
  public function index(Request $request) {
    list($yamlform, $source_entity) = $this->yamlFormRequest->getYamlFormEntities();
    $this->exporter->setYamlForm($yamlform);
    $this->exporter->setSourceEntity($source_entity);

    $query = $request->query->all();
    unset($query['destination']);
    if (isset($query['filename'])) {
      $build = $this->formBuilder()->getForm('Drupal\yamlform\Form\YamlFormResultsExportForm');

      // Redirect to file export.
      $file_path = $this->exporter->getFileTempDirectory() . '/' . $query['filename'];
      if (file_exists($file_path)) {
        $file_url = Url::fromRoute('entity.yamlform.results_export_file', ['yamlform' => $yamlform->id(), 'filename' => $query['filename']], ['absolute' => TRUE])->toString();
        drupal_set_message(t('Export creation complete. Your download should begin now. If it does not start, <a href=":href">download the file here</a>. This file may only be downloaded once.', [':href' => $file_url]));
        $build['#attached']['html_head'][] = [
          [
            '#tag' => 'meta',
            '#attributes' => [
              'http-equiv' => 'refresh',
              'content' => '0; url=' . $file_url,
            ],
          ],
          'yamlform_results_export_download_file_refresh',
        ];
      }

      return $build;
    }
    elseif ($query) {

      if (!empty($query['excluded_columns']) && is_string($query['excluded_columns'])) {
        $excluded_columns = explode(',', $query['excluded_columns']);
        $query['excluded_columns'] = array_combine($excluded_columns, $excluded_columns);
      }

      $export_options = $query + $this->exporter->getDefaultExportOptions();
      if ($this->exporter->getTotal($yamlform, $export_options) >= $this->exporter->getBatchLimit()) {
        self::batchSet($yamlform, $export_options);
        return batch_process(Url::fromRoute('entity.yamlform.results_export', ['yamlform' => $yamlform->id()]));
      }
      else {
        $this->exporter->generate($yamlform, $export_options);
        $file_path = $this->exporter->getFilePath($yamlform, $export_options);
        return $this->downloadFile($file_path, $export_options['download']);
      }

    }
    else {
      return $this->formBuilder()->getForm('Drupal\yamlform\Form\YamlFormResultsExportForm', $yamlform);
    }
  }

  /**
   * Returns YAML form submission results as CSV file.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param string $filename
   *   CSV file name.
   *
   * @return array|\Symfony\Component\HttpFoundation\Response
   *   A response that renders or redirects to the CSV file.
   */
  public function file(Request $request, YamlFormInterface $yamlform, $filename) {
    $file_path = $this->exporter->getFileTempDirectory() . '/' . $filename;
    if (!file_exists($file_path)) {
      $t_args = [
        ':href' => Url::fromRoute('entity.yamlform.results_export', ['yamlform' => $yamlform->id()])->toString(),
      ];
      $build = [
        '#markup' => $this->t('No export file ready for download. The file may have already been downloaded by your browser. Visit the <a href=":href">download export form</a> to create a new export.', $t_args),
      ];
      return $build;
    }
    else {
      return $this->downloadFile($file_path);
    }
  }

  /**
   * Download generated CSV file.
   *
   * @param string $file_path
   *   The paths the generate CSV file.
   * @param bool $download
   *   Download the generated CSV file. Default to TRUE.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response object containing the CSV file.
   */
  public function downloadFile($file_path, $download = TRUE) {
    // Return the CSV files.
    $csv = file_get_contents($file_path);
    unlink($file_path);

    if ($download) {
      $extension = pathinfo($file_path, PATHINFO_EXTENSION);
      switch ($extension) {
        case 'tsv';
          $content_type = 'text/tab-separated-values';
          break;

        case 'csv';
          $content_type = 'text/csv';
          break;

        default:
          $content_type = 'text/plain';
          break;
      }
      $headers = [
        'Content-Length' => strlen($csv),
        'Content-Type' => $content_type,
        'Content-Disposition' => 'attachment; filename="' . basename($file_path) . '"',
      ];
    }
    else {
      $headers = [
        'Content-Length' => strlen($csv),
        'Content-Type' => 'text/plain; charset=utf-8',
      ];
    }
    return new Response($csv, 200, $headers);
  }

  /****************************************************************************/
  // Batch functions.
  // Using static method to prevent the service container from being serialized.
  // "Prevents exception 'AssertionError' with message 'The container was serialized.'."
  /****************************************************************************/

  /**
   * Batch API; Initialize batch operations.
   *
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An array of export options.
   *
   * @see http://www.jeffgeerling.com/blogs/jeff-geerling/using-batch-api-build-huge-csv
   */
  static public function batchSet(YamlFormInterface $yamlform, array $export_options) {
    if (!empty($export_options['excluded_columns']) && is_string($export_options['excluded_columns'])) {
      $excluded_columns = explode(',', $export_options['excluded_columns']);
      $export_options['excluded_columns'] = array_combine($excluded_columns, $excluded_columns);
    }

    /** @var \Drupal\yamlform\YamlFormSubmissionExporterInterface $exporter */
    $exporter = \Drupal::service('yamlform_submission.exporter');

    $parameters = [
      $yamlform,
      $exporter->getFieldDefinitions($export_options),
      $exporter->getElements($yamlform, $export_options),
      $export_options,
    ];
    $batch = [
      'title' => t('Exporting submissions'),
      'init_message' => t('Creating export file'),
      'error_message' => t('The export file could not be created because an error occurred.'),
      'operations' => [
        [['\Drupal\yamlform\Controller\YamlFormResultsExportController', 'batchProcess'], $parameters],
      ],
      'finished' => ['\Drupal\yamlform\Controller\YamlFormResultsExportController', 'batchFinish'],
    ];

    batch_set($batch);
  }

  /**
   * Batch API callback; Write the header and rows of the export to the export file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   * @param array $field_definitions
   *   YAML form submission field definitions.
   * @param array $element_columns
   *   YAML form elements as columns.
   * @param array $export_options
   *   An associative array of export options.
   * @param mixed|array $context
   *   The batch current context.
   */
  static public function batchProcess(YamlFormInterface $yamlform, array $field_definitions, array $element_columns, array $export_options, &$context) {
    /** @var \Drupal\yamlform\YamlFormSubmissionExporterInterface $exporter */
    $exporter = \Drupal::service('yamlform_submission.exporter');
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_sid'] = 0;
      $context['sandbox']['max'] = $exporter->getQuery($yamlform, $export_options)->count()->execute();
      $context['results']['yamlform'] = $yamlform;
      $context['results']['export_options'] = $export_options;
      $exporter->writeHeader($yamlform, $field_definitions, $element_columns, $export_options);
    }

    // Write CSV records.
    $query = $exporter->getQuery($yamlform, $export_options);
    $query->condition('sid', $context['sandbox']['current_sid'], '>');
    $query->range(0, $exporter->getBatchLimit());
    $entity_ids = $query->execute();
    $yamlform_submissions = YamlFormSubmission::loadMultiple($entity_ids);
    $exporter->writeRecords($yamlform, $yamlform_submissions, $field_definitions, $element_columns, $export_options);

    // Track progress.
    $context['sandbox']['progress'] += count($yamlform_submissions);
    $context['sandbox']['current_sid'] = ($yamlform_submissions) ? end($yamlform_submissions)->id() : 0;

    $context['message'] = t('Exported @count of @total submissions...', ['@count' => $context['sandbox']['progress'], '@total' => $context['sandbox']['max']]);

    // Track finished.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch API callback; Completed export.
   *
   * @param bool $success
   *   TRUE if batch successfully completed.
   * @param array $results
   *   Batch results.
   * @param array $operations
   *   An array of function calls (not used in this function).
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to download the exported results.
   */
  static public function batchFinish($success, array $results, array $operations) {
    /** @var \Drupal\yamlform\YamlFormSubmissionExporterInterface $exporter */
    $exporter = \Drupal::service('yamlform_submission.exporter');

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $results['yamlform'];
    $export_options = $results['export_options'];

    if (!$success) {
      $file_path = $exporter->getFilePath($yamlform, $export_options);
      @unlink($file_path);
      drupal_set_message(t('Finished with an error.'));
    }
    else {
      $filename = $exporter->getFileName($yamlform, $export_options);
      $redirect_url = Url::fromRoute('entity.yamlform.results_export', ['yamlform' => $yamlform->id()], ['query' => ['filename' => $filename], 'absolute' => TRUE]);
      return new RedirectResponse($redirect_url->toString());
    }
  }

}
