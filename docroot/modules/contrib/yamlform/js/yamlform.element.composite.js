/**
 * @file
 * YAML form composite element handler.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Attach handlers to composite element required.
   */
  Drupal.behaviors.yamlFormCompositeRequired = {
    attach: function (context) {
      $(context).find('#edit-properties-required').once().click(function() {
        // If the main required properties is checked off, check required for
        // all composite elements.
        if (this.checked) {
          $('input[name$="__required]"]').attr('checked', 'checked');
          $('input[name$="__required]"]').attr('readonly', 'readonly');
        }
        else {
          $('input[name$="__required]"]').removeAttr('readonly');
        }
      });
    }
  };

})(jQuery, Drupal);
