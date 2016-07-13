<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\EntityAutocomplete.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'entity_autocomplete' element.
 *
 * @YamlFormElement(
 *   id = "entity_autocomplete",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Entity!Element!EntityAutocomplete.php/class/EntityAutocomplete",
 *   label = @Translation("Entity autocomplete"),
 *   category = @Translation("Entity references")
 * )
 */
class EntityAutocomplete extends EntityReferenceBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'tags' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function hasMultipleValues(array $element) {
    return (!empty($element['#tags'])) ? TRUE : parent::hasMultipleValues($element);
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    // If #tags (aka multiple entities) use #after_builder to set #element_value
    // which must be executed after
    // \Drupal\Core\Entity\Element\EntityAutocomplete::validateEntityAutocomplete().
    if ($this->hasMultipleValues($element)) {
      $element['#after_build'][] = [get_class($this), 'afterBuildEntityAutocomplete'];
    }
  }

  /**
   * Form API callback. After build set the #element_validate handler.
   */
  public static function afterBuildEntityAutocomplete(array $element, FormStateInterface $form_state) {
    $element['#element_validate'][] = ['\Drupal\yamlform\Plugin\YamlFormElement\EntityAutocomplete', 'validateEntityAutocomplete'];
    return $element;
  }

  /**
   * Form API callback. Remove target id property and create an array of entity ids.
   */
  public static function validateEntityAutocomplete(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $value = $form_state->getValue($name);
    if (is_array($value) && !empty($value)) {
      $entity_ids = [];
      foreach ($value as $item) {
        $entity_ids[] = $item['target_id'];
      }
      $form_state->setValueForElement($element, $entity_ids);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['entity_reference']['tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tags'),
      '#description' => $this->t('Check this option if the user should be allowed to enter multiple entity references.'),
      '#return_value' => TRUE,
    ];
    return $form;
  }

}
