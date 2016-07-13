<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormTestController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Drupal\yamlform\YamlFormSubmissionGenerateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for YAML form testing.
 */
class YamlFormTestController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * YAML form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $yamlFormRequest;

  /**
   * YAML form submission generation service.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionGenerateInterface
   */
  protected $generate;

  /**
   * Constructs a new YamlFormTestController object.
   *
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The YAML form request handler.
   * @param \Drupal\yamlform\YamlFormSubmissionGenerateInterface $submission_generate
   *   The YAML form submission generation service.
   */
  public function __construct(YamlFormRequestInterface $yamlform_request, YamlFormSubmissionGenerateInterface $submission_generate) {
    $this->yamlFormRequest = $yamlform_request;
    $this->generate = $submission_generate;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform.request'),
      $container->get('yamlform_submission.generate')
    );
  }

  /**
   * Returns a form to add a new test submission to a YAML form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   The YAML form submission form.
   */
  public function testForm(Request $request) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\Core\Entity\EntityInterface $source_entity */
    list($yamlform, $source_entity) = $this->yamlFormRequest->getYamlFormEntities();
    $values = [];

    // Set source entity type and id.
    if ($source_entity) {
      $values['entity_type'] = $source_entity->getEntityTypeId();
      $values['entity_id'] = $source_entity->id();
    }

    if ($request->query->get('yamlform_id') == $yamlform->id()) {
      return $yamlform->getSubmissionForm($values);
    }

    // Generate date.
    $values['data'] = $this->generate->getData($yamlform);

    return $yamlform->getSubmissionForm($values);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   *
   * @return string
   *   The YAML form label as a render array.
   */
  public function title(YamlFormInterface $yamlform) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\Core\Entity\EntityInterface $source_entity */
    list($yamlform, $source_entity) = $this->yamlFormRequest->getYamlFormEntities();
    return $this->t('Testing %title form', ['%title' => ($source_entity) ? $source_entity->label() : $yamlform->label()]);
  }

}
