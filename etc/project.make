; The Drush Make API version. This should always be 2.
api = 2

; The version of Drupal the profile is built for. Although you can leave this
; as '7.x', it's better to be precise and define the exact version of core your
; distribution works with.
core = 7.34

projects[drupal][type] = core

; EdW modules
; projects[edw_extras][subdir] = edw
; projects[edw_extras][location] = http://fserver.eaudeweb.ro/fserver
; projects[edw_extras][type] = module

; Very common modules used in almost all projects
projects[ctools][subdir] = contrib
; projects[date][subdir] = contrib
; projects[entity][subdir] = contrib
; projects[entityreference][subdir] = contrib
; projects[features][subdir] = contrib
; projects[ftools][subdir] = contrib
; projects[wysiwyg][subdir] = contrib
; projects[jquery_update][subdir] = contrib
; projects[libraries][subdir] = contrib
; projects[link][subdir] = contrib
; projects[pathauto][subdir] = contrib
; projects[strongarm][subdir] = contrib
; projects[taxonomy_access_fix][subdir] = contrib
; projects[token][subdir] = contrib
; projects[uuid][subdir] = contrib
; projects[variable][subdir] = contrib
projects[views][subdir] = contrib

; Multilingual
; projects[i18n][subdir] = contrib
; projects[entity_translation][subdir] = contrib
; projects[title][subdir] = contrib
; projects[lang_dropdown][subdir] = contrib
; projects[languagefield][subdir] = contrib

; Search modules
; dependencies[] = facetapi
; projects[search_api][subdir] = contrib
; projects[search_api_attachments][subdir] = contrib
; projects[search_api_solr][subdir] = contrib
; projects[search_api_et][subdir] = contrib
; projects[search_api_et_solr][subdir] = contrib
; projects[search_autocomplete][subdir] = contrib

; Common modules
; projects[chosen][subdir] = contrib
; projects[entity_collection][subdir] = contrib
; projects[htmlmail][subdir] = contrib
; projects[mailsystem][subdir] = contrib
; projects[image_field_caption][subdir] = contrib
; projects[menu_block][subdir] = contrib
; projects[migrate][subdir] = contrib
; projects[r4032login][subdir] = contrib
; projects[rules][subdir] = contrib
; projects[views_slideshow][subdir] = contrib

; Development modules
projects[devel][subdir] = contrib
; projects[reroute_email][subdir] = contrib
; projects[diff][subdir] = contrib

; Rare modules
; projects[context][subdir] = contrib
; projects[field_group][subdir] = contrib
; projects[flickr][subdir] = contrib
; projects[on_the_web][subdir] = contrib
; projects[linkchecker][subdir] = contrib
; projects[pathologic][subdir] = contrib
; projects[views_bulk_operations][subdir] = contrib

; Exotic modules
; projects[feeds][subdir] = contrib
; projects[file_entity][subdir] = contrib
; projects[media][subdir] = contrib
; projects[tmgmt][subdir] = contrib
; projects[workbench][subdir] = contrib
; projects[workbench_access][subdir] = contrib
; projects[workbench_moderation][subdir] = contrib
