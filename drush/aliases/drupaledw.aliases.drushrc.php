<?php

// Site drupaledw, environment dev
$aliases['dev'] = array(
  'root' => '/var/www/html/drupaledw.dev/docroot',
  'ac-site' => 'drupaledw',
  'ac-env' => 'dev',
  'ac-realm' => 'devcloud',
  'uri' => 'drupaledwmxzrfksjvy.devcloud.acquia-sites.com',
  'remote-host' => 'srv-5188.devcloud.hosting.acquia.com',
  'remote-user' => 'drupaledw.dev',
);
$aliases['dev.livedev'] = array(
  'parent' => '@drupaledw.dev',
  'root' => '/mnt/gfs/drupaledw.dev/livedev/docroot',
);

// Site drupaledw, environment prod
$aliases['prod'] = array(
  'root' => '/var/www/html/drupaledw.prod/docroot',
  'ac-site' => 'drupaledw',
  'ac-env' => 'prod',
  'ac-realm' => 'devcloud',
  'uri' => 'drupaledwqxmngydaqu.devcloud.acquia-sites.com',
  'remote-host' => 'srv-5188.devcloud.hosting.acquia.com',
  'remote-user' => 'drupaledw.prod',
);
$aliases['prod.livedev'] = array(
  'parent' => '@drupaledw.prod',
  'root' => '/mnt/gfs/drupaledw.prod/livedev/docroot',
);

// Site drupaledw, environment test
$aliases['test'] = array(
  'root' => '/var/www/html/drupaledw.test/docroot',
  'ac-site' => 'drupaledw',
  'ac-env' => 'test',
  'ac-realm' => 'devcloud',
  'uri' => 'drupaledwtpaitjjt3j.devcloud.acquia-sites.com',
  'remote-host' => 'srv-5188.devcloud.hosting.acquia.com',
  'remote-user' => 'drupaledw.test',
);
$aliases['test.livedev'] = array(
  'parent' => '@drupaledw.test',
  'root' => '/mnt/gfs/drupaledw.test/livedev/docroot',
);

// Add your local aliases.
if (file_exists(dirname(__FILE__) . '/aliases.local.php')) {
  include dirname(__FILE__) . '/aliases.local.php';
}

