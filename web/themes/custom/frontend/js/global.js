/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {
    'use strict';
    Drupal.behaviors.bootstrap_barrio_subtheme = {
        attach: function (context, settings) {
          $('header').css('z-index', '2');
            var position = $(window).scrollTop();
            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) {
                    $('body').addClass("scrolled");
                }
                else {
                    $('body').removeClass("scrolled");
                }
                var scroll = $(window).scrollTop();
                if (scroll > position) {
                    $('body').addClass("scrolldown");
                    $('body').removeClass("scrollup");
                }
                else {
                    $('body').addClass("scrollup");
                    $('body').removeClass("scrolldown");
                }
                position = scroll;
            });

          var URLactual = window.location.href;
          var res = URLactual.split('/');
          if(res[3] == 'medir-el-impacto#'){
            $('.node--type-medir-el-impacto .G-tabs li:eq(2) a').click();
          }
          //divs en filtros
          if($('#views-exposed-form-tipos-de-productos-moda .form-row .filter-title').length == 0) {

              $('#views-exposed-form-tipos-de-productos-moda .form-row').append('<div class="filter-title"></div><div class="filter-orden"></div>');
              var filter_title = $('#views-exposed-form-tipos-de-productos-moda fieldset.form-item-title');
              var filter_orden = $('#views-exposed-form-tipos-de-productos-moda fieldset.form-item-sort-by, #views-exposed-form-tipos-de-productos-moda fieldset.form-item-sort-order, #views-exposed-form-tipos-de-productos-moda .form-actions');
              $('.filter-title').append(filter_title);
              $('.filter-orden').append(filter_orden);

          }

          if($('#views-exposed-form-tipos-de-productos-hogar .form-row .filter-title').length == 0) {

            $('#views-exposed-form-tipos-de-productos-hogar .form-row').append('<div class="filter-title"></div><div class="filter-orden"></div>');
            var filter_title_hogar = $('#views-exposed-form-tipos-de-productos-hogar fieldset.form-item-title');
            var filter_orden_hogar = $('#views-exposed-form-tipos-de-productos-hogar fieldset.form-item-sort-by, #views-exposed-form-tipos-de-productos-hogar fieldset.form-item-sort-order, #views-exposed-form-tipos-de-productos-hogar .form-actions');
            $('.filter-title').append(filter_title_hogar);
            $('.filter-orden').append(filter_orden_hogar);

          }

          if($('#views-exposed-form-tipos-de-productos-dia-a-dia .form-row .filter-title').length == 0) {

            $('#views-exposed-form-tipos-de-productos-dia-a-dia .form-row').append('<div class="filter-title"></div><div class="filter-orden"></div>');
            var filter_title_dia = $('#views-exposed-form-tipos-de-productos-dia-a-dia fieldset.form-item-title');
            var filter_orden_dia = $('#views-exposed-form-tipos-de-productos-dia-a-dia fieldset.form-item-sort-by, #views-exposed-form-tipos-de-productos-dia-a-dia fieldset.form-item-sort-order, #views-exposed-form-tipos-de-productos-dia-a-dia .form-actions');
            $('.filter-title').append(filter_title_dia);
            $('.filter-orden').append(filter_orden_dia);

          }
          $('.form-item-politica input').change(function(){
            if($('.form-item-politica input').prop('checked')){
              $('button').removeClass('disabled');
            }else{
              $('button').addClass('disabled');
            }
          })
          var value_boton = $('#commerce-checkout-flow-multistep-default button#edit-actions-next').val();
          if(value_boton == 'Pagar y completar compra'){
            $('#commerce-checkout-flow-multistep-default button#edit-actions-next').addClass('disabled');
          }
          $('#edit-politica').change(function(){
            if($(this).prop('checked')){
              $('#commerce-checkout-flow-multistep-default button#edit-actions-next').removeClass('disabled');
            }else{
              $('#commerce-checkout-flow-multistep-default button#edit-actions-next').addClass('disabled');
            }
          })


          //orden menu superior
          /*var buscador = $('a.E-buscador');
          var usuario = $('a.E-perfil');
          $('#block-cestadelacompra').before(buscador).before(usuario);*/
            $("#views-exposed-form-productos-page-1 #edit-field-categoria-de-producto-target-id .checkbox").each(function (e) {
                if ($(this).siblings('ul').length > 0) {
                    $(this).addClass('collapse-parent');
                }
            });
            $('#views-exposed-form-buscador-page-1 .form-row > div').removeClass('d-none');
            $('#views-exposed-form-buscador-page-1 .form-type-textfield input').keyup(function(e){
              if(e.which == 13) {
                $('#views-exposed-form-buscador-page-1 .form-submit').click();
              }
            });
          $('#views-exposed-form-buscador-page-1 .checkbox input').off().change(function(){
            $('#views-exposed-form-buscador-page-1 .form-submit').click();
          });

          //Acordeon en moda/hogar/dia-a-dia
          $('.contenido-acordeon .checkbox').addClass('collapse');
          $('.contenido-acordeon p').off('mouseup').on('mouseup', function(){
            $(this).parent().find('.checkbox').collapse('toggle');
            if($(this).hasClass('flecha-bottom')){
              $(this).removeClass('flecha-bottom');
              $(this).addClass('flecha-top');
            }else{
              $(this).addClass('flecha-bottom');
              $(this).removeClass('flecha-top');
            }
          });


          // Sumar restar cantidad
          $('#page').on('click', '.num-in span', function(e){
            e.stopImmediatePropagation();
            var $input = $(this).parents('.num-in').find('input');
            if($(this).hasClass('minus')) {
              var count = parseFloat($input.val()) - 1;
              count = count < 1 ? 1 : count;
              if (count < 2) {
                $(this).addClass('dis');
              }
              else {
                $(this).removeClass('dis');
              }
              $input.val(count);
            }
            else {
              var count = parseFloat($input.val()) + 1
              $input.val(count);
              if (count > 1) {
                $(this).parents('.num-block').find(('.minus')).removeClass('dis');
              }
            }

            $input.change();
            if($('.view-commerce-cart-form #edit-submit').length){
              $('.view-commerce-cart-form #edit-submit').trigger('click');
            }
            return false;
          });


          $('.field--type-entity-reference.field--name-purchased-entity .fieldgroup.form-composite[data-drupal-selector="edit-purchased-entity-0-attributes-attribute-talla"]').addClass('E-talla');

          $('.boton-filtros').off('click').click(function(){
            if($('.container-filtros-vista .container-filtros').hasClass('mostrar-filtros')){
              $('.container-filtros-vista .container-filtros').removeClass('mostrar-filtros');
              $('.container-filtros-vista .container-filtros').addClass('ocultar-filtros');
            }else{
              $('.container-filtros-vista .container-filtros').addClass('mostrar-filtros');
              $('.container-filtros-vista .container-filtros').removeClass('ocultar-filtros');
            }

            if($('.boton-filtros').hasClass('boton-filtrar')){
              $('.boton-filtros').removeClass('boton-filtrar');
              $('.boton-filtros').addClass('boton-cerrar');
            }else{
              $('.boton-filtros').addClass('boton-filtrar');
              $('.boton-filtros').removeClass('boton-cerrar');
            }
          });
          $('.commerce-order-item-add-to-cart-form .field--widget-commerce-product-variation-attributes .attribute-widgets.js-form-wrapper.form-group').before($('.commerce-order-item-add-to-cart-form .field--name-quantity'));
         $('.js-form-item-quantity-0-value label').css('display', 'none');

         //Buscador
          $('.tooltip-buscador').parent().parent().find('input').click(function(){
            console.log('pasa');
            console.log($('#edit-title').val());
            if($('#edit-title').val() == 'Buscar...'){
              $('#edit-title').val('');
            }
          });
          //Orden en filtros de moda/hogar/dia-a-dia
          $('#orden-form').css('display', 'none');
          /*$('.view-tipos-de-productos .view-filters').append($('#orden-form'));
          var formulario = $('#orden-form').html();
          $('#edit-container-precio').on('change', function(){
            var precio = $(this).val();
            $('#views-exposed-form-tipos-de-productos-hogar .js-form-item-sort-by select').val('price__number');
            $('#views-exposed-form-tipos-de-productos-hogar .js-form-item-sort-order select').val(precio);
            $('#views-exposed-form-tipos-de-productos-hogar .button').click();
          });
          $('#edit-container-impacto').on('change', function(){
            var impacto = $(this).val();
            $('#views-exposed-form-tipos-de-productos-hogar .js-form-item-sort-by select').val('field_porcentaje_value');
            $('#views-exposed-form-tipos-de-productos-hogar .js-form-item-sort-order select').val(impacto);
            $('#views-exposed-form-tipos-de-productos-hogar .button').click();
          });
          $('#views-exposed-form-tipos-de-productos-hogar .button').on('click', function(){
            setTimeout(function(){
              $('.view-tipos-de-productos .view-filters').append(formulario);
            }, 1000);

          })*/
          setTimeout(function(){
            $('.cargando-productos').css('display', 'none');
          }, 1000);
          $('[data-toggle="tooltip"]').tooltip();
          $(window).on('load', function(){
            $('#edit-container-group-publico-publico > div:eq(0) input').trigger('change');

          });

          $('.filter-orden .js-form-item-sort-order select').change(function(){
            $('#views-exposed-form-tipos-de-productos-moda button').click();
            $('#views-exposed-form-tipos-de-productos-hogar button').click();
            $('#views-exposed-form-tipos-de-productos-dia-a-dia button').click();
          })
        }
    };

    $(document).ready(function () {

        $("#views-exposed-form-productos-page-1 #edit-field-categoria-de-producto-target-id .checkbox").each(function (e) {
            if ($(this).siblings('ul').length > 0) {
                $(this).addClass('collapse-parent');
            }
        });


        $("#views-exposed-form-productos-page-1 #edit-field-categoria-de-producto-target-id .collapse-parent .form-checkbox").bind("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if ($(this).parent().parent().parent().parent().hasClass('active-collapse')) {
                $(this).parent().parent().parent().parent().removeClass("active-collapse");
                $(this).parent().parent().removeClass("li-parent");
            } else {
                $(this).parent().parent().parent().parent().addClass("active-collapse");
                $(this).parent().parent().addClass("li-parent");
            }
        });

        $("#search-fashion-title").bind("click", function (e) {
            e.preventDefault();
            var input = document.getElementById("edit-title");
            input.value = document.getElementById("edit-title-filter").value;
            jQuery('#views-exposed-form-productos-page-1 .form-item-title input').change();
        });

        // COOKIES

      //cookies.remove('grota');


      document.addEventListener('cookiesjsrUserConsent', function (event) {
        var cookies = document.cookie.match("(^|[^;]+)\\s*" + "grota" + "\\s*=\\s*([^;]+)");
        cookies = cookies ? cookies.pop() : "{}";

        if (cookies == "{}") {
          $(window).scrollTop(40);
          $('.overlay').show();
        }
        else {
          let cookieTagManager = false;
          let aux = cookies.split(',');
          for (let i=0;i<aux.length;i++) {
            if (aux[i].search("true") > -1 && aux[i].search("gtag") > -1) {
              cookieTagManager = true;
            }
          }

          if (!cookieTagManager) {
            document.cookie = '_ga_WH6XKCQEZ8' + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
            document.cookie = '_ga' + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
            //window.location.href = '/';
          }

          $('.overlay').hide();
        }
      });



    });


})(jQuery, Drupal);
