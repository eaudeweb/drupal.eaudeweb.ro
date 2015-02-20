<?php

function sk_drush_command() {
  return array(
    'sk' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
      'description' => 'Show how to use drush commands to manage the project'
    ),
    'sk-setup' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
      'description' => 'Validate configuration, download & install Drupal core - use to start a new project',
    ),
    'sk-install' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
      'description' => 'Install a clean project instance from scratch',
    ),
    'sk-update' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
      'description' => 'Update instance: database updates, revert features, clear caches',
    ),
    'sk-reset-variables' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
      'description' => 'Reset variables with those from the project configuration file',
      'options' => array(
        'verbose' => 'Show the value of each variable set'
      )
    ),
    'sk-devify' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
      'description' => 'Use when cloning production enviroment to local devel machine. Disable production configuration & enable development specific items',
    ),
    'sk-clean' => array(
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
      'description' => 'Deletes all files on disk',
    ),
  );
}

function drush_sk() {
  $commands = sk_drush_command();
  unset($commands['sk']);
  echo "Project commands:\n";
  foreach($commands as $k => $v) {
    echo " * $k: " . $v['description'] . "\n";
  }
}


function drush_sk_setup() {
  global $project_config;
  $local = $project_config['aliases']['local'];
  $makefile = !empty($local['makefile']) ? $local['makefile'] : '';
  if (!empty($makefile)) {
    $makefile = $makefile[0] == '/' ? $makefile : PROJECT_ROOT . '/' . $makefile;
    drush_invoke_process('@self', 'make', array($makefile, 'docroot'));
  }
  else {
    // Run drush make to make the Drupal instance
    drush_invoke_process('@self', 'dl', array('drupal', '--drupal-project-rename=docroot'));
  }
}

function drush_sk_install() {
  global $project_config;

  $makefile = isset($project_config['aliases']['local']['makefile']) ? $project_config['aliases']['local']['makefile'] : FALSE;
  if ($makefile) {
    $profile = 'standard';
    $src = PROJECT_ROOT . '/etc/profiles/' . $profile;
    $dest = PROJECT_ROOT . '/docroot/profiles/standard';
    if (is_dir($src)) {
      sk_rrmdir($dest);
      sk_rcopy(PROJECT_ROOT . '/etc/profiles/' . $profile, PROJECT_ROOT . '/docroot/profiles/standard');
    }
  }
  $local = $project_config['aliases']['local'];
  // Run site-install
  $db = $local['db'];
  $admin = $local['users']['admin'];
  $variables = $local['variables'];
  $db_url = sprintf('mysql://%s:%s@%s:%s/%s', $db['username'], $db['password'], $db['host'], $db['port'], $db['database']);
  $command_specific = array(
    'db-url' => $db_url,
    'account-mail' => $admin['mail'],
    'account-name' => $admin['name'],
    'account-pass' => $admin['pass'],
    'db-su' => $db['root_username'],
    'db-su-pw' => $db['root_password'],
    'site-mail' => $variables['site_mail']
  );
  drush_invoke_process('@self', 'site-install', array(), $command_specific);
}

function drush_sk_update() {
  drush_invoke('updatedb');
  if (module_exists('features')) {
    drush_invoke_process('@self', 'features-revert-all', array(), array(
      'force' => TRUE,
      'yes' => TRUE,
    ));
  }
  drush_invoke('cc', array('type' => 'all'));
  drush_log(dt('Update command finished'), 'success');
}

function drush_sk_devify() {
  global $project_config;

  $local = $project_config['aliases']['local'];
  $config = $local['devify'];

  $modules = array_unique(array_merge($config['disable-modules'], array('update')));
  module_disable($modules);

  $modules = array_unique(array_merge($config['enable-modules'], array()));
  module_disable($modules);

  $variables = array_unique(array_merge($config['delete-variables'], array('googleanalytics_account')));
  foreach ($variables as $variable) {
    variable_del($variable);
  }

  $variables = array_merge(
    $config['reset-variables'], array(
      'preprocess_css' => 0,
      'preprocess_js' => 0,
      'cache' => 0,
      'page_compression' => 0,
    )
  );
  foreach ($variables as $key => $value) {
    variable_set($key, $value);
  }

  // Devify Solr servers
  if (!empty($local['solr-servers']) && module_exists('search_api_solr')) {
    foreach ($local['solr-servers'] as $machine_name => $cfg) {
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
    }
  }
  drush_log(dt('Devified!'), 'success');
}

function drush_sk_clean() {
  $default = PROJECT_ROOT . '/docroot/sites/default';
  if (is_dir($default)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($default));
    foreach ($iterator as $item) {
      chmod($item, 0777);
    }
  }
  $docroot = PROJECT_ROOT . '/docroot';
  if (is_dir($docroot)) {
    sk_rrmdir($docroot);
  }
}

function drush_sk_reset_variables() {
  global $project_config;
  $variables = $project_config['aliases']['local']['variables'];
  foreach ($variables as $k => $v) {
    if (drush_get_option('verbose', FALSE)) {
      drush_log(sprintf('Setting %s = %s', $k, $v));
    }
    variable_set($k, $v);
  }
}