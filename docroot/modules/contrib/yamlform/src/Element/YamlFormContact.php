<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormContact.
 */

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for a contact element.
 *
 * @FormElement("yamlform_contact")
 */
class YamlFormContact extends YamlFormAddress {

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements() {
    $elements = [];
    $elements['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
    ];
    $elements['company'] = [
      '#type' => 'textfield',
      '#title' => t('Company'),
    ];
    $elements += parent::getCompositeElements();
    $elements['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
    ];
    $elements['phone'] = [
      '#type' => 'tel',
      '#title' => t('Phone'),
    ];
    return $elements;
  }

}
