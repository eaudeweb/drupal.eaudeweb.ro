<?php

function standard_setup_users() {
  $cfg = standard_get_config();
  unset($cfg['users']['admin']);
  if (!empty($cfg['users'])) {
    foreach($cfg['users'] as $username => $config) {
      $user = array_merge(
        array(
          'status' => 1,
          'roles' => array(),
        ),
        $config
      );
      user_save(NULL, $user);
    }
  }
}
