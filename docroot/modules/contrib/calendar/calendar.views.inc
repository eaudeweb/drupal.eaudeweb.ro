<?php

/**
 * @file
 * Provides views data for the calendar module.
 */

/**
 * Implements hook_views_data_alter().
 */
function calendar_views_data_alter(array &$data) {
  foreach ($data as $table_name => $table_data) {
    foreach ($table_data as $property_name => $properties) {
      if (isset($properties['argument']) && $properties['argument']['id'] === 'date') {
        $data[$table_name] += _calendar_create_field_arguments($property_name);
      }
    }
  }
  $data['views']['calendar_header'] = array(
    'title' => t('Calendar Header'),
    'help' => t('Calendar heading or pager.'),
    'area' => array(
      'id' => 'calendar_header',
    ),
  );

}

/**
 * Create all the granularity arguments for a a field.
 *
 * @param $field_id
 * @param string $group
 *
 * @return mixed
 */
function _calendar_create_field_arguments($field_id, $group = '') {
  $granularity_args = [];
  // All other granularity types are added by core.
  $granularity_types = [
    //'year' => t('Date in the form of YYYY.'),
    //'month' => t('Date in the form of MM (01 - 12).'),
    //'year_month' => t('Date in the form of YYYYMM.'),
    //'full_date' => t('Date in the form of CCYYMMDD.'),
    'year_week' => t('Date in the form of YYYYW'),
  ];
  foreach ($granularity_types as $granularity_type => $granularity_label) {

    $granularity_arg = [
      'title' => t('Calendar @name @granularity', ['@name' => $field_id, '@granularity' => $granularity_type]),
      'help' => t('A calendar contextual filter that handles @label.', ['@label' => $granularity_label]),
      'argument' => [
        'field' => $field_id,
        'id' => 'date_' . $granularity_type,
      ],
    ];
    if ($group) {
      $granularity_arg['group'] = $group;
    }
    $granularity_args[$field_id . '_' . $granularity_type] = $granularity_arg;
  }
  return $granularity_args;
}
