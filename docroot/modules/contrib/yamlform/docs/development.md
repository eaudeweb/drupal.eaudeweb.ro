# Commands

## [Update composer libraries](https://www.drupal.org/project/composer_manager)

    composer drupal-update

## [Apply patch](https://www.drupal.org/node/1399218)

    curl https://www.drupal.org/files/[patch-name].patch | git apply -

## [Revert patch](https://www.drupal.org/patch/reverse)

    curl https://www.drupal.org/files/[patch-name].patch | git apply -R -

## [Create and manage patches](https://www.drupal.org/node/707484)

    # Create and checkout issue branch
    git checkout -b [issue-number]-[issue-description]
    
    # Push issue branch to D.O. (optional)
    git push -u origin [issue-number]-[issue-description]
    
    # Create patch by comparing (current) issue branch with 8.x-1.x branch 
    git diff 8.x-1.x > [project_name]-[issue-description]-[issue-number]-[comment-number]-[drupal-version].patch

## Ignoring patches (and .gitignore)

    cat >> .gitignore <<'EOF'
    .gitignore
    *.patch
    EOF
    
## Merge branch

    # Merge branch with all commits
    git checkout 8.x-1.x
    git merge [issue-number]-[issue-description]
    git push

    # Merge branch as a single new commit
    git checkout 8.x-1.x
    git merge --squash [issue-number]-[issue-description]
    git commit -m 'Issue #[issue-number]: [issue-description]'
    git push
    
## Delete issue branch
 
     # Delete local issue branch.
     git branch -d [issue-number]-[issue-description] 

     # Delete remote issue branch.
     git push origin :[issue-number]-[issue-description]

## [GitFlow](https://www.drupal.org/node/2406727) Cheatsheet
    
    # Create branch
    git checkout -b [issue-number]-[issue-description]
    git push -u origin [issue-number]-[issue-description]
    
    # Create patch
    git diff 8.x-1.x > [project_name]-[issue-description]-[issue-number]-00.patch

    # Merge branch with all commits
    git checkout 8.x-1.x
    git merge [issue-number]-[issue-description]
    git push

    # Merge branch as a single new commit
    git checkout 8.x-1.x
    git merge --squash [issue-number]-[issue-description]
    git commit -m 'Issue #[issue-number]: [issue-description]'
    git push
  
    # Delete branch
    git branch -d [issue-number]-[issue-description]
    git push origin :[issue-number]-[issue-description]

## Reinstall YAML form module.

    drush php-eval 'module_load_include('install', 'yamlform'); yamlform_uninstall();'; drush cron;
    drush php-eval 'module_load_include('install', 'yamlform_node'); yamlform_node_uninstall();'; drush cron; 
    drush yamlform-purge --all -y; drush pmu -y yamlform_test; drush pmu -y yamlform_examples; drush pmu -y yamlform_templates; drush pmu -y yamlform_ui; drush pmu -y yamlform_node; drush pmu -y  yamlform; 
    drush en -y yamlform yamlform_ui yamlform_test;

    # Optional.
    drush en -y yamlform_examples yamlform_templates yamlform_node;
    drush pmy -y yamlform_test_third_party_settings yamlform_translation_test;
    drush en -y yamlform_test_third_party_settings yamlform_translation_test;

## Reinstall YAML form test module.

    drush yamlform-purge --all -y; drush pmu -y yamlform_test; drush en -y yamlform_test;

## Update YAML form configuration using Features module

    # Show the difference between the active config and the default config.
    drush features-diff yamlform
    drush features-diff yamlform_test

    # IMPORTANT: 
    # YOU MUST FIX *.info.yml WHERE THE DEPENDENCIES DECLARE ITSELF..

    # Export and tidy all YAML form configuration from your site.          
    drush features-export -y yamlform
    drush features-export -y yamlform_node
    drush features-export -y yamlform_examples
    drush features-export -y yamlform_templates
    drush features-export -y yamlform_test

    # Re-import all YAML form configuration into your site.      
    drush features-import -y yamlform
    drush features-import -y yamlform_node
    drush features-import -y yamlform_examples
    drush features-import -y yamlform_templates
    drush features-import -y yamlform_test

## Install extra modules.

    drush en -y yamlform captcha honeypot select_or_other;

## Create test roles and users.

    drush role-create developer
    drush role-add-perm developer 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview,administer yamlform,administer blocks,administer nodes'
    drush user-create developer --password="developer"
    drush user-add-role developer developer
    
    drush role-create admin
    drush role-add-perm admin 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview,administer yamlform submission'
    drush user-create admin --password="admin"
    drush user-add-role admin admin

    drush role-create manager
    drush role-add-perm manager 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview'
    drush user-create manager --password="manager"
    drush user-add-role manager manager

    drush role-create user
    drush user-create user --password="user"
    drush user-add-role user user

    drush role-create any
    drush user-create any --password="any"
    drush role-add-perm any 'view the administration theme,access toolbar,view yamlform node submissions any node,edit yamlform node submissions any node,delete yamlform node submissions any node'
    drush user-add-role any any

    drush role-create own
    drush user-create own --password="own"
    drush role-add-perm own 'view the administration theme,access toolbar,view yamlform node submissions own node,edit yamlform node submissions own node,delete yamlform node submissions own node'
    drush user-add-role own own

## Create test submissions for 'Contact' and 'Example: Elements' form.

    drush yamlform-generate contact
    drush yamlform-generate example_elements

## Test update hooks

    drush php-eval 'module_load_include('install', 'yamlform'); ($message = yamlform_update_8001()) ? drupal_set_message($message) : NULL;'
    
## Access developer information

    drush role-add-perm anonymous 'access devel information'
    drush role-add-perm authenticated 'access devel information'

## Update composer packages

    composer drupal-update

## Reinstall

    drush -y site-install\
      --account-mail="example@example.com"\
      --account-name="webmaster"\
      --account-pass="drupal.admin"\
      --site-mail="example@example.com"\
      --site-name="Drupal 8 (dev)";

    # Enable core modules
    drush -y pm-enable\
      book\
      simpletest\
      telephone\
      language\
      locale\
      content_translation\
      config_translation;
  
    # Disable core modules
    drush -y pm-uninstall\
      update;
  
    # Enable contrib modules
    drush -y pm-enable\
      devel\
      devel_generate\
      kint\
      webprofiler\
      yamlform\
      yamlform_test\
      yamlform_translation_test;

# Code Sources and Design Patterns

Below is just a reference to where most of the code snippets and/or 
design pattern were taken from to build this module.
 
- UI/Naming convention: Webform module (Copied)
- YamlForm to YamlFormSubmission bundle: Vocabulary to Term entities
- YamlFormSubmission entity_type and entity_id: Comment entity
- YamlFormSubmission preview: CommentForm
- YamlFormHandler plugin: ImageEffect
- YamlFormEntityReferenceItem field type: FileItem
