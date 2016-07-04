<?php

/**
 * @file
 * Contains \Drupal\piwik\Tests\PiwikStatusMessagesTest.
 */

namespace Drupal\piwik\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test status messages functionality of Piwik module.
 *
 * @group Piwik
 */
class PiwikStatusMessagesTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['piwik'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $permissions = [
      'access administration pages',
      'administer piwik',
    ];

    // User to set up piwik.
    $this->admin_user = $this->drupalCreateUser($permissions);
  }

  /**
   * Tests if status messages tracking is properly added to the page.
   */
  public function testPiwikStatusMessages() {
    $site_id = '1';
    $this->config('piwik.settings')->set('site_id', $site_id)->save();
    $this->config('piwik.settings')->set('url_http', 'http://www.example.com/piwik/')->save();
    $this->config('piwik.settings')->set('url_https', 'https://www.example.com/piwik/')->save();

    // Enable logging of errors only.
    $this->config('piwik.settings')->set('track.messages', ['error' => 'error'])->save();

    $this->drupalPostForm('user/login', [], t('Log in'));
    $this->assertRaw('_paq.push(["trackEvent", "Messages", "Error message", "Username field is required."]);', '[testPiwikStatusMessages]: trackEvent "Username field is required." is shown.');
    $this->assertRaw('_paq.push(["trackEvent", "Messages", "Error message", "Password field is required."]);', '[testPiwikStatusMessages]: trackEvent "Password field is required." is shown.');

    // @todo: investigate why drupal_set_message() fails.
    //drupal_set_message('Example status message.', 'status');
    //drupal_set_message('Example warning message.', 'warning');
    //drupal_set_message('Example error message.', 'error');
    //drupal_set_message('Example error <em>message</em> with html tags and <a href="http://www.example.com/">link</a>.', 'error');
    //$this->drupalGet('');
    //$this->assertNoRaw('_paq.push(["trackEvent", "Messages", "Status message", "Example status message."]);', '[testPiwikStatusMessages]: Example status message is not enabled for tracking.');
    //$this->assertNoRaw('_paq.push(["trackEvent", "Messages", "Warning message", "Example warning message."]);', '[testPiwikStatusMessages]: Example warning message is not enabled for tracking.');
    //$this->assertRaw('_paq.push(["trackEvent", "Messages", "Error message", "Example error message."]);', '[testPiwikStatusMessages]: Example error message is shown.');
    //$this->assertRaw('_paq.push(["trackEvent", "Messages", "Error message", "Example error message with html tags and link."]);', '[testPiwikStatusMessages]: HTML has been stripped successful from Example error message with html tags and link.');
  }

}
