<?php
// Rename this file to {your-project}.aliases.drushrc.php

$aliases['dev'] = array(
  'root' => '/disk/path/to/docroot',
  'uri' => 'http://dev.project.org/',
  'remote-host' => 'dev.project.org',
  'remote-user' => 'php',
);
$aliases['test'] = array(
  'root' => '/disk/path/to/docroot',
  'uri' => 'http://test.project.org/',
  'remote-host' => 'test.project.org',
  'remote-user' => 'php',
);
$aliases['prod'] = array(
  'root' => '/disk/path/to/docroot',
  'uri' => 'http://www.project.org/',
  'remote-host' => 'www.project.org',
  'remote-user' => 'php',
);

// Add your local aliases.
if (file_exists(dirname(__FILE__) . '/aliases.local.php')) {
  include dirname(__FILE__) . '/aliases.local.php';
}
