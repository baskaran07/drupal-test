(function ($) {

  'use strict';

  function showFieldGroups(checkbox,show) {
    var fieldgroups = checkbox.parent().siblings().filter( function() {
      return /field-group/.test($(this).attr("class"));
    });
    if(show) {
      fieldgroups.show();
    }
    else {
      fieldgroups.hide();
    }
  }

  Drupal.behaviors.entityForm = {
    attach: function (context, settings) {
      $('.cms-content-sync-edit-override',context).each(function() {
        var checkbox = $(this);
        showFieldGroups(checkbox,checkbox.is(':checked'));
      });

      $('.cms-content-sync-edit-override',context).click( function(e) {
        var checkbox  = $(this);
        var id        = checkbox.attr('data-cms-content-sync-edit-override-id');
        var override  = checkbox.is(':checked');
        var elements  = $('.cms-content-sync-edit-override-id-'+id);
        showFieldGroups(checkbox,override);
        if(override) {
          elements.removeClass('cms-content-sync-edit-override-hide');
        }
        else {
          elements.addClass('cms-content-sync-edit-override-hide');
        }
      } );
    }
  };

})(jQuery, drupalSettings);
