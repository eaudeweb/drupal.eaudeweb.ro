<?php

function standard_setup_search() {
  $cfg = standard_get_config();
  if(!empty($cfg['solr-servers']) && module_exists('search_api') && module_exists('search_api_solr')) {
    foreach($cfg['solr-servers'] as $machine_name => $server) {
      standard_search_add_search_api_solr($machine_name, $server);
    }
  }
}

function standard_search_add_search_api_solr($machine_name, $cfg) {
  if (empty($machine_name)) {
    drupal_set_message('Skipping Search API Solr configuration without machine name', 'error');
    return;
  }
  $cfg = array_merge(
    array(
      'name' => 'Solr server',
      'enabled' => 1,
      'description' => 'Search server',
      'scheme' => 'http',
      'host' => 'localhost',
      'port' => '8983',
      'path' => '/solr',
      'http_user' => '',
      'http_pass' => '',
      'excerpt' => NULL,
      'retrieve_data' => NULL,
      'highlight_data' => NULL,
      'skip_schema_check' => NULL,
      'solr_version' => '',
      'http_method' => 'AUTO',
    ), $cfg);
  if ($server = search_api_server_load($machine_name)) {
    $server->name = $cfg['name'];
    $server->enabled = $cfg['enabled'];
    $server->description = $cfg['description'];
    $server->options = array(
      'scheme' => $cfg['scheme'],
      'host' => $cfg['host'],
      'port' => $cfg['port'],
      'path' => $cfg['path'],
      'http' => array(
        'http_user' => $cfg['http_user'],
        'http_pass' => $cfg['http_pass'],
      ),
      'excerpt' => $cfg['excerpt'],
      'retrieve_data' => $cfg['retrieve_data'],
      'highlight_data' => $cfg['highlight_data'],
      'skip_schema_check' => $cfg['skip_schema_check'],
      'solr_version' => $cfg['solr_version'],
      'http_method' => $cfg['http_method'],
    );
    $server->save();
  }
  else {
    module_load_include('inc', 'search_api', 'search_api.admin');
    $form_state = array(
      'values' => array(
        'machine_name' => $machine_name,
        'class' => 'search_api_solr_service',
        'name' => $cfg['name'],
        'enabled' => $cfg['enabled'],
        'description' => $cfg['description'],
        'options' => array(
          'form' => array(
            'scheme' => $cfg['scheme'],
            'host' => $cfg['host'],
            'port' => $cfg['port'],
            'path' => $cfg['path'],
            'http' => array(
              'http_user' => $cfg['http_user'],
              'http_pass' => $cfg['http_pass'],
            ),
            'advanced' => array(
              'excerpt' => $cfg['excerpt'],
              'retrieve_data' => $cfg['retrieve_data'],
              'highlight_data' => $cfg['highlight_data'],
              'skip_schema_check' => $cfg['skip_schema_check'],
              'solr_version' => $cfg['solr_version'],
              'http_method' => $cfg['http_method'],
            ),
          ),
        ),
      ),
    );
    drupal_form_submit('search_api_admin_add_server', $form_state);
  }
}
