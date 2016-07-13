<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormMessageManagerInterface.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface for managing a YAML form's custom, default, and hard-coded messages.
 */
interface YamlFormMessageManagerInterface {

  /****************************************************************************/
  // Hardcode message constants.
  /****************************************************************************/

  /**
   * Admin only access.
   */
  const ADMIN_ACCESS = 1;

  /**
   * Default submission confirmation.
   */
  const SUBMISSION_DEFAULT_CONFIRMATION = 2;

  /**
   * Submission previous.
   */
  const SUBMISSION_PREVIOUS = 3;

  /**
   * Submission updates.
   */
  const SUBMISSION_UPDATED = 4;

  /**
   * Submission test.
   */
  const SUBMISSION_TEST = 5;

  /**
   * YAML form not saving or sending any data.
   */
  const FORM_SAVE_EXCEPTION = 6;

  /**
   * YAML form not able to handle file uploads.
   */
  const FORM_FILE_UPLOAD_EXCEPTION = 7;

  /****************************************************************************/
  // Configurable message constants.
  // Values corresponds to admin config and YAML form settings.
  /****************************************************************************/

  /**
   * YAML form exception.
   */
  const FORM_EXCEPTION = 'form_exception_message';

  /**
   * YAML form preview.
   */
  const FORM_PREVIEW_MESSAGE = 'preview_message';

  /**
   * YAML form closed.
   */
  const FORM_CLOSED_MESSAGE = 'form_closed_message';

  /**
   * YAML form confidential.
   */
  const FORM_CONFIDENTIAL_MESSAGE = 'form_confidential_message';

  /**
   * Limit user submission.
   */
  const LIMIT_USER_MESSAGE = 'limit_user_message';

  /**
   * Limit total submission.
   */
  const LIMIT_TOTAL_MESSAGE = 'limit_total_message';

  /**
   * Submission draft saved.
   */
  const SUBMISSION_DRAFT_SAVED = 'draft_saved_message';

  /**
   * Submission draft loaded.
   */
  const SUBMISSION_DRAFT_LOADED = 'draft_loaded_message';

  /**
   * Submission confirmation.
   */
  const SUBMISSION_CONFIRMATION = 'confirmation_message';

  /**
   * Set the YAML form used for custom messages and token replacement.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL);

  /**
   * Set the YAML form source entity whose submissions are being exported.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   */
  public function setSourceEntity(EntityInterface $entity = NULL);

  /**
   * Set the YAML form submission used for token replacement.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function setYamlFormSubmission(YamlFormSubmissionInterface $yamlform_submission = NULL);

  /**
   * Get message.
   *
   * @param string $key
   *   The name of YAML form settings message to be displayed.
   *
   * @return string|bool
   *   A message or FALSE if no message is found.
   */
  public function get($key);

  /**
   * Display message.
   *
   * @param string $key
   *   The name of YAML form settings message to be displayed.
   * @param string $type
   *   (optional) The message's type. Defaults to 'status'. These values are
   *   supported:
   *   - 'status'
   *   - 'warning'
   *   - 'error'
   *
   * @return bool
   *   TRUE if message was displayed.
   */
  public function display($key, $type = 'status');

  /**
   * Build message.
   *
   * @return array
   *   A render array containing a message.
   */
  public function build($key);

  /**
   * Log message.
   *
   * @param string $key
   *   The name of YAML form settings message to be logged.
   * @param string $type
   *   (optional) The message's type. Defaults to 'warning'. These values are
   *   supported:
   *   - 'notice'
   *   - 'warning'
   *   - 'error'
   */
  public function log($key, $type = 'warning');

}
