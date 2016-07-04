<?php

namespace Drupal\corps\Tests;

/**
 * Tests the installation profile.
 *
 * @group corps
 */
class CorpsProfileIntegrityTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'corps';
  protected $strictConfigSchema = FALSE;

  function testTheme() {
    $this->drupalGet('<front>');
    // Check the bootstrap theme has been loaded.
    $this->assertRaw('bootstrap.css');
  }

  function testTaxonomyTags() {
    $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    $this->assertTrue(array_key_exists('tags', $vocabularies), 'Taxonomy "tags" exists');
  }
}
