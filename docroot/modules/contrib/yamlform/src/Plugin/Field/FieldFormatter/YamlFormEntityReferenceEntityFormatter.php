<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\Field\FieldFormatter\YamlFormEntityReferenceEntityFormatter.
 */

namespace Drupal\yamlform\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\yamlform\YamlFormMessageManagerInterface;

/**
 * Plugin implementation of the 'YAML form rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "yamlform_entity_reference_entity_view",
 *   label = @Translation("YAML form"),
 *   description = @Translation("Display the referenced YAML form with default submission data."),
 *   field_types = {
 *     "yamlform"
 *   }
 * )
 */
class YamlFormEntityReferenceEntityFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\yamlform\YamlFormInterface $entity */
      if ($entity->id() && $items[$delta]->status) {
        $values = ['data' => $items[$delta]->default_data];
        $elements[$delta] = $entity->getSubmissionForm($values);
      }
      else {
        /** @var \Drupal\yamlform\YamlFormMessageManagerInterface $message_manager */
        $message_manager = \Drupal::service('yamlform.message_manager');
        $message_manager->setYamlForm($entity);
        $elements[$delta] = $message_manager->build(YamlFormMessageManagerInterface::FORM_CLOSED_MESSAGE);
      }
    }

    return $elements;
  }

}
