<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormHandlerManagerInterface.
 */

namespace Drupal\yamlform;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Collects available YAML form handlers.
 */
interface YamlFormHandlerManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface {

}
