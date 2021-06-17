(function ($, Drupal) {
  Drupal.behaviors.proveedorGin = {
    attach: function (context, settings) {
      let vistaTabla = $('.view-content .gin-table-scroll-wrapper');

      if (vistaTabla.length) {
        vistaTabla.addClass('views-form')
      }
    }
  };
})(jQuery, Drupal);
