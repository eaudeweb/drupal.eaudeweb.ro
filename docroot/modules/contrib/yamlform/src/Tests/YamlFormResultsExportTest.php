<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormResultsExportTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\YamlFormInterface;

/**
 * Tests for YAML form results export.
 *
 * @group YamlForm
 */
class YamlFormResultsExportTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'locale', 'yamlform', 'yamlform_test'];

  /**
   * Tests export.
   */
  public function testExportOptions() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    $this->drupalLogin($this->adminSubmissionUser);

    // Check default options.
    $this->getExport($yamlform);
    $this->assertRaw('"First name","Last name"');
    $this->assertRaw('George,Washington');
    $this->assertRaw('Abraham,Lincoln');
    $this->assertRaw('Hillary,Clinton');

    // Check delimiter.
    $this->getExport($yamlform, ['delimiter' => '|']);
    $this->assertRaw('"First name"|"Last name"');
    $this->assertRaw('George|Washington');

    // Check header keys = label.
    $this->getExport($yamlform, ['header_keys' => 'label']);
    $this->assertRaw('"First name","Last name"');

    // Check header keys = key.
    $this->getExport($yamlform, ['header_keys' => 'key']);
    $this->assertRaw('first_name,last_name');

    // Check options format compact.
    $this->getExport($yamlform, ['options_format' => 'compact']);
    $this->assertRaw('"Flag colors"');
    $this->assertRaw('"Red,White,Blue"');

    // Check options format separate.
    $this->getExport($yamlform, ['options_format' => 'separate']);
    $this->assertRaw("Red,White,Blue");
    $this->assertNoRaw('"Flag colors"');
    $this->assertRaw('X,X,X');
    $this->assertNoRaw('"Red,White,Blue"');

    // Check options item format label.
    $this->getExport($yamlform, ['options_item_format' => 'label']);
    $this->assertRaw('"Red,White,Blue"');

    // Check options item format key.
    $this->getExport($yamlform, ['options_item_format' => 'key']);
    $this->assertNoRaw('"Red,White,Blue"');
    $this->assertRaw('"red,white,blue"');

    // Check entity reference format link.
    $nodes = $this->getNodes();
    $this->getExport($yamlform, ['entity_reference_format' => 'link']);
    $this->assertRaw('"Favorite node ID","Favorite node Title","Favorite node URL"');
    $this->assertRaw('' . $nodes[0]->id() . ',"' . $nodes[0]->label() . '",' . $nodes[0]->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Check entity reference format id.
    $this->getExport($yamlform, ['entity_reference_format' => 'id']);
    $this->assertRaw('"Favorite node"');
    $this->assertNoRaw('"Favorite node Title","Favorite node ID","Favorite node URL"');
    $this->assertRaw(',node:' . $nodes[0]->id() . ',');
    $this->assertNoRaw('"' . $nodes[0]->label() . '",' . $nodes[0]->id() . ',' . $nodes[0]->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Check likert questions format label.
    $this->getExport($yamlform, ['likert_questions_format' => 'label']);
    $this->assertRaw('"Question 1","Question 2","Question 3"');

    // Check likert questions format key.
    $this->getExport($yamlform, ['likert_questions_format' => 'key']);
    $this->assertNoRaw('"Question 1","Question 2","Question 3"');
    $this->assertRaw('q1,q2,q3');

    // Check likert answers format label.
    $this->getExport($yamlform, ['likert_answers_format' => 'label']);
    $this->assertRaw('"Answer 1","Answer 1","Answer 1"');

    // Check likert answers format key.
    $this->getExport($yamlform, ['likert_answers_format' => 'key']);
    $this->assertNoRaw('"Option 1","Option 1","Option 1"');
    $this->assertRaw('1,1,1');

    // Check composite w/o header prefix.
    $this->getExport($yamlform, ['composite_header_prefix' => 1]);
    $this->assertRaw('"Address: Address","Address: Address 2","Address: City/Town","Address: State/Province","Address: Country","Address: Zip/Postal Code"');

    // Check composite w header prefix.
    $this->getExport($yamlform, ['composite_header_prefix' => 0]);
    $this->assertRaw('Address,"Address 2",City/Town,State/Province,Country,"Zip/Postal Code"');

    // Check limit.
    $this->getExport($yamlform, ['range_type' => 'latest', 'range_latest' => 1]);
    $this->assertRaw('Hillary,Clinton');
    $this->assertNoRaw('George,Washington');
    $this->assertNoRaw('Abraham,Lincoln');

    // Check sid start.
    $this->getExport($yamlform, ['range_type' => 'sid', 'range_start' => $submissions[1]->id()]);
    $this->assertNoRaw('George,Washington');
    $this->assertRaw('Abraham,Lincoln');
    $this->assertRaw('Hillary,Clinton');

    // Check sid range.
    $this->getExport($yamlform, ['range_type' => 'sid', 'range_start' => $submissions[1]->id(), 'range_end' => $submissions[1]->id()]);
    $this->assertNoRaw('George,Washington');
    $this->assertRaw('Abraham,Lincoln');
    $this->assertNoRaw('Hillary,Clinton');

    // Check date range.
    $submissions[0]->set('created', strtotime('1/01/2000'))->save();
    $submissions[1]->set('created', strtotime('1/01/2001'))->save();
    $submissions[2]->set('created', strtotime('1/01/2002'))->save();
    $this->getExport($yamlform, ['range_type' => 'date', 'range_start' => '12/31/2000', 'range_end' => '12/31/2001']);
    $this->assertNoRaw('George,Washington');
    $this->assertRaw('Abraham,Lincoln');
    $this->assertNoRaw('Hillary,Clinton');

    // Check entity type and id hidden.
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download');
    $this->assertNoFieldById('edit-export-download-submitted-entity-type');

    // Change submission 0 & 1 to be submitted user account.
    $submissions[0]->set('entity_type', 'user')->set('entity_id', '1')->save();
    $submissions[1]->set('entity_type', 'user')->set('entity_id', '2')->save();

    // Check entity type and id visible.
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download');
    $this->assertFieldById('edit-export-download-submitted-entity-type');

    // Check entity type limit.
    $this->getExport($yamlform, ['entity_type' => 'user']);
    $this->assertRaw('George,Washington');
    $this->assertRaw('Abraham,Lincoln');
    $this->assertNoRaw('Hillary,Clinton');

    // Check entity type and id limit.
    $this->getExport($yamlform, ['entity_type' => 'user', 'entity_id' => '1']);
    $this->assertRaw('George,Washington');
    $this->assertNoRaw('Abraham,Lincoln');
    $this->assertNoRaw('Hillary,Clinton');

    // Check changing default export (delimiter) settings.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalPostForm('admin/structure/yamlform/settings', ['export[format][delimiter]' => '|'], t('Save configuration'));
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download', [], t('Download'));
    $this->assertRaw('"Submission ID"|"Submission URI"');

    // Check saved YAML form export (delimiter) settings.
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download', ['export[format][delimiter]' => '.'], t('Save settings'));
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download', [], t('Download'));
    $this->assertRaw('"Submission ID"."Submission URI"');

    // Check delete YAML form export (delimiter) settings.
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download', [], t('Reset settings'));
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download', [], t('Download'));
    $this->assertRaw('"Submission ID"|"Submission URI"');
  }

  /**
   * Request an YAML form results export CSV.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $options
   *   An associative array of export options.
   */
  protected function getExport(YamlFormInterface $yamlform, array $options = []) {
    /** @var \Drupal\yamlform\YamlFormSubmissionExporterInterface $exporter */
    $exporter = \Drupal::service('yamlform_submission.exporter');
    $options += $exporter->getDefaultExportOptions();
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/download', ['query' => $options]);
  }

}
