<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormUiElementAddForm.
 */

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides an add form for a YAML form element.
 */
class YamlFormUiElementAddForm extends YamlFormUiElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $type = NULL) {
    $this->yamlform = $yamlform;
    $parent_key = $this->getRequest()->get('parent');

    if ($parent_key) {
      $parent_element = $yamlform->getElementDecoded($parent_key);
      if (!$parent_element) {
        throw new NotFoundHttpException();
      }
    }

    $this->element['#type'] = $type;
    $this->action = $this->t('created');
    return parent::buildForm($form, $form_state, $yamlform, NULL, $parent_key);
  }

}
