[Issue #1920902: Unable to tidy the bulk export of YamlForm and YamlFormOptions config files 
because Drupal's YAML utility is not a service.](https://www.drupal.org/node/1920902)

---
      
[Issue #2502195: Regression: Form throws LogicException when trying to render a form with 
object as an element's default value.](https://www.drupal.org/node/2502195)  

- Impacts previewing entity autocomplete elements.

---

[Issue #2636066: Access control is not applied to config entity queries](https://www.drupal.org/node/2636066)

  - YAML forms that the current user can't access are not being hidden via the EntityQuery.
  - See: Drupal\yamlform\YamlFormEntityListBuilder

---

[Issue #2484693: Telephone Link field formatter breaks Drupal with 5 digits or less in the number](https://www.drupal.org/node/2720923)

   - Workaround is to manually build a static HTML link.
   - See: \Drupal\yamlform\Plugin\YamlFormElement\Telephone::formatHtml

---

[Issue #2235581: Make Token Dialog support inserting in WYSIWYGs (TinyMCE, CKEditor, etc.)](https://www.drupal.org/node/2235581)

   - Blocks tokens from easily being inserted in CodeMirror widget.
   - Workaround is to disable '#click_insert' functionality from the token
     dialog.
   
---

Config entity does NOT support [Entity Validation API](https://www.drupal.org/node/2015613).

  - Validation constraints are only applicable to content entities and fields.
  - In D8 all config entity validation is handled via 
    \Drupal\Core\Form\FormInterface::validateForm
  - Workaround: Create YamlFormEntityElementsValidator service.      
    
---

[Forms System Issues for Drupal core](https://www.drupal.org/project/issues/drupal?status=Open&version=8.x&component=forms+system)
    
- [Issue #1593964: Allow FAPI usage of the datalist element](https://www.drupal.org/node/1593964)
- [Issue #1183606: Implement Form API support for new HTML5 elements](https://www.drupal.org/node/1183606)   


---

[Unable to alter local actions prior to rendering](https://www.drupal.org/node/2585169)

- Makes it impossible to open an action in a dialog.
