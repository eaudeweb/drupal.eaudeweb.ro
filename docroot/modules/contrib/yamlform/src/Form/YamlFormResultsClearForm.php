<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormResultsClearForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Url;

/**
 * Form for YAML form results clear form.
 */
class YamlFormResultsClearForm extends YamlFormSubmissionsDeleteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_results_clear';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->sourceEntity) {
      $t_args = ['%title' => $this->sourceEntity->label()];
    }
    else {
      $t_args = ['%title' => $this->yamlform->label()];
    }
    return $this->t('Are you sure you want to delete all submissions to %title form?', $t_args);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    if ($this->sourceEntity) {
      $entity_type = $this->sourceEntity->getEntityTypeId();
      $entity_id = $this->sourceEntity->id();
      $route_name = "entity.$entity_type.yamlform.results_submissions";
      $router_parameters = [$entity_type => $entity_id];
    }
    else {
      $route_name = 'entity.yamlform.results_submissions';
      $router_parameters = ['yamlform' => $this->yamlform->id()];
    }
    return new Url($route_name, $router_parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    if ($this->sourceEntity) {
      $t_args = ['%title' => $this->sourceEntity->label()];
    }
    else {
      $t_args = ['%title' => $this->yamlform->label()];
    }
    $this->t('Form %title submissions cleared.', $t_args);
  }

}
