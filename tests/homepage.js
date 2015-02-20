/**
 * Homepage tests.
 *
 * These tests make some assertions on a Drupal vanilla installation.
 * Feel free to change them to suit your needs.
 */
var yaml = require('js-yaml');
var th = require('./th.js');

casper.test.begin('Tests homepage structure', 3, function suite(test) {
  // Open the homepage.
  var uri = th.projectGetSetting('aliases.local.uri');
  casper.start(uri);
  casper.thenOpen(uri, function() {
    // Check that we get a 200 response code.
    test.assertHttpStatus(200, 'Homepage was loaded successfully.');
    // Check the presence of the main items in the page.
    test.assertExists('a#logo', 'Header link to the homepage is present.');
    test.assertExists('form#user-login-form', 'Login form is present.');
  });
  casper.run(function() { test.done(); });
});
