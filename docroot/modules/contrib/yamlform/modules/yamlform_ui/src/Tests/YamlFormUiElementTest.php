<?php

/**
 * @file
 * Definition of Drupal\yamlform_ui\test\YamlFormUiElementTest.
 */

namespace Drupal\yamlform_ui\Tests;

use Drupal\yamlform\Tests\YamlFormTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form UI element.
 *
 * @group YamlFormUi
 */
class YamlFormUiElementTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'filter', 'user', 'yamlform', 'yamlform_test', 'yamlform_examples', 'yamlform_ui'];

  /**
   * Tests element.
   */
  public function testElements() {
    $this->drupalLogin($this->adminFormUser);

    /**************************************************************************/
    // Reordering
    /**************************************************************************/

    // Check reordered elements.
    $yamlform_contact = YamlForm::load('contact');

    // Check original contact element order.
    $this->assertEqual(['name', 'email', 'subject', 'message'], array_keys($yamlform_contact->getElementsDecodedAndFlattened()));

    // Check updated (reverse) contact element order.
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform_contact */
    $edit = [
      'elements_reordered[message][weight]' => 0,
      'elements_reordered[subject][weight]' => 1,
      'elements_reordered[email][weight]' => 2,
      'elements_reordered[name][weight]' => 3,
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact', $edit, t('Save elements'));

    \Drupal::entityManager()->getStorage('yamlform_submission')->resetCache();
    $yamlform_contact = YamlForm::load('contact');
    $this->assertEqual(['message', 'subject', 'email', 'name'], array_keys($yamlform_contact->getElementsDecodedAndFlattened()));

    /**************************************************************************/
    // CRUD
    /**************************************************************************/

    // Check create element.
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/element/add/textfield', ['key' => 'test', 'properties[title]' => 'Test'], t('Save'));

    // Check read element.
    $this->drupalGet('yamlform/contact');
    $this->assertRaw('<label for="edit-test">Test</label>');
    $this->assertRaw('<input data-drupal-selector="edit-test" type="text" id="edit-test" name="test" value="" size="60" maxlength="255" class="form-text" />');

    // Check update element.
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/element/test/edit', ['properties[title]' => 'Test 123', 'properties[default_value]' => 'This is a default value'], t('Save'));
    $this->drupalGet('yamlform/contact');
    $this->assertRaw('<label for="edit-test">Test 123</label>');
    $this->assertRaw('<input data-drupal-selector="edit-test" type="text" id="edit-test" name="test" value="This is a default value" size="60" maxlength="255" class="form-text" />');

    // Check that 'test' element is being added to the yamlform_submission_data table.
    $this->drupalPostForm('yamlform/contact/test', [], t('Send message'));
    $this->assertEqual(1, db_query("SELECT COUNT(sid) FROM {yamlform_submission_data} WHERE yamlform_id='contact' AND name='test'")->fetchField());

    // Check delete element.
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/element/test/delete', [], t('Delete'));
    $this->drupalGet('yamlform/contact');
    $this->assertNoRaw('<label for="edit-test">Test 123</label>');
    $this->assertNoRaw('<input data-drupal-selector="edit-test" type="text" id="edit-test" name="test" value="This is a default value" size="60" maxlength="255" class="form-text" />');

    // Check that 'test' element values were deleted from the yamlform_submission_data table.
    $this->assertEqual(0, db_query("SELECT COUNT(sid) FROM {yamlform_submission_data} WHERE yamlform_id='contact' AND name='test'")->fetchField());

    /**************************************************************************/
    // Element properties.
    /**************************************************************************/

    // Loops through all the elements, edits them via the UI, and check that
    // the element's render array has not be altered.
    // This verifies that the edit element form it not expectedly altering
    // an elements render array.
    $yamlform_ids = ['example_elements', 'test_element_extras'];
    foreach ($yamlform_ids as $yamlform_id) {
      /** @var \Drupal\yamlform\YamlFormInterface $yamlform_elements */
      $yamlform_elements = YamlForm::load($yamlform_id);
      $original_elements = $yamlform_elements->getElementsDecodedAndFlattened();
      foreach ($original_elements as $key => $original_element) {
        $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform_elements->id() . '/element/' . $key . '/edit', [], t('Save'));

        // Must reset the YAML form entity cache so that the update elements can
        // be loaded.
        \Drupal::entityManager()->getStorage('yamlform_submission')->resetCache();

        /** @var \Drupal\yamlform\YamlFormInterface $yamlform_elements */
        $yamlform_elements = YamlForm::load($yamlform_id);
        $updated_element = $yamlform_elements->getElementsDecodedAndFlattened()[$key];

        $this->assertEqual($original_element, $updated_element, "'$key'' properties is equal.");
      }

    }
  }

  /**
   * Tests permissions.
   */
  public function testPermissions() {
    $yamlform = $this->createYamlForm();

    // Check source page access not visible to user with 'administer yamlform'
    // permission.
    $account = $this->drupalCreateUser(['administer yamlform']);
    $this->drupalLogin($account);
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/source');
    $this->assertResponse(403);
    $this->drupalLogout();

    // Check source page access not visible to user with 'edit yamlform source'
    // without 'administer yamlform' permission.
    $account = $this->drupalCreateUser(['edit yamlform source']);
    $this->drupalLogin($account);
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/source');
    $this->assertResponse(403);
    $this->drupalLogout();

    // Check source page access visible to user with 'edit yamlform source'
    // and 'administer yamlform' permission.
    $account = $this->drupalCreateUser(['administer yamlform', 'edit yamlform source']);
    $this->drupalLogin($account);
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/source');
    $this->assertResponse(200);
    $this->drupalLogout();
  }

}
