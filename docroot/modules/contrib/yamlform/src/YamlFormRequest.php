<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormRequest.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Handles YAML form requests.
 */
class YamlFormRequest implements YamlFormRequestInterface {

  /**
   * The entity manager
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   */
  protected $entityManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a YamlFormSubmissionExporter object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(EntityManagerInterface $entity_manager, RouteMatchInterface $route_match) {
    $this->entityManager = $entity_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentSourceEntity($ignored_types = NULL) {
    $entity_types = $this->entityManager->getEntityTypeLabels();
    if ($ignored_types) {
      if (is_array($ignored_types)) {
        $entity_types = array_diff_key($entity_types, array_flip($ignored_types));
      }
      else {
        unset($entity_types[$ignored_types]);
      }
    }
    foreach ($entity_types as $entity_type => $entity_label) {
      $entity = $this->routeMatch->getParameter($entity_type);
      if ($entity instanceof EntityInterface) {
        return $entity;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentYamlForm() {
    $source_entity = self::getCurrentSourceEntity('yamlform');
    if ($source_entity && method_exists($source_entity, 'hasField') && $source_entity->hasField('yamlform')) {
      return $source_entity->yamlform->entity;
    }
    else {
      return $this->routeMatch->getParameter('yamlform');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormEntities() {
    $yamlform = $this->getCurrentYamlForm();
    $source_entity = $this->getCurrentSourceEntity('yamlform');
    return [$yamlform, $source_entity];
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormSubmissionEntities() {
    $yamlform_submission = $this->routeMatch->getParameter('yamlform_submission');
    $source_entity = $this->getCurrentSourceEntity('yamlform_submission');
    return [$yamlform_submission, $source_entity];
  }

}
