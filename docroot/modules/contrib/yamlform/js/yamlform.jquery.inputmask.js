/**
 * @file
 * Javascript behaviors for YAML form jquery.inputmask integration.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.yamlFormElementMask = {
    attach: function (context) {
      $(context).find('input.js-yamlform-element-mask').once('yamlform-element-mask').inputmask();
    }
  };

})(jQuery, Drupal);
