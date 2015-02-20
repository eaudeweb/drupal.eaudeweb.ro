# Testing frameworks

Here are tests that are outside the DrupalWebTestCase (like Selenium tests). There are provided some example tests:

1. homepage.js - tests for successful loading of Drupal homepage. Tu run this test you need to install these dependencies:
  * Install ``nodejs`` - 0.12.0 (brew install nodejs), with ``npm``
  * Install dependencies: ``npm -g install fs js-yaml casperjs phantomjs``
  * Run with ``casperjs test homepage.js``
