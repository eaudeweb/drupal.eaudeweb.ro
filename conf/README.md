# Configuration profiles

This directory contains exported Drupal configuration profiles. 
These are used by the `drush config-import/export [--partial]` command to save/import configurations across instances.

Usually directories here look like:
  
  * `default` - for the default configuration (dev)
  * `test` - for testing server
  * `prod` - for the production server