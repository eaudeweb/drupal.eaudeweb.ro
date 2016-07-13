<?php

/**
 * @file
 * Definition of Drupal\yamlform_node\test\YamlFormNodeResultsTest.
 */

namespace Drupal\yamlform_node\Tests;

use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Tests\YamlFormTestBase;

/**
 * Tests for YAML form node results.
 *
 * @group YamlFormNode
 */
class YamlFormNodeResultsTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test', 'yamlform_node'];

  /**
   * Tests YAML form node results.
   */
  public function testResults() {
    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityManager()->getStorage('yamlform_submission');

    $this->createUsers();

    $yamlform = YamlForm::load('contact');

    // Create node.
    $node = $this->drupalCreateNode(['type' => 'yamlform']);

    // Check access denied to YAML form rsults.
    $this->drupalLogin($this->adminSubmissionUser);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(403);

    // Set Node YAML form to the contact form.
    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->save();

    // Generate 3 node submissions and 3 yamlform submissions.
    $this->drupalLogin($this->normalUser);
    $node_sids = [];
    $yamlform_sids = [];
    for ($i=1; $i <= 3; $i++) {
      $edit = [
        'name' => "node$i",
        'email' => "node$i@example.com",
        'subject' => "Node $i subject",
        'message' => "Node $i message",
      ];
      $this->drupalPostForm('node/' . $node->id(), $edit, t('Send message'));
      $node_sids[$i] = $this->getLastSubmissionId($yamlform);
      $edit = [
        'name' => "yamlform$i",
        'email' => "yamlform$i@example.com",
        'subject' => "YAML form $i subject",
        'message' => "YAML form $i message",
      ];
      $this->drupalPostForm('yamlform/contact', $edit, t('Send message'));
      $yamlform_sids[$i] = $this->getLastSubmissionId($yamlform);
    }

    // Check that 6 submission were created.
    $this->assertEqual($submission_storage->getTotal($yamlform, $node), 3);
    $this->assertEqual($submission_storage->getTotal($yamlform), 6);

    // Check YAML form node results.
    $this->drupalLogin($this->adminSubmissionUser);
    $node_route_parameters = ['node' => $node->id(), 'yamlform_submission' => $node_sids[1]];
    $node_submission_url = Url::fromRoute('entity.node.yamlform_submission.canonical', $node_route_parameters);
    $yamlform_submission_route_parameters = ['yamlform_submission' => $node_sids[1]];
    $yamlform_submission_url = Url::fromRoute('entity.yamlform_submission.canonical', $yamlform_submission_route_parameters);

    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(200);
    $this->assertRaw('<h1 class="page-title">' . $node->label() . '</h1>');
    $this->assertNoRaw('<h1 class="page-title">' . $yamlform->label() . '</h1>');
    $this->assertRaw(('<a href="' . $node_submission_url->toString() . '">' . $node_sids[1] . '</a>'));
    $this->assertNoRaw(('<a href="' . $yamlform_submission_url->toString() . '">' . $yamlform_sids[1] . '</a>'));

    // Check YAML form node title.
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/manage/' . $node_sids[1]);
    $this->assertRaw($node->label() . ': Submission #' . $node_sids[1]);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/manage/' . $node_sids[2]);
    $this->assertRaw($node->label() . ': Submission #' . $node_sids[2]);

    // Check YAML form node navigation.
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/manage/' . $node_sids[1]);
    $node_route_parameters = ['node' => $node->id(), 'yamlform_submission' => $node_sids[2]];
    $node_submission_url = Url::fromRoute('entity.node.yamlform_submission.canonical', $node_route_parameters);
    $this->assertRaw('<a href="' . $node_submission_url->toString() . '" rel="next" title="Go to next page">Next submission <b>›</b></a>');

    // Check YAML form node saved draft.
    $yamlform->setSetting('draft', TRUE);
    $yamlform->save();

    // Check YAML form saved draft.
    $this->drupalLogin($this->normalUser);
    $edit = [
      'name' => "nodeDraft",
      'email' => "nodeDraft@example.com",
      'subject' => "Node draft subject",
      'message' => "Node draft message",
    ];
    $this->drupalPostForm('node/' . $node->id(), $edit, t('Save Draft'));
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('A partially-completed form was found. Please complete the remaining portions.');
    $this->drupalGet('yamlform/contact');
    $this->assertNoRaw('A partially-completed form was found. Please complete the remaining portions.');

    $this->drupalLogout();

    // Create any and own user accounts.
    $any_user = $this->drupalCreateUser([
      'access content',
      'view yamlform node submissions any node',
      'edit yamlform node submissions any node',
      'delete yamlform node submissions any node',
    ]);
    $own_user = $this->drupalCreateUser([
      'access content',
      'view yamlform node submissions own node',
      'edit yamlform node submissions own node',
      'delete yamlform node submissions own node',
    ]);

    // Check accessing results posted to any YAML form node.
    $this->drupalLogin($any_user);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(200);

    // Check accessing results posted to own YAML form node.
    $this->drupalLogin($own_user);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(403);

    $node->setOwnerId($own_user->id())->save();
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(200);

    // Check deleting YAML form node results.
    $this->drupalPostForm('node/' . $node->id() . '/yamlform/results/clear', [], t('Clear'));
    $this->assertEqual($submission_storage->getTotal($yamlform, $node), 0);
    $this->assertEqual($submission_storage->getTotal($yamlform), 3);
  }

}
