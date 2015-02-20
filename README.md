# What is this

This repository is useful for you if you want to a start a new Drupal project. Provides a directory layout, a project configuration file and a set of drush commands that helps you manage in the future the Drupal instance.

# How to use it

Suppose you want to bootstrap your new project based on this skelet. So, what should you do?

1. Use _Download ZIP_ Github feature from right menu and unpack into a new directory. 
2. Edit [etc/project.yml](etc/project.yml) to configure project global variables (used by everyone in the team)
3. Instruct each team member to override these global settings by creating a local *etc/local.yml* file
4. Customize [etc/project.make](etc/project.make) and [etc/profiles/standard/standard.info](etc/profiles/standard/standard.info) files to enable the modules you want to have for the new project
5. Run ``drush sk-setup`` to download the Drupal core & modules (wrapper around ``drush make``)
6. Run ``drush sk-install`` to create the database and install the Drupal instance on your computer (wrapper around ``drush site-install``)

Now that you have a new project in place, you want to create a new repo from it and share with the team.

7. Use ``git init .`` to create a new repo for your project.
8. Commit & push the changes
9. To further receive bug fixes and updates from this project you can add a new origin: ``git remote add skeledrup https://github.com/cristiroma/skeledrup``
10. Use ``git fetch`` to download updates
11. Use ``git merge skeledrup/master`` to include the updates in your working tree

Repeat steps 10 and 11 each time you feel you need to bringing updates to your project. Happy coding :)

12. (Optional) Instead of fixing bugs of this project in your own repo, fork this and contribute back using pull requests - then use bring them to your repo ;)

# Writing tests

Please read [tests/readme.md](tests/readme.md) for more information on writing tests for your project.

# List of drush commands

## Project related

* ``drush sk`` - overview of these commands
* ``drush sk-setup`` - validate configuration and prepare the Drupal instance
* ``drush sk-install`` - install a clean site
* ``drush sk-update`` - update an existing site by running database updates, revert the features etc.
* ``drush sk-devify`` - configure instance with development environment customisations
* ``drush sk-reset-variables`` - Reset the instance variables to their values from config Yaml file
* ``drush sk-clean`` - Remove docroot/ folder

## Block related commands

* ``drush block-configure <theme> <module> <delta> [region] [weight]`` - Saves single block configuration (ie move block to a region)
* ``drush block-disable <theme> <module> <delta>`` - Quickly disable a single block
* ``drush block-show <theme> <module> <delta>`` - Show the configuration options for one or more blocks

## Solr related commands
* ``drush solr-config-reset [solr-machine1,solr-machine2]`` - Show the configuration options for one or more blocks

# Credits

Credit to this project goes to:

* [Lullabot's boilerplate](https://github.com/Lullabot/drupal-boilerplate) and _also parts of the code have been copied into this project_.
* [drush-extras](https://www.drupal.org/project/drush_extras) - Useful extra commands available for drush

We're using it in our projects here @ [Eau de Web](http://www.eaudeweb.ro).


*Made in Romania*

