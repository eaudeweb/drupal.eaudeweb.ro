<?php

/**
 * @file
 * Contains \Drupal\yamlform\Utility\YamlFormElementHelper.
 */

namespace Drupal\yamlform\Utility;

use Drupal\Core\Render\Element;

/**
 * Helper class YAML form element methods.
 */
class YamlFormElementHelper {

  /**
   * Get an associative array containing a render element's properties.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   An associative array containing a render element's properties.
   */
  public static function getProperties(array $element) {
    $properties = [];
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        $properties[$key] = $value;
      }
    }
    return $properties;
  }

  /**
   * Remove all properties from a render element.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   A render element with no properties.
   */
  public static function removeProperties(array $element) {
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        unset($element[$key]);
      }
    }
    return $element;
  }

  /**
   * Add prefix to all top level keys in an associative array.
   *
   * @param array $array
   *   An associative array.
   * @param string $prefix
   *   Prefix to be prepended to all keys.
   *
   * @return array
   *   An associative array with all top level keys prefixed.
   */
  public static function addPrefix(array $array, $prefix = '#') {
    $prefixed_array = [];
    foreach ($array as $key => $value) {
      if ($key[0] != $prefix) {
        $key = $prefix . $key;
      }
      $prefixed_array[$key] = $value;
    }
    return $prefixed_array;
  }

  /**
   * Remove prefix from all top level keys in an associative array.
   *
   * @param array $array
   *   An associative array.
   * @param string $prefix
   *   Prefix to be remove from to all keys.
   *
   * @return array
   *   An associative array with prefix removed from all top level keys.
   */
  public static function removePrefix(array $array, $prefix = '#') {
    $unprefixed_array = [];
    foreach ($array as $key => $value) {
      if ($key[0] == $prefix) {
        $key = preg_replace('/^' . $prefix . '/', '', $key);
      }
      $unprefixed_array[$key] = $value;
    }
    return $unprefixed_array;
  }

}
