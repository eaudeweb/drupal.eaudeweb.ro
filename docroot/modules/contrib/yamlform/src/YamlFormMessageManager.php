<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormMessageManager.
 */

namespace Drupal\yamlform;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;

/**
 * Defines the YAML form message (and loggin) manager.
 */
class YamlFormMessageManager implements YamlFormMessageManagerInterface {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * A YAML form.
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
   * A YAML form submission.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionInterface
   */
  protected $yamlformSubmission;

  /**
   * Constructs a YamlFormMessageManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Token $token, LoggerInterface $logger) {
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL) {
    $this->yamlform = $yamlform;
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
  public function setYamlFormSubmission(YamlFormSubmissionInterface $yamlform_submission = NULL) {
    $this->yamlformSubmission = $yamlform_submission;
    if ($yamlform_submission && empty($this->yamlform)) {
      $this->yamlform = $yamlform_submission->getYamlForm();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function display($key, $type = 'status') {
    if ($build = $this->build($key)) {
      drupal_set_message(\Drupal::service('renderer')->renderPlain($build), $type);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build($key) {
    if ($message = $this->get($key)) {
      return [
        '#markup' => $message,
        '#allowed_tags' => Xss::getAdminTagList(),
      ];
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    $yamlform_settings = ($this->yamlform) ? $this->yamlform->getSettings() : [];
    if (!empty($yamlform_settings[$key])) {
      return $this->replaceTokens($yamlform_settings[$key]);
    }

    $default_settings = $this->configFactory->get('yamlform.settings')->get('settings');
    if (!empty($default_settings['default_' . $key])) {
      return $this->replaceTokens($default_settings['default_' . $key]);
    }

    $yamlform = $this->yamlform;
    $source_entity = $this->sourceEntity;
    if ($source_entity) {
      $entity_type = $source_entity->getEntityTypeId();
      $entity_id = $source_entity->id();
      $submission_route_name = "entity.$entity_type.yamlform.submissions";
      $submission_route_parameters = [$entity_type => $entity_id];
    }
    else {
      $submission_route_name = 'entity.yamlform.submissions';
      $submission_route_parameters = ['yamlform' => $yamlform->id()];
    }
    $t_args = [
      '%form' => ($source_entity) ? $source_entity->label() : $yamlform->label(),
      ':handlers_href' => $yamlform->toUrl('handlers-form')->toString(),
      ':settings_href' => $yamlform->toUrl('settings-form')->toString(),
      ':submissions_href' => Url::fromRoute($submission_route_name, $submission_route_parameters)->toString(),
    ];

    switch ($key) {
      case YamlFormMessageManagerInterface::ADMIN_ACCESS:
        return $this->t('This form is <a href=":setting_href">closed</a>. Only submission administrators are allowed to access this form and create new submissions.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_DEFAULT_CONFIRMATION:
        return $this->t('New submission added to %form.', $t_args);

      case YamlFormMessageManagerInterface::FORM_SAVE_EXCEPTION:
        return $this->t('This form is currently not saving any submitted data. Please enable the <a href=":settings_href">saving of results</a> or add a <a href=":handlers_href">submission handler</a> to the form.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_PREVIOUS:
        return $this->t('You have already submitted this form. <a href=":submissions_href">View your previous submissions</a>.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_UPDATED:
        return $this->t('Submission updated in %form.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_TEST;
        return $this->t("The below form has been prepopulated with custom/random test data. When submitted, this information <b>will still be saved</b> and/or <b>sent to designated recipients</b>.", $t_args);

      default:
        return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($key, $type = 'warning') {
    $yamlform = $this->yamlform;
    $context = [
      'link' => $yamlform->toLink($this->t('Edit'), 'edit-form')->toString(),
    ];

    switch ($key) {
      case YamlFormMessageManagerInterface::FORM_FILE_UPLOAD_EXCEPTION:
        $message = 'To support file uploads the saving of submission must be enabled. <b>All uploaded load files would be lost</b> Please either uncheck \'Disable saving of submissions\' or remove all the file upload elements.';
        break;

      case YamlFormMessageManagerInterface::FORM_SAVE_EXCEPTION:
        $context['%form'] = $yamlform->label();
        $message = '%form is not saving any submitted data and has been disabled.';
        break;
    }

    $this->logger->$type($message, $context);
  }


  /**
   * Replace tokens in text.
   *
   * @param string $text
   *   A string of text that main contain tokens.
   *
   * @return string
   *   Text will tokens replaced.
   */
  protected function replaceTokens($text) {
    // Most strings won't contain tokens so lets check and return ASAP.
    if (!is_string($text) || strpos($text, '[') === FALSE) {
      return $text;
    }

    $token_data = [
      'yamlform' => $this->yamlform,
      'yamlform_submission' => $this->yamlformSubmission,
    ];
    $token_options = ['clear' => TRUE];

    return $this->token->replace($text, $token_data, $token_options);
  }

}
