(function ($, Drupal) {
  Drupal.behaviors.checkout = {
    attach: function (context, settings) {
      if ($('.checkout-pane-shipping-information').length) {
        $(window).on('load', function () {
          $('.button-recalculate-shipping-onload').mousedown();
        });

        $('.path-checkout').on('change', '.checkout-pane-shipping-information .administrative-area', function(){
          $('.button-recalculate-shipping').mousedown();
        });
      }
     let buttonReview = $('.chechout-review button.form-submit');

      $(window).on('load', function () {
        if (buttonReview.length) {
          buttonReview.click();
        }
      });

    }
  };
})(jQuery, Drupal);
