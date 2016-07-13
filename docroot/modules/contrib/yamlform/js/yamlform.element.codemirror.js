/**
 * @file
 * Javascript behaviors for YAML form CodeMirror integration.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.yamlFormCodeMirrorYaml = {
    attach: function (context) {

      // YAML form CodeMirror editor.
      $(context).find('textarea.js-yamlform-codemirror').once('yamlform-codemirror').each(function () {

        // Open all closed details, so that editor height is correctly calculated.
        var $details = $(this).parents('details:not([open])');
        $details.attr('open', 'open');

        // #59 HTML5 required attribute breaks hack for form submission.
        // https://github.com/marijnh/CodeMirror-old/issues/59
        $(this).removeAttr('required');

        var editor = CodeMirror.fromTextArea(this, {
          mode: $(this).attr('data-yamlform-codemirror-mode'),
          lineNumbers: true,
          viewportMargin: Infinity,
          readOnly: $(this).prop('readonly') ? true : false,
          // Setting for using spaces instead of tabs - https://github.com/codemirror/CodeMirror/issues/988
          extraKeys: {
            Tab: function (cm) {
              var spaces = Array(cm.getOption('indentUnit') + 1).join(' ');
              cm.replaceSelection(spaces, 'end', '+element');
            }
          }
        });

        // Now, close details.
        $details.removeAttr('open');

        // Issue #2764443: CodeMirror is not setting submitted value when
        // rendered within a YAML form UI dialog.
        editor.on('blur', function(event){
          editor.save();
        });

      });

      // YAML form CodeMirror syntax coloring.
      $(context).find('.js-yamlform-codemirror-runmode').once('yamlform-codemirror-runmode').each(function () {
        // Mode Runner - http://codemirror.net/demo/runmode.html
        CodeMirror.runMode($(this).addClass('cm-s-default').html(), $(this).attr('data-yamlform-codemirror-mode'), this);
      });

    }
  };

  // Workaround: When a dialog opens we need to reference all CodeMirror
  // editors to make sure they are properly sized.
  $(window).on('dialog:aftercreate', function (dialog, $element, settings) {
    $('.CodeMirror').each(function(index, $element){
      var $details = $(this).parents('details:not([open])');
      $details.attr('open', 'open');
      $element.CodeMirror.refresh();
      // Now, close details.
      $details.removeAttr('open');
    });
  });

})(jQuery, Drupal);
