<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormElementCodeMirrorTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for YAML form (render) element extras.
 *
 * @group YamlForm
 */
class YamlFormElementExtrasTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests building of custom elements.
   */
  public function testBuildElements() {
    $this->drupalGet('yamlform/test_element_extras');

    /**************************************************************************/
    // creditcard_number
    /**************************************************************************/

    // Check basic creditcard_number.
    $this->assertRaw('<label for="edit-creditcard-number-basic">Credit card number basic</label>');
    $this->assertRaw('<input data-drupal-selector="edit-creditcard-number-basic" type="text" id="edit-creditcard-number-basic" name="creditcard_number_basic" value="" size="16" maxlength="16" class="form-textfield form-creditcard_number" />');

    /**************************************************************************/
    // email_multiple
    /**************************************************************************/

    // Check basic email_multiple.
    $this->assertRaw('<label for="edit-email-multiple-basic">Multiple email addresses (basic)</label>');
    $this->assertRaw('<input data-drupal-selector="edit-email-multiple-basic" aria-describedby="edit-email-multiple-basic--description" type="text" id="edit-email-multiple-basic" name="email_multiple_basic" value="" size="60" class="form-textfield form-email-multiple" />');
    $this->assertRaw('Multiple email addresses may be separated by commas.');

    /**************************************************************************/
    // email_confirm
    /**************************************************************************/

    // Check basic email_confirm.
    $this->assertRaw('<label for="edit-email-confirm-basic-mail1">Email confirm basic</label>');
    $this->assertRaw('<input class="email-field form-email" data-drupal-selector="edit-email-confirm-basic-mail1" type="email" id="edit-email-confirm-basic-mail1" name="email_confirm_basic[mail1]" value="" size="60" maxlength="254" />');
    $this->assertRaw('<label for="edit-email-confirm-basic-mail2">Confirm Email confirm basic</label>');
    $this->assertRaw('<input class="email-confirm form-email" data-drupal-selector="edit-email-confirm-basic-mail2" type="email" id="edit-email-confirm-basic-mail2" name="email_confirm_basic[mail2]" value="" size="60" maxlength="254" />');

    // Check advanced email_confirm w/ custom label.
    $this->assertRaw('<label for="edit-email-confirm-advanced-mail1">Email confirm advanced</label>');
    $this->assertRaw('<input class="email-field form-email" data-drupal-selector="edit-email-confirm-advanced-mail1" type="email" id="edit-email-confirm-advanced-mail1" name="email_confirm_advanced[mail1]" value="" size="60" maxlength="254" />');
    $this->assertRaw('<label for="edit-email-confirm-advanced-mail2">Please confirm your email address</label>');
    $this->assertRaw('<input class="email-confirm form-email" data-drupal-selector="edit-email-confirm-advanced-mail2" type="email" id="edit-email-confirm-advanced-mail2" name="email_confirm_advanced[mail2]" value="" size="60" maxlength="254" />');

    /**************************************************************************/
    // select_other
    /**************************************************************************/

    // Check basic select_other.
    $this->assertRaw('<select data-drupal-selector="edit-select-other-basic-select" id="edit-select-other-basic-select" name="select_other_basic[select]" class="form-select">');
    $this->assertRaw('<input data-drupal-selector="edit-select-other-basic-other" type="text" id="edit-select-other-basic-other" name="select_other_basic[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');
    $this->assertRaw('<option value="_other_" selected="selected">Other...</option>');

    // Check advanced select_other w/ custom label.
    $this->assertRaw('<label for="edit-select-other-advanced-select" class="js-form-required form-required">Select other advanced</label>');
    $this->assertRaw('<select data-drupal-selector="edit-select-other-advanced-select" id="edit-select-other-advanced-select" name="select_other_advanced[select]" class="form-select required" required="required" aria-required="true">');
    $this->assertRaw('<option value="_other_" selected="selected">Is there another option you wish to enter?</option>');
    $this->assertRaw('<input data-drupal-selector="edit-select-other-advanced-other" aria-describedby="edit-select-other-advanced-other--description" type="text" id="edit-select-other-advanced-other" name="select_other_advanced[other]" value="Four" size="60" maxlength="128" placeholder="What is this other option" class="form-text" />');
    $this->assertRaw('<div id="edit-select-other-advanced-other--description" class="description">');
    $this->assertRaw('Other select description');

    // Check multiple select_other.
    $this->assertRaw('<label for="edit-select-other-multiple-select">Select other multiple</label>');
    $this->assertRaw('<select data-drupal-selector="edit-select-other-multiple-select" multiple="multiple" name="select_other_multiple[select][]" id="edit-select-other-multiple-select" class="form-select">');
    $this->assertRaw('<input data-drupal-selector="edit-select-other-multiple-other" type="text" id="edit-select-other-multiple-other" name="select_other_multiple[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    /**************************************************************************/
    // checkboxes
    /**************************************************************************/

    // Check basic checkboxes.
    $this->assertRaw('<span class="fieldset-legend">Checkboxes other basic</span>');
    $this->assertRaw('<input data-drupal-selector="edit-checkboxes-other-basic-checkboxes-other-" type="checkbox" id="edit-checkboxes-other-basic-checkboxes-other-" name="checkboxes_other_basic[checkboxes][_other_]" value="_other_" checked="checked" class="form-checkbox" />');
    $this->assertRaw('<label for="edit-checkboxes-other-basic-checkboxes-other-" class="option">Other...</label>');
    $this->assertRaw('<input data-drupal-selector="edit-checkboxes-other-basic-other" type="text" id="edit-checkboxes-other-basic-other" name="checkboxes_other_basic[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    // Check advanced checkboxes.
    $this->assertRaw('<span class="fieldset-legend js-form-required form-required">Checkboxes other advanced</span>');
    $this->assertRaw('<input data-drupal-selector="edit-checkboxes-other-advanced-other" aria-describedby="edit-checkboxes-other-advanced-other--description" type="text" id="edit-checkboxes-other-advanced-other" name="checkboxes_other_advanced[other]" value="Four" size="60" maxlength="128" placeholder="What is this other option" class="form-text" />');
    $this->assertRaw('<div id="edit-checkboxes-other-advanced-other--description" class="description">');
    $this->assertRaw('Other checkbox description');

    /**************************************************************************/
    // radios
    /**************************************************************************/

    // Check basic radios_other.
    $this->assertRaw('<span class="fieldset-legend">Radios other basic</span>');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-basic-radios-other-" type="radio" id="edit-radios-other-basic-radios-other-" name="radios_other_basic[radios]" value="_other_" checked="checked" class="form-radio" />');
    $this->assertRaw('<label for="edit-radios-other-basic-radios-other-" class="option">Other...</label>');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-basic-other" type="text" id="edit-radios-other-basic-other" name="radios_other_basic[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    // Check advanced radios_other w/ custom label.
    $this->assertRaw('<span class="fieldset-legend js-form-required form-required">Radios other advanced</span>');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-advanced-radios-other-" type="radio" id="edit-radios-other-advanced-radios-other-" name="radios_other_advanced[radios]" value="_other_" checked="checked" class="form-radio" />');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-advanced-other" aria-describedby="edit-radios-other-advanced-other--description" type="text" id="edit-radios-other-advanced-other" name="radios_other_advanced[other]" value="Four" size="60" maxlength="128" placeholder="What is this other option" class="form-text" />');
    $this->assertRaw('<div id="edit-radios-other-advanced-other--description" class="description">');
    $this->assertRaw('Other radio description');

    /**************************************************************************/
    // likert
    /**************************************************************************/

    $this->assertRaw('<table data-likert-answers-count="3" data-drupal-selector="edit-likert-basic-table" id="edit-likert-basic-table" class="sticky-enabled responsive-enabled" data-striping="1">');
    $this->assertPattern('#<th></th>\s+<th>Option 1</th>\s+<th>Option 2</th>\s+<th>Option 3</th>#');
    $this->assertRaw('<label for="edit-likert-basic-table-q1-question-title">Question 1</label>');
    $this->assertRaw('<td><div class="js-form-item form-item js-form-type-radio form-type-radio js-form-item-likert-basic-q1 form-item-likert-basic-q1">');
    $this->assertRaw('<input data-drupal-selector="edit-likert-basic-q1" type="radio" id="edit-likert-basic-q1" name="likert_basic[q1]" value="1" class="form-radio" />');
    $this->assertRaw('<label for="edit-likert-basic-q1" class="option">Option 1</label>');

    /**************************************************************************/
    // code:yaml
    /**************************************************************************/

    // Check YAML.
    $this->assertRaw('<label for="edit-yaml-basic">YAML basic</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-yaml-basic" class="js-yamlform-codemirror yamlform-codemirror yaml form-textarea resize-vertical" data-yamlform-codemirror-mode="text/x-yaml" id="edit-yaml-basic" name="yaml_basic" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // code:html
    /**************************************************************************/

    // Check HTML.
    $this->assertRaw('<label for="edit-html-basic">HTML basic</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-html-basic" class="js-yamlform-codemirror yamlform-codemirror html form-textarea resize-vertical" data-yamlform-codemirror-mode="text/html" id="edit-html-basic" name="html_basic" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // code:text
    /**************************************************************************/

    // Check Text.
    $this->assertRaw('<label for="edit-text-basic">Text basic</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-text-basic" class="js-yamlform-codemirror yamlform-codemirror text form-textarea resize-vertical" data-yamlform-codemirror-mode="text/plain" id="edit-text-basic" name="text_basic" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // yamlform_element_options
    /**************************************************************************/

    // Check YAML form element options
    $this->assertRaw('<select class="js-edit-options-options form-select" data-drupal-selector="edit-options-options" aria-describedby="edit-options-options--description" id="edit-options-options" name="options[options]">');
    $this->assertRaw('<option value="yes_no" selected="selected">Yes/No</option>');

    // Check YAML form element options (custom)
    $this->assertRaw('<select class="js-edit-options-custom--2-options form-select" data-drupal-selector="edit-options-custom-options" aria-describedby="edit-options-custom-options--description" id="edit-options-custom-options" name="options_custom[options]">');
    $this->assertRaw('<option value="" selected="selected">Custom...</option>');
    $this->assertRaw(">1: one\n2: two\n3: three\n</textarea>");

    // Check YAML form element options (likert)
    $this->assertRaw('<select class="js-edit-options-likert-options form-select" data-drupal-selector="edit-options-likert-options" aria-describedby="edit-options-likert-options--description" id="edit-options-likert-options" name="options_likert[options]">');
    $this->assertRaw('<option value="">Custom...</option><option value="likert_agreement">');

    /**************************************************************************/
    // contact (composite element)
    /**************************************************************************/

    // Check YAML form contact basic.
    $this->assertRaw('<fieldset data-drupal-selector="edit-contact-basic" id="edit-contact-basic--wrapper" class="fieldgroup form-composite required js-form-item form-item js-form-wrapper form-wrapper" required="required" aria-required="true">');
    $this->assertRaw('<span class="fieldset-legend js-form-required form-required">Contact basic</span>');
    $this->assertRaw('<label for="edit-contact-basic-name" class="js-form-required form-required">Name</label>');
    $this->assertRaw('<input data-drupal-selector="edit-contact-basic-name" type="text" id="edit-contact-basic-name" name="contact_basic[name]" value="John Smith" size="60" maxlength="128" class="form-text required" required="required" aria-required="true" />');

    // Check custom name title, description, and required.
    $this->assertRaw('<label for="edit-contact-advanced-name" class="js-form-required form-required">Custom contact name</label>');
    $this->assertRaw('<input data-drupal-selector="edit-contact-advanced-name" aria-describedby="edit-contact-advanced-name--description" type="text" id="edit-contact-advanced-name" name="contact_advanced[name]" value="John Smith" size="60" maxlength="128" class="form-text required" required="required" aria-required="true" />');
    $this->assertRaw('Custom contact name description');

    // Check custom state type and not required.
    $this->assertRaw('<label for="edit-contact-advanced-state-province">State/Province</label>');
    $this->assertRaw('<input data-drupal-selector="edit-contact-advanced-state-province" type="text" id="edit-contact-advanced-state-province" name="contact_advanced[state_province]" value="New Jersey" size="60" maxlength="128" class="form-text" />');

    // Check custom country access.
    $this->assertNoRaw('edit-contact-advanced-country');

    /**************************************************************************/
    // creditcard (composite element)
    /**************************************************************************/

    $this->assertRaw('<fieldset data-drupal-selector="edit-creditcard" id="edit-creditcard--wrapper" class="fieldgroup form-composite js-form-item form-item js-form-wrapper form-wrapper">');
    $this->assertRaw('<span class="fieldset-legend">Credit Card</span>');
    $this->assertRaw('<div data-drupal-selector="edit-creditcard" id="edit-creditcard--wrapper" class="fieldgroup form-composite js-form-wrapper form-wrapper"><div class="messages messages--warning">The credit card element is experimental and insecure because it stores information as plain text.</div><div class="js-form-item form-item js-form-type-textfield form-type-textfield js-form-item-creditcard-name form-item-creditcard-name">');
    $this->assertRaw('<label for="edit-creditcard-name">Name on Card</label>');
    $this->assertRaw('<input data-drupal-selector="edit-creditcard-name" type="text" id="edit-creditcard-name" name="creditcard[name]" value="John Smith" size="60" maxlength="128" class="form-text" />');
    $this->assertRaw('<select data-drupal-selector="edit-creditcard-expiration-month" id="edit-creditcard-expiration-month" name="creditcard[expiration_month]" class="form-select">');
  }

  /**
   * Tests value processing for custom elements.
   */
  public function testProcessingElements() {

    /**************************************************************************/
    // creditcard_number
    /**************************************************************************/

    // Check invalid credit card number.
    $edit = [
      'creditcard_number_basic' => 'not value',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The credit card number is not valid.');

    // Check valid credit card number.
    $edit = [
      'creditcard_number_basic' => '4111111111111111',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('The credit card number is not valid.');

    // Check valid AmEx (15 digit).
    $edit = [
      'creditcard_number_basic' => '378282246310005',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('The credit card number is not valid.');

    /**************************************************************************/
    // email_multiple
    /**************************************************************************/

    // Check invalid second email address.
    $edit = [
      'email_multiple_basic' => 'example@example.com, Not a valid email address',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The email address <em class="placeholder">Not a valid email address</em> is not valid.');

    // Check invalid token email address.
    $edit = [
      'email_multiple_basic' => 'example@example.com, [token]',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The email address <em class="placeholder">[token]</em> is not valid.');

    // Check valid second email address.
    $edit = [
      'email_multiple_basic' => 'example@example.com, other@other.com',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw("email_multiple_basic: 'example@example.com, other@other.com'");

    // Check valid token email address (via #allow_tokens).
    $edit = [
      'email_multiple_advanced' => 'example@example.com, [token]',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw("email_multiple_advanced: 'example@example.com, [token]'");

    /**************************************************************************/
    // email_confirm
    /**************************************************************************/

    // Check invalid email addresses.
    $edit = [
      'email_confirm_advanced[mail1]' => 'Not a valid email address',
      'email_confirm_advanced[mail2]' => 'Not a valid email address, again',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The email address <em class="placeholder">Not a valid email address</em> is not valid.');
    $this->assertRaw('The email address <em class="placeholder">Not a valid email address, again</em> is not valid.');

    // Check non-matching email addresses.
    $edit = [
      'email_confirm_advanced[mail1]' => 'example01@example.com',
      'email_confirm_advanced[mail2]' => 'example02@example.com',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The specified email addresses do not match.');

    // Check matching email addresses.
    $edit = [
      'email_confirm_advanced[mail1]' => 'example@example.com',
      'email_confirm_advanced[mail2]' => 'example@example.com',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<li class="messages__item">The specified email addresses do not match.</li>');
    $this->assertRaw('email_confirm_advanced: example@example.com');

    // Check empty confirm email address.
    $edit = [
      'email_confirm_advanced[mail1]' => '',
      'email_confirm_advanced[mail2]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<li class="messages__item">Confirm Email field is required.</li>');

    /**************************************************************************/
    // select_other
    /**************************************************************************/

    // Check select other required validation.
    $edit = [
      'select_other_advanced[select]' => '',
      'select_other_advanced[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('Select other advanced field is required.');

    // Check select other processing w/ other.
    $edit = [
      'select_other_advanced[select]' => '_other_',
      'select_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('select_other_advanced: Five');

    // Check select other processing w/o other.
    $edit = [
      'select_other_advanced[select]' => 'One',
      // This value is ignored, because 'select_other_advanced[select]' is not set to '_other'.
      'select_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('select_other_advanced: One');
    $this->assertNoRaw('select_other_advanced: Five');

    /**************************************************************************/
    // radios_other
    /**************************************************************************/

    // Check radios other required validation.
    $edit = [
      'radios_other_advanced[radios]' => '_other_',
      'radios_other_advanced[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('Radios other advanced field is required.');

    // Check radios other processing w/ other.
    $edit = [
      'radios_other_advanced[radios]' => '_other_',
      'radios_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('radios_other_advanced: Five');

    // Check radios other processing w/o other.
    $edit = [
      'radios_other_advanced[radios]' => 'One',
      // This value is ignored, because 'radios_other_advanced[radios]' is not set to '_other'.
      'radios_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('radios_other_advanced: One');
    $this->assertNoRaw('radios_other_advanced: Five');

    /**************************************************************************/
    // checkboxes_other
    /**************************************************************************/

    // Check checkboxes other required validation.
    $edit = [
      'checkboxes_other_advanced[checkboxes][One]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Two]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Three]' => FALSE,
      'checkboxes_other_advanced[checkboxes][_other_]' => TRUE,
      'checkboxes_other_advanced[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('Checkboxes other advanced field is required.');

    // Check radios other processing w/ other.
    $edit = [
      'checkboxes_other_advanced[checkboxes][One]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Two]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Three]' => FALSE,
      'checkboxes_other_advanced[checkboxes][_other_]' => TRUE,
      'checkboxes_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('checkboxes_other_advanced:
  - Five');

    // Check checkboxes other processing w/o other.
    $edit = [
      'checkboxes_other_advanced[checkboxes][One]' => TRUE,
      'checkboxes_other_advanced[checkboxes][Two]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Three]' => FALSE,
      'checkboxes_other_advanced[checkboxes][_other_]' => FALSE,
      // This value is ignored, because 'radios_other_advanced[radios]' is not set to '_other'.
      'checkboxes_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('checkboxes_other_advanced:
  - One');
    $this->assertNoRaw('checkboxes_other_advanced:
  - Five');

    /**************************************************************************/
    // code:yaml
    /**************************************************************************/

    // Check invalid YAML.
    $edit = [
      'yaml_basic' => "'not: valid",
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('Malformed inline YAML string (&#039;not: valid) at line 1 (near &quot;&#039;not: valid&quot;).');

    // Check valid YAML.
    $edit = [
      'yaml_basic' => 'is: valid',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw("Malformed inline YAML string ('not: valid) at line 1 (near \"'not: valid \").");

    /**************************************************************************/
    // code:html
    /**************************************************************************/

    // Check invalid HTML.
    $edit = [
      'html_basic' => "<b>bold</bold>",
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">HTML basic</em> is not valid.');
    $this->assertRaw('expected &#039;&gt;&#039;');

    // Check valid HTML.
    $edit = [
      'html_basic' => '<b>bold</b>',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<em class="placeholder">HTML basic</em> is not valid.');
    $this->assertNoRaw('expected &#039;&gt;&#039;');

    /**************************************************************************/
    // likert
    /**************************************************************************/

    // Check likert required.
    $this->drupalPostForm('yamlform/test_element_likert', [], t('Submit'));
    $this->assertRaw('Question 1 field is required.');
    $this->assertRaw('Question 2 field is required.');
    $this->assertRaw('Question 3 field is required.');

    // Check likert processing.
    $edit = [
      'likert[q1]' => '1',
      'likert[q2]' => '2',
      'likert[q3]' => '3',
    ];
    $this->drupalPostForm('yamlform/test_element_likert', $edit, t('Submit'));
    $this->assertRaw("likert:
  q1: '1'
  q2: '2'
  q3: '3'");

    /**************************************************************************/
    // markup
    /**************************************************************************/

    $this->drupalGet('yamlform/test_element_markup');
    $this->assertRaw('<p>This is normal markup</p>');
    $this->assertRaw('<p>This is only displayed on the form view.</p>');
    $this->assertNoRaw('<p>This is only displayed on the submission view.</p>');
    $this->assertRaw('<p>This is displayed on the both the form and submission view.</p>');

    $this->drupalPostForm('yamlform/test_element_markup', [], t('Preview'));
    $this->assertNoRaw('<p>This is normal markup</p>');
    $this->assertNoRaw('<p>This is only displayed on the form view.</p>');
    $this->assertRaw('<p>This is only displayed on the submission view.</p>');
    $this->assertRaw('<p>This is displayed on the both the form and submission view.</p>');

    /**************************************************************************/
    // yamlform_element_options
    /**************************************************************************/

    $this->drupalPostForm('yamlform/test_element_extras', [], t('Submit'));

    $this->assertRaw('options: yes_no');
    $this->assertRaw('options_custom:
  1: one
  2: two
  3: three');
    $this->assertRaw('options_likert: likert_ten_scale');

    /**************************************************************************/
    // contact (composite element)
    /**************************************************************************/

    // Check composite value.
    $this->drupalPostForm('yamlform/test_element_extras', [], t('Submit'));
    $this->assertRaw("contact_basic:
  name: 'John Smith'
  company: Acme
  address: '100 Main Street'
  address_2: 'PO BOX 999'
  city: 'Hill Valley'
  state_province: 'New Jersey'
  country: 'United States of America'
  postal_code: 11111-1111
  email: example@example.com
  phone: 123-456-7890");

    // Check validate required composite elements.
    $edit = [
      'contact_basic[name]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('Name field is required.');

    /**************************************************************************/
    // creditcard (composite element)
    /**************************************************************************/

    // Check composite value.
    $this->drupalPostForm('yamlform/test_element_extras', [], t('Submit'));

    $this->assertRaw("creditcard:
  name: 'John Smith'
  type: VI
  number: '4111111111111111'
  civ: '111'
  expiration_month: '1'
  expiration_year: '2025'");
  }

}
