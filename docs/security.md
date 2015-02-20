# Security checklist measures

# 1. Apache web server security configuration

## Virtual host configuration

Please see [apache-example.conf](../etc/apache-example.conf) on how to configure correctly the security of the VH. 


## docroot/

All files in this directory (except those in *docroot/sites/default/files*) must be read-only to Apache process

```
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 .
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 ..
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 authorize.php
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 cron.php
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 favicon.ico
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 .htaccess
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 includes
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 index.php
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 install.php
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 misc
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 modules
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 profiles
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 robots.txt
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 scripts
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 sites
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0 themes
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 update.php
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_content_t:s0 xmlrpc.php
```

# Filesystem security configuration

### docroot/sites/default/files

All files in this directory must be owned and have read-write permissions

```
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 .
drwxr-x---. php    apache unconfined_u:object_r:httpd_sys_content_t:s0 ..
-rw-r-----. php apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 .htaccess
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 css
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 ctools
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 js
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 styles
```

(Optional) Use git to track changes in the files directory to easily find out suspect files (since last commit)

  * git init . && git add . && git commit -m "Initial revision"
  * .git directory should be owned by root account in order to prevent commits to the repo by apache user.
  * from time to time examine and commit new files
  * This is recommended for small sites which have a moderate rate of changes in this directory


### temp/ files directory

WARNING! Do not leave Drupal default /tmp.

This must be set in Drupal as temp directory:

* Configuration screen: admin/config/media/file-system
* Drush command: drush vset file_temporary_path /absolute/path/to/project/temp)

Typical permission configuration for this directory

```
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 .
drwxr-x---. php apache unconfined_u:object_r:httpd_sys_content_t:s0    ..
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 temporary-uploaded-fragment
-rw-r-----. root   apache unconfined_u:object_r:httpd_sys_content_t:s0 .htaccess
```


### private/ files directory

WARNING! Never store private files under sites/default/files!

This is optional, but recommended. By default private storage is not configured. To set it:

* Configuration screen: admin/config/media/file-system
* Drush command: drush vset file_private_path /absolute/path/to/project/private

Typical permission configuration for this directory:

```
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 .
drwxr-x---. php    apache unconfined_u:object_r:httpd_sys_content_t:s0 ..
drwxrwx---. apache apache unconfined_u:object_r:httpd_sys_rw_content_t:s0 decision
-rw-r-----. root   apache unconfined_u:object_r:httpd_sys_content_t:s0 .htaccess
```

# Drupal instance security configuration checklist

1. Always set explicit temporary directory outside /tmp and configure security properly as stated above

2. Disable PHP input filter and all modules that allows entering PHP from the back-end

