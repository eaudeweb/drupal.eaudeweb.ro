<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormEmailConfirm.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element requiring users to double-element and confirm an email address.
 *
 * Formats as a pair of email addresses fields, which do not validate unless
 * the two entered email addresses match.
 *
 * Below code is copied from: \Drupal\Core\Render\Element\PasswordConfirm.
 *
 * @FormElement("yamlform_email_confirm")
 */
class YamlFormEmailConfirm extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#process' => [
        [$class, 'processEmailConfirm'],
      ],
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      if (!isset($element['#default_value'])) {
        $element['#default_value'] = '';
      }
      $element['mail2'] = $element['mail1'] = $element['#default_value'];
      return $element;
    }
    return NULL;
  }

  /**
   * Expand an email confirm field into two HTML5 email elements.
   */
  public static function processEmailConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    if (empty($element['#title'])) {
      $element['#title'] = t('Email');
    }
    if (empty($element['#confirm_title'])) {
      $element['#confirm_title'] = t('Confirm @title', ['@title' => $element['#title']]);
    }

    $element['mail1'] = [
      '#type' => 'email',
      '#title' => $element['#title'],
      '#value' => empty($element['#value']) ? NULL : $element['#value']['mail1'],
      '#required' => $element['#required'],
      '#attributes' => ['class' => ['email-field']],
    ];
    $element['mail2'] = [
      '#type' => 'email',
      '#title' => $element['#confirm_title'],
      '#value' => empty($element['#value']) ? NULL : $element['#value']['mail2'],
      '#required' => $element['#required'],
      '#attributes' => ['class' => ['email-confirm']],
    ];
    $element['#element_validate'] = [[get_called_class(), 'validateEmailConfirm']];

    // Set element size.
    if (isset($element['#size'])) {
      $element['mail1']['#size'] = $element['mail2']['#size'] = $element['#size'];
    }

    // Unset element title to prevent duplicate titles.
    unset($element['#title']);

    return $element;
  }

  /**
   * Validates an email confirm element.
   */
  public static function validateEmailConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $mail1 = trim($element['mail1']['#value']);
    $mail2 = trim($element['mail2']['#value']);
    if (!empty($mail1) || !empty($mail2)) {
      if (strcmp($mail1, $mail2)) {
        $form_state->setError($element['mail2'], t('The specified email addresses do not match.'));
      }
    }
    elseif ($element['#required']) {
      if (empty($mail1)) {
        $form_state->setError($element['mail1'], t('@name field is required.', ['@name' => $element['mail1']['#title']]));
      }
      if (empty($mail2)) {
        $form_state->setError($element['mail2'], t('@name field is required.', ['@name' => $element['mail2']['#title']]));
      }
    }

    // Email field must be converted from a two-element array into a single
    // string regardless of validation results.
    $form_state->setValueForElement($element['mail1'], NULL);
    $form_state->setValueForElement($element['mail2'], NULL);
    $form_state->setValueForElement($element, $mail1);

    return $element;
  }

}
