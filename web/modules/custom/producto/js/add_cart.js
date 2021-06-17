(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.addCart = {
    attach: function (context, settings) {
      $(window).on('load', function (){
        let id = drupalSettings.variation;
        if (id) {
          $('.ajax-variation-load-' + id).trigger('change');
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
