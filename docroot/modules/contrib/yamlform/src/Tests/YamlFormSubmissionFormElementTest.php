<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlSubmissionFormElementTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form submission form element.
 *
 * @group YamlForm
 */
class YamlFormSubmissionFormElementTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests prepare elements.
   */
  public function testPrepareElements() {
    // Check invalid elements.
    $this->drupalGet('yamlform/test_element_invalid');
    $this->assertRaw('Unable to display this form. Please contact the site administrator.');

    // Check ignored properties.
    $yamlform_ignored_properties = YamlForm::load('test_element_ignored_properties');
    $elements = $yamlform_ignored_properties->getElementsInitialized();
    foreach (YamlForm::getIgnoredProperties() as $ignored_property) {
      $this->assert(!isset($elements['test'][$ignored_property]), new FormattableMarkup('@property ignored.', ['@property' => $ignored_property]));
    }

    /* Test #private element property */

    // Check element with #private property hidden for normal user.
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('yamlform/test_element_private');
    $this->assertNoFieldByName('private', '');

    // Check element with #private property visible for admin user.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_element_private');
    $this->assertFieldByName('private', '');

    /* Test #unique element property */

    $yamlform_unique = YamlForm::load('test_element_unique');

    // Check element with #unique property only allows one unique 'value' to be
    // submitted.
    $sid = $this->postSubmission($yamlform_unique, [], t('Submit'));
    $this->assertNoRaw('The value <em class="placeholder">value</em> has already been submitted once for the <em class="placeholder">textfield</em> field. You may have already submitted this form, or you need to use a different value.');
    $this->drupalPostForm('yamlform/test_element_unique', [], t('Submit'));
    $this->assertRaw('The value <em class="placeholder">value</em> has already been submitted once for the <em class="placeholder">textfield</em> field. You may have already submitted this form, or you need to use a different value.');

    // Check element with #unique can be updated.
    $this->drupalPostForm("admin/structure/yamlform/results/manage/$sid/edit", [], t('Submit'));
    $this->assertNoRaw('The value <em class="placeholder">value</em> has already been submitted once for the <em class="placeholder">textfield</em> field. You may have already submitted this form, or you need to use a different value.');
    $this->assertRaw('New submission added to Test: Element: Unique.');

    /* Test data elements */

    $yamlform_dates = YamlForm::load('test_element_dates');

    // Check 'text_format' values.
    $this->drupalGet('yamlform/test_element_dates');
    $this->assertFieldByName('date', '2009-08-18');
    $this->assertFieldByName('datetime[date]', '2009-08-18');
    $this->assertFieldByName('datetime[time]', '16:00:00');
    $this->assertFieldByName('datelist_date[month]', '8');

    // Check 'datelist' and 'datetime' #default_value.
    $form = $yamlform_dates->getSubmissionForm();
    $this->assert(is_string($form['elements']['date']['#default_value']), 'date #default_value is a string.');
    $this->assert($form['elements']['datetime']['#default_value'] instanceof DrupalDateTime, 'datelist_date #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');
    $this->assert($form['elements']['datelist_date']['#default_value'] instanceof DrupalDateTime, 'datelist_date #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');

    // Check 'entity_autocomplete' #default_value.
    $yamlform_entity_autocomplete = YamlForm::load('test_element_entity_autocomplete');

    /* Test entity_autocomplete element */

    $this->drupalGet('yamlform/test_element_entity_autocomplete');
    $this->assertFieldByName('user', 'admin (1)');

    // Issue #2471154 Anonymous user label can't be viewed and auth user labels
    // are only accessible with 'access user profiles' permission.
    // https://www.drupal.org/node/2471154
    // Check if 'view label' access for accounts is supported (8.2.x+).
    if (User::load(0)->access('view label')) {
      $this->assertFieldByName('users', 'Anonymous (0), admin (1)');
    }
    else {
      $this->assertFieldByName('users', '- Restricted access - (0), admin (1)');
    }

    $form = $yamlform_entity_autocomplete->getSubmissionForm();

    // Single entity (w/o #tags).
    // TODO: (TESTING) Figure out why the below #default_value is an array when it should be the entity.
    // @see \Drupal\yamlform\YamlFormSubmissionForm::prepareElements()
    $this->assert($form['elements']['user']['#default_value'][0] instanceof AccountInterface, 'user #default_value instance of \Drupal\Core\Session\AccountInterface.');

    // Multiple entities (w #tags).
    $this->assert($form['elements']['users']['#default_value'][0] instanceof AccountInterface, 'users #default_value instance of \Drupal\Core\Session\AccountInterface.');
    $this->assert($form['elements']['users']['#default_value'][1] instanceof AccountInterface, 'users #default_value instance of \Drupal\Core\Session\AccountInterface.');

    /* Test text format element */

    $yamlform_text_format = YamlForm::load('test_element_text_format');

    // Check 'text_format' values.
    $this->drupalGet('yamlform/test_element_text_format');
    $this->assertFieldByName('text_format[value]', 'The quick brown fox jumped over the lazy dog.');
    $this->assertRaw('No HTML tags allowed.');

    $text_format = [
      'value' => 'Custom value',
      'format' => 'custom_format',
    ];
    $form = $yamlform_text_format->getSubmissionForm(['data' => ['text_format' => $text_format]]);
    $this->assertEqual($form['elements']['text_format']['#default_value'], $text_format['value']);
    $this->assertEqual($form['elements']['text_format']['#format'], $text_format['format']);

    // Check elements properties moved to the form.
    $this->drupalGet('yamlform/test_form_properties');
    $this->assertPattern('/Form prefix<form /');
    $this->assertPattern('/<\/form>\s+Form suffix/');
    $this->assertRaw('form class="yamlform-submission-test-form-properties-form yamlform-submission-form test-form-properties" invalid="invalid" style="border: 10px solid red; padding: 1em;"');
  }

}
