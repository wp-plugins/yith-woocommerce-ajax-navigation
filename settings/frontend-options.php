<?php

$frontend = array(

    'frontend' => array(

        'header'   => array(

            array( 'type' => 'open' ),

            array(
                'name' => __( 'General Settings', 'yith_wc_ajxnav' ),
                'type' => 'title'
            ),

            array( 'type' => 'close' )
        ),

        'settings' => array(

            array( 'type' => 'open' ),

            array(
                'id'   => 'yith_wcan_frontend_description',
                'name' => _x( 'How To:', 'Admin panel: option description', 'yith_wc_ajxnav' ),
                'type' => 'wcan_description',
                'desc' => _x( "If your theme use the WooCommerce standard templates, you don't need to change the following values.
                                Otherwise, add the classes used in the template of your theme.
                                If you don't know them, please contact the developer of your theme to receive the correct classes.", 'Admin: Panel section description', 'yith_wc_ajxnav' ),
            ),

            array(
                'name' => __( 'Product Container', 'yith_wc_ajxnav' ),
                'desc' => __( 'Put here the CSS class or id for the product container', 'yith_wc_ajxnav' ) . ' (Default: <strong>.products</strong>)',
                'id'   => 'yith_wcan_ajax_shop_container',
                'type' => 'text',
                'std'  => '.products'
            ),

            array(
                'name' => __( 'Shop Pagination Container', 'yith_wc_ajxnav' ),
                'desc' => __( 'Put here the CSS class or id for the shop pagination container', 'yith_wc_ajxnav' ) . ' (Default: <strong>nav.woocommerce-pagination</strong>)',
                'id'   => 'yith_wcan_ajax_shop_pagination',
                'type' => 'text',
                'std'  => 'nav.woocommerce-pagination'
            ),

            array(
                'name' => __( 'Result Count Container', 'yith_wc_ajxnav' ),
                'desc' => __( 'Put here the CSS class or id for the result count container', 'yith_wc_ajxnav' ) . ' (Default: <strong>.woocommerce-result-count</strong>)',
                'id'   => 'yith_wcan_ajax_shop_result_container',
                'type' => 'text',
                'std'  => '.woocommerce-result-count'
            ),

            array( 'type' => 'close' ),
        ),
    )
);

return apply_filters( 'yith_wcan_panel_frontend_options', $frontend );