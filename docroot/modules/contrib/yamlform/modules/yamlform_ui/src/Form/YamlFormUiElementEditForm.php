<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormUiElementEditForm.
 */

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides an edit form for a YAML form element.
 */
class YamlFormUiElementEditForm extends YamlFormUiElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $key = NULL) {
    $this->element = $yamlform->getElementDecoded($key);
    if (!$this->element) {
      throw new NotFoundHttpException();
    }

    $form['#title'] = $this->t('Edit @title element', [
      '@title' => (!empty($this->element['#title'])) ? $this->element['#title'] : $key,
    ]);

    $this->action = $this->t('updated');
    return parent::buildForm($form, $form_state, $yamlform, $key);
  }

}
