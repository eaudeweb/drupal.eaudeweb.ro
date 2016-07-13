/**
 * @file
 * YAML form UI admin behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Filters the YAML form element list by a text input search string.
   *
   * The text input will have the selector `input.yamlform-ui-filter-text`.
   *
   * The target element to do searching in will be in the selector
   * `input.yamlform-ui-filter-text[data-element]`
   *
   * The text source where the text should be found will have the selector
   * `.yamlform-ui-filter-text-source`
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the YAML form element filtering.
   */
  Drupal.behaviors.yamlformFilterByText = {
    attach: function (context, settings) {
      var $input = $('input.yamlform-ui-filter-text').once('yamlform-ui-filter-text');
      var $table = $($input.attr('data-element'));
      var $filter_rows;

      /**
       * Filters the YAML form element list.
       *
       * @param {jQuery.Event} e
       *   The jQuery event for the keyup event that triggered the filter.
       */
      function filterElementList(e) {
        var query = $(e.target).val().toLowerCase();

        /**
         * Shows or hides the YAML form element entry based on the query.
         *
         * @param {number} index
         *   The index in the loop, as provided by `jQuery.each`
         * @param {HTMLElement} label
         *   The label of the yamlform.
         */
        function toggleBlockEntry(index, label) {
          var $label = $(label);
          var $row = $label.parent().parent();
          var textMatch = $label.text().toLowerCase().indexOf(query) !== -1;
          $row.toggle(textMatch);
        }

        // Filter if the length of the query is at least 2 characters.
        if (query.length >= 2) {
          $filter_rows.each(toggleBlockEntry);
        }
        else {
          $filter_rows.each(function (index) {
            $(this).parent().parent().show();
          });
        }
      }

      if ($table.length) {
        $filter_rows = $table.find('div.yamlform-ui-filter-text-source');
        $input.on('keyup', filterElementList);
      }
    }
  };

}(jQuery, Drupal));
