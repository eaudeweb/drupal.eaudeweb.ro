<?php

/**
 * @file
 * Provides views data for the datetime module.
 */

use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_field_views_data_alter().
 *
 * This hook is only need to bring in our extra arguments that will be added to core.
 * @see https://www.drupal.org/node/2567815
 *      When this resolved this hook can be removed.
 *      Currently this is exact copy of datetime_field_views_data after patch applied.
 */
function calendar_datetime_field_views_data_alter(array &$data, FieldStorageConfigInterface $field_storage) {
  // @todo This code only covers configurable fields, handle base table fields
  //   in https://www.drupal.org/node/2489476.
  //$data = views_field_default_views_data($field_storage);
  foreach ($data as $table_name => $table_data) {
    // Set the 'datetime' filter type.
    if (isset($data[$table_name][$field_storage->getName() . '_value'])
      && $data[$table_name][$field_storage->getName() . '_value']['filter']['id'] == 'datetime') {
      // Create year, month, and day arguments.
      $group = $data[$table_name][$field_storage->getName() . '_value']['group'];
      $arguments = [
        // Argument type => help text.
        'year' => t('Calendar Date in the form of YYYY.'),
        'year_month' => t('Calendar Date in the form of YYYYMM.'),
        'full_date' => t('Calendar Date in the form of CCYYMMDD.'),
        'year_week' => t('Calendar Date in the form of CCYYW.'),
      ];
      foreach ($arguments as $argument_type => $help_text) {
        $data[$table_name][$field_storage->getName() . '_value_' . $argument_type] = [
          'title' => 'Calendar ' . $field_storage->getLabel() . ' (' . $argument_type . ')',
          'help' => $help_text,
          'argument' => [
            'field' => $field_storage->getName() . '_value',
            'id' => 'datetime_' . $argument_type,
          ],
          'group' => $group,
        ];
      }
    }
  }
}

