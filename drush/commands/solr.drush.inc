<?php

/**
 * @file
 * Devify Solr command for Drush
 */

/**
 * Implements hook_drush_command().
 */
function solr_drush_command() {
  $items = array();
  $items['solr-config-reset'] = array(
    'description' => 'Reset Solr servers configuration according to configuration file',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array(
      'servers' => dt('Optional machine name to reset configuration for a single server'),
    )
  );
  return $items;
}

function drush_solr_config_reset() {
  $cfg = sk_get_config();
  if(!empty($cfg['solr-servers']) && module_exists('search_api') && module_exists('search_api_solr')) {
    $filter = sk_parse_drush_get_option_array('servers');
    require_once PROJECT_ROOT . '/etc/profiles/standard/standard.lib.search.inc';
    foreach($cfg['solr-servers'] as $key => $server) {
      if (empty($filter) || in_array($key, $filter)) {
        if (drush_get_option('verbose', FALSE)) {
          drush_log(dt('Resetting Solr server !server to its local defaults', array('!server' => $key)));
        }
        standard_search_add_search_api_solr($key, $server);
      }
    }
  }
  else {
    drush_log('No valid Solr setup found. Check project configuration Yaml files and enabled modules.', 'error');
  }
}
