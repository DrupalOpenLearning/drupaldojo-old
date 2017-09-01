(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Select/Deselect All.
   *
   * @type {{attach: attach}}
   */
  Drupal.behaviors.TableList = {
    attach: function (context) {
      $('#tablelist_deselecting', context).click(function () {
        $('.exclude_tablelist option:selected').prop('selected', false);
      });
      $('#tablelist_selectall', context).click(function () {
        $('.exclude_tablelist option').prop('selected', true);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
