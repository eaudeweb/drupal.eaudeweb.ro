
Table of Contents
-----------------

 * [About this module](#about-this-module)
 * [Demo](#demo)
 * [Goals](#goals)
 * [Concepts](#concepts)
 * [Example](#example)
 * [Features](#features)
 * [Security](#security)
 * [Installation](#installation) 
 * [Releases](#releases)  
 * [Extending](#extending)
 * [Related Projects](#related-projectss)
 * [Troubleshooting](#troubleshooting)
 * [Notes](#notes)
 * [Similar Projects](#similar-projects)


About this Module
-----------------

The YAML form module is a FAPI based form builder and submission manager for 
Drupal 8.

> <b>IMPORTANT:</b>
> The YAML form module now includes a user interface (UI), which allows 
> site builders to easily build and maintain forms. 
> Developers are still able to 'view source' and edit a form's 
> [FAPI](https://www.drupal.org/node/2117411) 
> [Render Array](https://www.drupal.org/developing/api/8/render/arrays)
> using [YAML](https://en.wikipedia.org/wiki/YAML).

The primary use case for this module is...

- Admin duplicates and customizes an existing form/template.
- Admin tests and publishes the new form.
- User fills in and submits the form.
- Submission is stored in the database.
- Admin receives an email notification. 
- User receives an email confirmation.
- Admin views submissions online.
- Admin downloads all submissions as a CSV.

Additional things to consider...

- Results can be exported as a CSV for use in 
  [Google Sheets](https://www.google.com/sheets/about/)
  or [Excel](https://products.office.com/en-us/excel).
- The alternatives: 
  [Contact](https://www.drupal.org/documentation/modules/contact) with 
  [Contact Storage](https://www.drupal.org/documentation/modules/contact_storage),
  [Eform](https://www.drupal.org/project/eform),
  [Webform](https://www.drupal.org/project/webform),
  [Form.io](https://form.io/),
  [Google Forms](https://www.google.com/forms/about/),
  [SurveyMonky](https://www.surveymonkey.com),
  [Webform.com](https://www.drupal.org/project/webform),
  [Wufoo](http://www.wufoo.com/),
  [etc...](https://www.google.com/search?q=Form+builders)
- How can you help extend and improve this module?


Demo
----

[![Drupal: YamlForm 8.x-1.x Demo - Site Builder Preview ](https://www.drupal.org/files/yamlform-youtube.png)](http://youtu.be/rMxU-d8vanc)

> Evaluate this project online using [simplytest.me](https://simplytest.me/project/yamlform).

> [Watch a demo](http://youtu.be/rMxU-d8vanc) of the YAML form module.

Goals
-----

- A stable, maintainable, and tested API for building forms and submission handling. 
- A pluggable/extensible API for custom submission handling. 
- A focused and limited feature set to ensure maintainability. 


Features
--------
    
**Forms** 

- Create forms using Drupal's Form API (FAPI)
- Custom confirmation page, message, and redirection
- Reusable and customizable list of common select menu, radios, and checkbox 
  options. This includes countries, states, etc...
- Reuse options for autocompletion
- Starter templates for common forms
- Ability to duplicate existing forms and templates
- Prepopulate form with querystring parameters
- Support for confidential submissions, which have no recorded IP address
  and must be submitted while logged out.
- Set custom URL aliases for the form and its confirmation page
- Preview and save draft support

**Elements**

- Support for every
  [form element](https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/8)
  included in Drupal 8 core. This includes file uploads and entity references.
- Conditional logic using [FAPI States API](https://api.drupal.org/api/examples/form_example%21form_example_states.inc/function/form_example_states_form/7)
- Input masks (using [jquery.inputmask](https://github.com/RobinHerbots/jquery.inputmask))
- Private elements
- Unique element values
- Define optional display format for individual elements. 
    - For example, checkboxes can display as comma delimited value or a bullet list.
- Provide custom form elements, including...
    - HTML source CodeMirror editor
    - YAML source CodeMirror editor
    - Text source CodeMirror editor
    - Credit card number
    - Email confirmation
    - Select other
    - Radios other
    - Checkboxes other
    - Composite used for contacts and addresses
    - [Likert scale](https://en.wikipedia.org/wiki/Likert_scale)
- Support for form functionality and elements provided by contrib modules 
  including
  [Honeypot](https://www.drupal.org/project/honeypot),
  [Mollom](https://www.drupal.org/project/mollom),
  [CAPTCHA](https://www.drupal.org/project/captcha),
  [Validators](https://www.drupal.org/project/validators),
  and more to come...

**Submissions/Results**

- View submissions as HTML, plain text, and YAML
- Download results as a CSV to Google Sheets or MS Excel
- Fine grain access control by roles and user
- Users can view previous submissions
- Limit total number of submissions or user specific submissions
- Drush support for exporting CSV and purging submissions

**Emails/Handlers**

- Extensible form submission handler plugin  
- Handles email notifications and confirmations 
- Preview and resend emails
- HTML email (does not require any additional modules)
- File attachments (requires [Mail System](https://www.drupal.org/project/mailsystem), only [Swift Mailer](https://www.drupal.org/project/swiftmailer) has been tested) 

**Multistep Form Wizard**

- Builds multi step forms
- Customize previous and next button labels
- Optional progress bar  

**Third Party Settings**

- Allows contrib modules to define additional settings and behaviors that can be 
  applied to all YAML forms or just one specific YAML form.
  
**Integration**

- Block integration
- Node integration
- Token support
- YAML and HTML source editor using [CodeMirror](https://codemirror.net/)

**Internationalization**

- Translation integration
- Tracks submission language

**Development**

- Generate test submissions using devel generate and customizable test datasets


Security
--------

This module allows developers to have full access to Drupal's Render API,
this includes the ability to set [callbacks](http://php.net/manual/en/language.types.callable.php),
which are PHP functions that are executed during the rendering process.
This means anyone who can administer and build a YAML form can call any PHP code
on your website.

> Only the most trusted users should be granted permission to administer and
  build YAML forms.

_Please note: Administering and exporting a YAML form's results is a dedicated
and secure role._
 

Installation
------------

1. Copy/upload the yamlform.module to the modules directory of your Drupal
   installation.
2. (optional) Manually install [CodeMirror](http://codemirror.net/) and 
    [jquery.inputmask](https://github.com/RobinHerbots/jquery.inputmask)
    into the /libraries directory.
   If CodeMirror and/or jquery.inputmask is not installed, they will be loaded 
   from <https://cdnjs.com/>.
3. Enable the 'YAML form' or the 'YAML form UI' module or the in 'Extend'. 
   (/admin/modules)
4. Setup permissions. (/admin/people/permissions#module-yamlform)
5. Begin building a new YAML form or duplicate an existing one.
   (/admin/structure/yamlform)
6. Publish your new YAML form as a...
    - **Page:** By linking to the published YAML form.
      (/yamlform/contact)  
    - **Node:** By creating a new node that references the YAML form.
      (/node/add/yamlform)
    - **Block:** By placing a YAML form block on your site.
      (/admin/structure/block)

Notes

- Tokens are supported and actively used by the YAML form module. 
  It is recommended that the  
  [Token module](https://www.drupal.org/project/token) is installed.
- For email file attachment support please install and configure the 
  [Mail System](https://www.drupal.org/project/mailsystem) and 
  [Swift Mailer](https://www.drupal.org/project/swiftmailer) modules.


Releases
--------

Even though, the YAML form module is still under active development with
regular [beta releases](https://www.drupal.org/documentation/version-info/alpha-beta-rc)
all YAML form configuration and submission data will be maintained and updated 
between releases.  **APIs can and will be changing** while this module moves 
from beta release and finally a release candidate. 

Simply put it, if you install and use the YAML form module AS-IS, out of the 
box, you _should_ be okay.  Once you start extending YAML forms with plugins, 
alter  hooks, and template overrides, you will need to read the release notes 
and assume _things will be changing_.


Extending
---------

YAML form provides a YamlFormHandler [plugin](https://www.drupal.org/developing/api/8/plugins)
as well as support for third party settings and alter hooks for contrib modules
to extend and enhance a YAML form.

**YamlFormHandler plugin**

The YamlFormHandler plugin allows developers to extend a YAML form's elements 
and submission handling. Each YamlFormHandler plugin should live in a dedicated 
module and handler namespace. For example, if a developer wanted to setup 
MailChimp integration, they would create the YAML form MailChimp module 
(yamlform_mailchimp.module) which would contain the YamlFormMailChimpHandler.

This approach/pattern will allow any popular YamlFormHandler plugins
(that include tests) to be easily contributed back to the main YAML form module.
          
**Third Party Settings**

The YAML form module allows contrib modules to also set third party settings
for all YAML forms and/or one specific YAML form.  

See the yamlform\_test\_third_party\_settings.module for an example of how a
contrib can use third party settings to extend and enhance a YAML form.


Related Projects
----------------

Below are additional projects that extend and provide additional YAML form 
features and functionality.

- [YAML Form Queue](https://www.drupal.org/project/yamlform_queue)  
  Provides a queue handler for YAML Form to store form submissions in a queue.


Notes
-----

- Element names will be used to store data.
- Duplicate element names are not allowed.
- The [#tree](https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/8#tree) 
  property, which is used to allow collections of form elements, is not allowed.
- Element callback properties are not supported within a YAML element.
  This includes `#element_validate`, `#after_build`, `#post_render`, `#pre_render`, `#process`, `#submit`, `#value_callback`, and `#validate`.
- Once there has been a form submission, existing element names should never be
  deleted, they can be be hidden (via `'#access': false`).


Troubleshooting
---------------

**How to debug issues with YAML form elements/elements?**

- A YAML form's element data is just a [Form API(FAPI)](https://www.drupal.org/node/37775)
  [render array](https://www.drupal.org/developing/api/8/render/arrays). 
- Some issues can be fixed by reading the API documentation associated 
  with a given [form element](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement/8).
  Links to a form element's API documentation is include in the Elements 
  overview. (/admin/structure/yamlform/settings/elements)

**How to get help fixing issues with the YAML form module?**

- Review the YAML form module's [issue queue](https://www.drupal.org/project/issues/yamlform) 
  for similar issues.
- If you need to create a new issue, **please** create and export an example of 
  the broken YAML form configuration.   
  _This will helps guarantee that your issue is reproducible._  
- Please also read [How to create a good issue](https://www.drupal.org/issue-queue/how-to)
  and use the [Issue Summary Template](https://www.drupal.org/node/1155816)
  when creating new issues.


Similar Projects
----------------

- [The Best Online Survey Tools of 2016](http://www.pcmag.com/article2/0,2817,2494737,00.asp)
- [Comparison of Form Building Modules](https://www.drupal.org/node/2083353)
- [Contact](https://www.drupal.org/documentation/modules/contact) 
    - [Contact Storage](https://www.drupal.org/project/contact_storage)
    - [Contact module 8.1 and beyond roadmap](https://www.drupal.org/node/2582955)
    - [Goodbye Webform? Contact Forms Are In the Drupal 8 Core](https://www.ostraining.com/blog/drupal/drupal-8-contact-forms/)
- [Eform](https://www.drupal.org/project/eform)
    - [When to use Entityform](https://www.drupal.org/node/1540680)
- [Webform](https://www.drupal.org/project/webform) 
    - [Port Webform to Drupal 8](https://www.drupal.org/node/2075941)


Author/Maintainer
-----------------

- [Jacob Rockowitz](http://drupal.org/user/371407)
