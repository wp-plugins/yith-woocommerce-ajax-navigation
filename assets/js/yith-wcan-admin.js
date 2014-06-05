/**
 * Admin
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */
jQuery(function($){

    $(document).on('change', '.yith_wcan_type, .yith_wcan_attributes', function(e){
        var t = this,
            container = $(this).parents('.widget-content').find('.yith_wcan_placeholder').html(''),
            spinner = container.next('.spinner').show();



        var display = $(this).parents('.widget-content').find('#yit-wcan-display');

        var data = {
            action: 'yith_wcan_select_type',
            id: $('input[name=widget_id]', $(t).parents('.widget-content')).val(),
            name: $('input[name=widget_name]', $(t).parents('.widget-content')).val(),
            attribute: $('.yith_wcan_attributes', $(t).parents('.widget-content')).val(),
            value: $('.yith_wcan_type', $(t).parents('.widget-content')).val(),
        };

        /* Hierarchical hide/show */
        if( data.value == 'list' || data.value == 'select' ) {
            display.show();
        }else if( data.value == 'label' || data.value == 'color' ){ console.log(data.value);
            display.hide();
        }

        $.post(ajaxurl, data, function(response) {
            spinner.hide();
            container.html( response.content );
            $(document).trigger('yith_colorpicker');
        }, 'json');
    });

    //color-picker
    $(document).on('yith_colorpicker', function(){
        $('.yith-colorpicker').each(function(){
            $(this).wpColorPicker();
        });
    }).trigger('yith_colorpicker');
});