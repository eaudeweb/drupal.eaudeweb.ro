<?php
/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\corps\Form\CorpsCustomInstallForm;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function corps_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
}

/**
 * Implements hook_install_tasks().
 */
function corps_install_tasks(&$install_state) {
  $tasks = array();
  $tasks['corps_custom_install_form'] = array(
    'display_name' => t('Configure installation'),
    'type' => 'form',
    'function' => 'Drupal\corps\Form\CorpsCustomInstallForm',
  );
  $tasks['corps_install_profile_modules'] = array(
    'display_name' => t('Install custom modules'),
    'type' => 'batch',
  );
  return $tasks;
}

/**
 * Install custom modules.
 * @see install_profile_modules()
 */
function corps_install_profile_modules() {
  $modules = \Drupal::state()->get('corps_profile_modules');
  if (empty($modules)) {
    $modules = array();
  }
  $operations = array();
  \Drupal::state()->delete('corps_profile_modules');
  foreach ($modules as $module) {
    $operations[] = array('_corps_install_module_batch', array($module));
  }
  $batch = array(
    'operations' => $operations,
    'title' => t('Installing @drupal', array('@drupal' => drupal_install_profile_distribution_name())),
    'error_message' => t('The installation has encountered an error.'),
    'finished' => '_corps_install_modules_finished',
  );
  return $batch;

}

/**
 * Batch operation for enabling custom modules.
 */
function _corps_install_module_batch($module_name, &$context) {
  \Drupal::service('module_installer')->install(array($module_name), TRUE);
  $context['results'][] = $module_name;
  $context['message'] = t('Installed %module module.', array('%module' => $module_name));
}

/**
 * Install Optional Configs after the custom modules are installed.
 */
function _corps_install_modules_finished() {
  \Drupal::service('config.installer')->installOptionalConfig();
}
