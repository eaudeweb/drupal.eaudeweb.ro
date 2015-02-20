Google Captcha for Drupal
====================

The Google Captcha module uses the reCAPTCHA web service to
improve the CAPTCHA system and protect email addresses. For
more information on what reCAPTCHA is, please visit:
    https://www.google.com/recaptcha

This module is inspired by the reCAPTCHA module, but uses new
Google No Captcha reCaptcha API. I hope this module will be finally
integrated with the original reCAPTCHA module:
    https://www.drupal.org/project/recaptcha


DEPENDENCIES
------------

* Google Captcha module depends on the CAPTCHA module.
  https://drupal.org/project/captcha


CONFIGURATION
-------------

1. Enable Google Captcha and CAPTCHA modules in:
       admin/modules

2. You'll now find a Google Captcha tab in the CAPTCHA
   administration page available at:
       admin/config/people/captcha/google_captcha

3. Register your web site at https://www.google.com/recaptcha/admin/create

4. Input the site and private keys into the Google Captcha settings.

5. Visit the Captcha administration page and set where you
   want the Google Captcha form to be presented:
       admin/config/people/captcha

THANK YOU
---------

 * Thank you goes to the reCAPTCHA team for all their
   help, support and their amazing Captcha solution
       https://www.google.com/recaptcha
