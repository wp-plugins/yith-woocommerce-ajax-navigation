<?php
/**
 * Functions
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.1.1
 */

if ( !defined( 'YITH_WCAN' ) ) { exit; } // Exit if accessed directly


/**
 * Return a dropdown with Woocommerce attributes
 */
function yith_wcan_dropdown_attributes( $selected, $echo = true ) {
    $attributes = YITH_WCAN_Helper::attribute_taxonomies();
    $options = "";

    foreach( $attributes as $attribute ) {
        $options .= "<option name='{$attribute}'". selected( $attribute, $selected, false ) .">{$attribute}</option>";
    }

    if( $echo ) {
        echo $options;
    } else {
        return $options;
    }
}


/**
 * Print the widgets options already filled
 *
 * @param $type string list|colors|label
 * @param $attribute woocommerce taxonomy
 * @param $id id used in the <input />
 * @param $name base name used in the <input />
 * @param $values array of values (could be empty if this is an ajax call)
 * 
 * @return string
 */
function yith_wcan_attributes_table( $type, $attribute, $id, $name, $values = array(), $echo = true ) {
    $return = '';

    $terms = get_terms( 'pa_' . $attribute, array('hide_empty'=>'0') );

    if( 'list' == $type ) {
        $return = '<input type="hidden" name="'. $name .'[colors]" value="" /><input type="hidden" name="'. $name .'[labels]" value="" />';
    } elseif( 'color' == $type ) {
        if( !empty($terms) ) {
            $return = sprintf('<table><tr><th>%s</th><th>%s</th></tr>', __('Term', 'yit'), __('Color', 'yit'));

            foreach( $terms as $term ) {
                $return .= "<tr><td><label for='{$id}{$term->term_id}'>{$term->name}</label></td><td><input type='text' id='{$id}{$term->term_id}' name='{$name}[colors][{$term->term_id}]' value='" . (isset( $values[$term->term_id] ) ? $values[$term->term_id] : '') . "' size='3' class='yith-colorpicker' /></td></tr>";
            }

            $return .= '</table>';
        }

        $return .= '<input type="hidden" name="'. $name .'[labels]" value="" />';
    } elseif( 'label' == $type ) {
        if( !empty($terms) ) {
            $return = sprintf('<table><tr><th>%s</th><th>%s</th></tr>', __('Term', 'yit'), __('Labels', 'yit'));

            foreach( $terms as $term ) {
                $return .= "<tr><td><label for='{$id}{$term->term_id}'>{$term->name}</label></td><td><input type='text' id='{$id}{$term->term_id}' name='{$name}[labels][{$term->term_id}]' value='" . (isset( $values[$term->term_id] ) ? $values[$term->term_id] : '') . "' size='3' maxlength='3' /></td></tr>";
            }

            $return .= '</table>';
        }

        $return .= '<input type="hidden" name="'. $name .'[colors]" value="" />';
    }

    if( $echo ) {
        echo $return;
    }

    return $return;
}


/**
 * Can the widget be displayed?
 *
 * @return bool
 */
function yith_wcan_can_be_displayed() {
    global $woocommerce, $_attributes_array;


/*    if ( ! is_post_type_archive( 'product' ) && ! is_tax( array_merge( $_attributes_array, array( 'product_cat', 'product_tag' ) ) ) )
        return false;*/

    if ( is_active_widget( false, false, 'yith-woo-ajax-navigation', true ) ) {
        return true;
    } else {
        return false;
    }
}


if( !function_exists('yit_curPageURL') ) {
    /**
     * Retrieve the current complete url
     *
     * @since 1.0
     */
    function yit_curPageURL() {
        $pageURL = 'http';
        if ( isset( $_SERVER["HTTPS"] ) AND $_SERVER["HTTPS"] == "on" )
            $pageURL .= "s";

        $pageURL .= "://";

        if ( isset( $_SERVER["SERVER_PORT"] ) AND $_SERVER["SERVER_PORT"] != "80" )
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        else
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

        return $pageURL;
    }
}