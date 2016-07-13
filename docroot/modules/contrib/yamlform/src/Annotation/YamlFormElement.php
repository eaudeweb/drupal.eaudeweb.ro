<?php

/**
 * @file
 * Contains \Drupal\yamlform\Annotation\YamlFormElement.
 */

namespace Drupal\yamlform\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a YAML form element annotation object.
 *
 * Plugin Namespace: Plugin\YamlFormElement.
 *
 * For a working example, see
 * \Drupal\yamlform\Plugin\YamlFormElement\Email
 *
 * @see hook_yamlform_element_info_alter()
 * @see \Drupal\yamlform\YamlFormElementInterface
 * @see \Drupal\yamlform\YamlFormElementBase
 * @see \Drupal\yamlform\YamlFormElementManager
 * @see \Drupal\yamlform\YamlFormElementManagerInterface
 * @see plugin_api
 *
 * @Annotation
 */
class YamlFormElement extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * URL to the element's API documentation.
   *
   * @var string
   */
  public $api;

  /**
   * The human-readable name of the YAML form element.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The category in the admin UI where the YAML form will be listed.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category = '';

  /**
   * Flag that defines hidden element.
   *
   * @var boolean
   */
  public $hidden = FALSE;

  /**
   * Flag that defines multiline element.
   *
   * @var boolean
   */
  public $multiline = FALSE;

  /**
   * Flag that defines multiple (value) element.
   *
   * @var boolean
   */
  public $multiple = FALSE;

  /**
   * Flag that defines composite element.
   *
   * @var boolean
   */
  public $composite = FALSE;

}
