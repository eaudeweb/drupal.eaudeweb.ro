<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Element.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'generic' element. Used as a fallback.
 *
 * @YamlFormElement(
 *   id = "yamlform_element",
 *   label = @Translation("Generic element"),
 *   hidden = TRUE
 * )
 */
class YamlFormElement extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['custom']['#title'] = $this->t('Element settings');
    $form['custom']['custom']['#title'] = $this->t('Properties');

    // Add link to theme API documentation.
    $theme = (isset($this->configuration['#theme'])) ? $this->configuration['#theme'] : '';
    if (function_exists('template_preprocess_' . $theme)) {
      $t_args = [
        '@href' => \Drupal\Core\Url::fromUri('https://api.drupal.org/api/drupal/core!includes!theme.inc/function/template_preprocess_' . $theme)->toString(),
        '%label' => $theme,
      ];
      $form['custom']['#description'] = $this->t('Read the the %label template\'s <a href="@href">API documentation</a>.', $t_args);
    }
    return $form;
  }

}
