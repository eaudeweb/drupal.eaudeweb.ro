<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\YamlFormEmailConfirm.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'email_confirm' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_email_confirm",
 *   label = @Translation("Email confirm"),
 *   category = @Translation("Advanced")
 * )
 */
class YamlFormEmailConfirm extends Email {}
