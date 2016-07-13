<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionExporterInterface.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for exporting YAML form submission results.
 */
interface YamlFormSubmissionExporterInterface {

  /**
   * Set the YAML form whose submissions are being exported.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL);

  /**
   * Get the YAML form whose submissions are being exported.
   *
   * @return \Drupal\yamlform\YamlFormInterface
   *   A YAML form.
   */
  public function getYamlForm();

  /**
   * Set the YAML form source entity whose submissions are being exported.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A YAML form's source entity.
   */
  public function setSourceEntity(EntityInterface $entity = NULL);

  /**
   * Get the YAML form source entity whose submissions are being exported.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A YAML form's source entity.
   */
  public function getSourceEntity();

  /**
   * Get export options for the current YAML form and entity.
   *
   * @return array
   *   Export options.
   */
  public function getYamlFormOptions();

  /**
   * Set export options for the current YAML form and entity.
   *
   * @param array $options
   *   Export options.
   */
  public function setYamlFormOptions(array $options = []);

  /**
   * Delete export options for the current YAML form and entity.
   */
  public function deleteYamlFormOptions();

  /**
   * Get default options for exporting a CSV.
   *
   * @return array
   *   Default options for exporting a CSV.
   */
  public function getDefaultExportOptions();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface $form_state);

  /**
   * Get the values from the form's state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array of export options.
   */
  public function getFormValues(FormStateInterface $form_state);

  /**
   * Generate YAML form submission as a CSV and write it to a temp file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options generated via the
   *   Drupal\yamlform\Form\YamlFormResultsExportForm.
   */
  public function generate(YamlFormInterface $yamlform, array $export_options);

  /**
   * Write YAML form results header to CSV file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeHeader(YamlFormInterface $yamlform, array $field_definitions, array $elements, array $export_options);

  /**
   * Write YAML form results header to CSV file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface[] $yamlform_submissions
   *   A YAML form submission.
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeRecords(YamlFormInterface $yamlform, array $yamlform_submissions, array $field_definitions, array $elements, array $export_options);

  /**
   * Get YAML form submission field definitions.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An associative array containing YAML form submission field definitions.
   */
  public function getFieldDefinitions(array $export_options);

  /**
   * Get YAML form elements.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An associative array containing YAML form elements keyed by name.
   */
  public function getElements(YamlFormInterface $yamlform, array $export_options);

  /**
   * Get YAML form submission query for specified YAMl form and export options.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A YAML form submission entity query.
   */
  public function getQuery(YamlFormInterface $yamlform, array $export_options);

  /**
   * Total number of submissions to be exported.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return int
   *   The total number of submissions to be exported.
   */
  public function getTotal(YamlFormInterface $yamlform, array $export_options);

  /**
   * Get the number of submissions to be exported with each batch.
   *
   * @return int
   *   Number of submissions to be exported with each batch.
   */
  public function getBatchLimit();

  /**
   * Determine if YAML form submissions must be exported using batch processing.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return bool
   *   TRUE if YAML form submissions must be exported using batch processing.
   */
  public function requiresBatch(YamlFormInterface $yamlform);

  /**
   * Get CSV file name and path for a YAML form.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return string
   *   CSV file name and path for a YAML form
   */
  public function getFilePath(YamlFormInterface $yamlform, array $export_options);

  /**
   * Get CSV file temp directory path.
   *
   * @return string
   *   Temp directory path.
   */
  public function getFileTempDirectory();

  /**
   * Get CSV file name for a YAML form.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return string
   *   CSV or TSV file name for a YAML form depending on the demlimiter.
   */
  public function getFileName(YamlFormInterface $yamlform, array $export_options);

}
