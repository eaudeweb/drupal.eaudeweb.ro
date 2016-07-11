# IUCN Wildlife

# Installation

## Pre-requisites

1. Drush (8.0)
2. Virtual host for your Drupal instance that points to the `docroot` directory from this repo.

## Quick start

1. Copy and rename `docroot/sites/example.settings.local.php` to `docroot/sites/default/settings.local.php`.
2. Configure the ``$databases`, `$trusted_host_patterns` and `$hash_salt` within settings.local.php file.
3. Run `drush sql-sync @drupaledw.<env> @self`.

### Configuration management

#### Export

```
$ drush config-export vcs # shared between environments
$ drush config-get <config-name> > config/<env>/<config-name>.yml # environment-specific overrides
```

**config-name:** The config object name, for example "system.site".
**env:** The environment name, for example "local", "dev", "prod".

#### Import

```
$ drush config-import vcs # shared between environments
$ drush config-import <env> --partial # environment-specific overrides
```

**env:** The environment name, for example "local", "dev", "prod".
