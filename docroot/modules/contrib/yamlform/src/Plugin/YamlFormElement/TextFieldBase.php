<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\TextField.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a base 'textfield' class.
 */
abstract class TextFieldBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'size' => '',
      'maxlength' => '',
      'placeholder' => '',
      'input_mask' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Convert custom #autocomplete property to a FAPI autocomplete route
    // that return YAML form options.
    if (isset($element['#autocomplete'])) {
      $element['#autocomplete_route_name'] = 'yamlform.options_autocomplete';
      $element['#autocomplete_route_parameters'] = [
        'yamlform' => $yamlform_submission->getYamlForm()->id(),
        'key' => $element['#yamlform_key'],
      ];
    }

    // Input mask support.
    if (isset($element['#input_mask'])) {
      // See if the element mask is JSON by looking for 'name':, else assume it
      // is a mask pattern.
      $input_mask = $element['#input_mask'];
      if (preg_match("/^'[^']+'\s*:/", $input_mask)) {
        $element['#attributes']['data-inputmask'] = $input_mask;
      }
      else {
        $element['#attributes']['data-inputmask-mask'] = $input_mask;
      }

      $element['#attributes']['class'][] = 'js-yamlform-element-mask';
      $element['#attached']['library'][] = 'yamlform/jquery.inputmask';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['form']['input_mask'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('Inputs masks'),
      '#description' => $this->t('An <a href=":href">inputmask</a> helps the user with the element by ensuring a predefined format.', [':href' => 'https://github.com/RobinHerbots/jquery.inputmask']),
      '#other_option_label' => $this->t('Custom...'),
      '#other_placeholder' => $this->t('Enter input mask...'),
      '#other_description' => $this->t('(9 = numeric; a = alphabetical; * = alphanumeric)'),
      '#options' => [
        '' => '',
        'Basic' => [
          "'alias': 'currency'" => $this->t('Currency - @format', ['@format' => '$ 9.99']),
          "'alias': 'mm/dd/yyyy'" => $this->t('Date - @format', ['@format' => 'mm/dd/yyyy']),
          "'alias': 'email'" => $this->t('Email - @format', ['@format' => 'example@example.com']),
          "'alias': 'percentage'" => $this->t('Percentage - @format', ['@format' => '99%']),
          '(999) 999-9999' => $this->t('Phone - @format', ['@format' => '(999) 999-9999']),
          '99999[-9999]' => $this->t('Zip code - @format', ['@format' => '99999[-9999]']),
        ],
        'Advanced' => [
          "'alias': 'ip'" => 'IP address - 255.255.255.255',
          '[9-]AAA-999' => 'License plate - [9-]AAA-999',
          "'alias': 'mac'" => 'MAC addresses - 99-99-99-99-99-99',
          '999-99-9999' => 'SSN - 999-99-9999',
          "'alias': 'vin'" => 'VIN (Vehicle identification number)',
        ],
      ],
    ];

    return $form;
  }

}
