<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormPage.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element\Details;

/**
 * Provides a form element for a YAML form 'wizard' page.
 *
 * A YAML form 'wizard' page is simply a detail widget that is transformed
 * into a page via the YamlFormSubmissionForm handler.
 *
 * @FormElement("yamlform_wizard_page")
 */
class YamlFormWizardPage extends Details {

}
