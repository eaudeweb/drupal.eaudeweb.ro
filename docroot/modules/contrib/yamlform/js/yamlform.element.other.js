/**
 * @file
 * YAML form (select|checkboxes|radios_)other element handler.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Toggle other text field.
   *
   * @param {boolean} show
   *   TRUE will display the text field. FALSE with hide and clear the text field.
   * @param {object} $textField
   *   The text field to be toggled.
   */
  function toggleOther(show, $textField) {
    if (show) {
      $textField.slideDown().find('element').focus();
    }
    else {
      $textField.slideUp();
      $textField.find('element').val('');
    }
  }

  /**
   * Attach handlers to select other elements.
   */
  Drupal.behaviors.yamlFormSelectOther = {
    attach: function (context) {
      $(context).find('.form-type-yamlform-select-other').once().each(function() {
        var $element = $(this);

        var $select = $element.find('.form-type-select');
        var $otherOption = $element.find('option[value="_other_"]');
        var $textField = $element.find('.form-type-textfield');

        if ($otherOption.is(':selected')) {
          $textField.show();
        }

        $select.on('change', function() {
          toggleOther($otherOption.is(':selected'), $textField);
        });
      });
    }
  };

  /**
   * Attach handlers to checkboxes other elements.
   */
  Drupal.behaviors.yamlFormCheckboxesOther = {
    attach: function (context) {
      $(context).find('.form-type-yamlform-checkboxes-other').once().each(function() {
        var $element = $(this);
        var $checkbox = $element.find('input[value="_other_"]');
        var $textField = $element.find('.form-type-textfield');

        if ($checkbox.is(':checked')) {
          $textField.show();
        }

        $checkbox.on('click', function() {
          toggleOther(this.checked, $textField);
        });
      });
    }
  };

  /**
   * Attach handlers to radios other elements.
   */
  Drupal.behaviors.yamlFormRadiosOther = {
    attach: function (context) {
      $(context).find('.form-type-yamlform-radios-other').once().each(function() {
        var $element = $(this);

        var $radios = $element.find('input[type="radio"]');
        var $textField = $element.find('.form-type-textfield');

        if ($radios.filter(':checked').val() === '_other_') {
          $textField.show();
        }

        $radios.on('click', function() {
          toggleOther(($radios.filter(':checked').val() === '_other_'), $textField);
        });
      });
    }
  };

})(jQuery, Drupal);
