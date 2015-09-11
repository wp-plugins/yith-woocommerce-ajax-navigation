<?php

$custom_style = array(

    'custom-style' => array(

        'header'   => array(

            array( 'type' => 'open' ),

            array(
                'name' => __( 'Custom Style', 'yith_wc_ajxnav' ),
                'type' => 'title'
            ),

            array( 'type' => 'close' )
        ),

        'settings' => array(

            array( 'type' => 'open' ),

            array(
                'name' => __( 'Put here your custom CSS rules:', 'yith_wc_ajxnav' ),
                'desc' => '',
                'id'   => 'yith_wcan_custom_style',
                'type' => 'textarea',
                'std'  => '',
                'custom_attributes' => array(
                    'rows' => 15,
                    'cols'  => 75
                )
            ),

            array( 'type' => 'close' ),
        ),
    )
);

return apply_filters( 'yith_wcan_panel_custom_style_options', $custom_style );