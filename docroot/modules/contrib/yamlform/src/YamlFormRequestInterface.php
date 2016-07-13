<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormRequestInterface.
 */

namespace Drupal\yamlform;

/**
 * Helper class YAML form entity methods.
 */
/**
 * Provides an interface defining a YAML form request handler.
 */
interface YamlFormRequestInterface {

  /**
   * Get the current request's entity.
   *
   * @param string|array $ignored_types
   *   (optional) Array of ignore entity types.
   *
   * @return NULL|\Drupal\Core\Entity\EntityInterface
   */
  public function getCurrentSourceEntity($ignored_types = NULL);

  /**
   * Get YAML form associated with the current request.
   *
   * @return NULL|\Drupal\yamlform\YamlFormInterface
   */
  public function getCurrentYamlForm();

  /**
   * Get the YAML form and source entity for the current request.
   *
   * @return array
   *   An array containing the YAML form and source entity for the current request.
   */
  public function getYamlFormEntities();

  /**
   * Get the YAML form submission and source entity for the current request.
   *
   * @return array
   *   An array containing the YAML form and source entity for the current request.
   */
  public function getYamlFormSubmissionEntities();

}
