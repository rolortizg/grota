(function ($, Drupal) {
  Drupal.behaviors.admin_web_gin = {
    attach: function (context, settings) {
      $(".gin--classic-toolbar ul.toolbar-menu.root li a").each(function () {
        if ($(this).text() === 'Vista general' || $(this).text().indexOf('Inicio') !== -1) {
          $(this).parent().remove();
        }
      })
    }
  };
})(jQuery, Drupal);
