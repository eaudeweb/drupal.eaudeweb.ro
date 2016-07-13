<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormSubmissionDeleteForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\yamlform\YamlFormRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for deleting a YAML form submission.
 */
class YamlFormSubmissionDeleteForm extends ContentEntityDeleteForm {

  /**
   * The YAML form entity.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;


  /**
   * The YAML form submission entity.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionInterface
   */
  protected $yamlformSubmission;

  /**
   * The YAML form source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * YAML form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $yamlFormRequest;

  /**
   * Constructs a new YamlFormSubmissionDeleteForm object.
   *
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The YAML form request handler.
   */
  public function __construct(YamlFormRequestInterface $yamlform_request) {
    $this->yamlFormRequest = $yamlform_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform.request')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    list($this->yamlformSubmission, $this->sourceEntity) = $this->yamlFormRequest->getYamlFormSubmissionEntities();
    $this->yamlform = $this->yamlformSubmission->getYamlForm();
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t(
      'Are you sure you want to delete @title: Submission #@id?', ['@title' => $this->getTitle(), '@id' => $this->getEntity()->id()]
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this->t(
      '@title: Submission #@id has been deleted.', ['@title' => $this->getTitle(), '@id' => $this->getEntity()->id()]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    if ($this->sourceEntity) {
      $entity_type = $this->sourceEntity->getEntityTypeId();
      $entity_id = $this->sourceEntity->id();
      $route_name = "entity.$entity_type.yamlform.results_submissions";
      $router_parameters = [$entity_type => $entity_id];
    }
    else {
      $route_name = 'entity.yamlform.results_submissions';
      $router_parameters = ['yamlform' => $this->yamlform->id()];
    }
    return new Url($route_name, $router_parameters);
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    return $this->getCancelUrl();
  }

  /**
   * Get title for question and message.
   *
   * @return null|string
   *   Title for question and message.
   */
  protected function getTitle() {
    return ($this->sourceEntity) ? $this->sourceEntity->label() : $this->yamlform->label();
  }

}
