<?php
/**
 * Functions
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( ! defined( 'YITH_WCAN' ) ) {
    exit;
} // Exit if accessed directly


/**
 * Return a dropdown with Woocommerce attributes
 */
function yith_wcan_dropdown_attributes( $selected, $echo = true ) {
    $attributes = YITH_WCAN_Helper::attribute_taxonomies();
    $options    = "";

    foreach ( $attributes as $attribute ) {
        $options .= "<option name='{$attribute}'" . selected( $attribute, $selected, false ) . ">{$attribute}</option>";
    }

    if ( $echo ) {
        echo $options;
    }
    else {
        return $options;
    }
}


/**
 * Print the widgets options already filled
 *
 * @param $type      string list|colors|label
 * @param $attribute woocommerce taxonomy
 * @param $id        id used in the <input />
 * @param $name      base name used in the <input />
 * @param $values    array of values (could be empty if this is an ajax call)
 *
 * @return string
 */
function yith_wcan_attributes_table( $type, $attribute, $id, $name, $values = array(), $echo = true ) {
    $return = '';

    $terms = get_terms( 'pa_' . $attribute, array( 'hide_empty' => '0' ) );

    if ( 'list' == $type ) {
        $return = '<input type="hidden" name="' . $name . '[colors]" value="" /><input type="hidden" name="' . $name . '[labels]" value="" />';
    }

    elseif ( 'color' == $type ) {
        if ( ! empty( $terms ) ) {
            $return = sprintf( '<table><tr><th>%s</th><th>%s</th></tr>', __( 'Term', 'yith_wc_ajxnav' ), __( 'Color', 'yith_wc_ajxnav' ) );

            foreach ( $terms as $term ) {
                $return .= "<tr><td><label for='{$id}{$term->term_id}'>{$term->name}</label></td><td><input type='text' id='{$id}{$term->term_id}' name='{$name}[colors][{$term->term_id}]' value='" . ( isset( $values[$term->term_id] ) ? $values[$term->term_id] : '' ) . "' size='3' class='yith-colorpicker' /></td></tr>";
            }

            $return .= '</table>';
        }

        $return .= '<input type="hidden" name="' . $name . '[labels]" value="" />';
    }

    elseif ( 'multicolor' == $type ) {
        if ( ! empty( $terms ) ) {
            $return = sprintf( '<table class="yith-wcan-multicolor"><tr><th>%s</th><th>%s</th><th>%s</th></tr>', __( 'Term', 'yith_wc_ajxnav' ), _x( 'Color 1', 'For multicolor: I.E. white and red T-Shirt', 'yith_wc_ajxnav' ), _x( 'Color 2', 'For multicolor: I.E. white and red T-Shirt', 'yith_wc_ajxnav' ) );

            foreach ( $terms as $term ) {

                $return .= "<tr>";

                $return .= "<td><label for='{$id}{$term->term_id}'>{$term->name}</label></td>";

                $return .= "<td><input type='text' id='{$id}{$term->term_id}_1' name='{$name}[multicolor][{$term->term_id}][]' value='" . ( isset( $values[$term->term_id][0] ) ? $values[$term->term_id][0] : '' ) . "' size='3' class='yith-colorpicker multicolor' /></td>";
                $return .= "<td><input type='text' id='{$id}{$term->term_id}_2' name='{$name}[multicolor][{$term->term_id}][]' value='" . ( isset( $values[$term->term_id][1] ) ? $values[$term->term_id][1] : '' ) . "' size='3' class='yith-colorpicker multicolor' /></td>";

                $return .= '</tr>';
            }

            $return .= '</table>';
        }

        $return .= '<input type="hidden" name="' . $name . '[labels]" value="" />';
    }

    elseif ( 'label' == $type ) {
        if ( ! empty( $terms ) ) {
            $return = sprintf( '<table><tr><th>%s</th><th>%s</th></tr>', __( 'Term', 'yith_wc_ajxnav' ), __( 'Labels', 'yith_wc_ajxnav' ) );

            foreach ( $terms as $term ) {
                $return .= "<tr><td><label for='{$id}{$term->term_id}'>{$term->name}</label></td><td><input type='text' id='{$id}{$term->term_id}' name='{$name}[labels][{$term->term_id}]' value='" . ( isset( $values[$term->term_id] ) ? $values[$term->term_id] : '' ) . "' size='3' /></td></tr>";
            }

            $return .= '</table>';
        }

        $return .= '<input type="hidden" name="' . $name . '[colors]" value="" />';
    }

    if ( $echo ) {
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
    }
    else {
        return false;
    }
}


if ( ! function_exists( 'yit_curPageURL' ) ) {
    /**
     * Retrieve the current complete url
     *
     * @since 1.0
     */
    function yit_curPageURL() {
        $pageURL = 'http';
        if ( isset( $_SERVER["HTTPS"] ) AND $_SERVER["HTTPS"] == "on" ) {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ( isset( $_SERVER["SERVER_PORT"] ) AND $_SERVER["SERVER_PORT"] != "80" ) {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }
        else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }
}

if ( ! function_exists( 'yit_reorder_terms_by_parent' ) ) {
    /**
     * Sort the array of terms associating the child to the parent terms
     *
     * @param $terms mixed|array
     *
     * @return mixed!array
     * @since 1.3.1
     */
    function yit_reorder_terms_by_parent( $terms ) {

        /* Extract Child Terms */
        $child_terms  = array();
        $terms_count  = 0;
        $parent_terms = array();

        foreach ( $terms as $array_key => $term ) {

            if ( $term->parent != 0 ) {

                if ( isset( $child_terms[$term->parent] ) && $child_terms[$term->parent] != null ) {
                    $child_terms[$term->parent] = array_merge( $child_terms[$term->parent], array( $term ) );
                }
                else {
                    $child_terms[$term->parent] = array( $term );
                }

            }
            else {
                $parent_terms[$terms_count] = $term;
            }
            $terms_count ++;
        }

        /* Reorder Therms */
        $terms_count = 0;
        $terms       = array();

        foreach ( $parent_terms as $term ) {

            $terms[$terms_count] = $term;

            /* The term as child */
            if ( array_key_exists( $term->term_id, $child_terms ) ) {

                foreach ( $child_terms[$term->term_id] as $child_term ) {
                    $terms_count ++;
                    $terms[$terms_count] = $child_term;
                }
            }
            $terms_count ++;
        }

        return $terms;
    }
}

if ( ! function_exists( 'yit_get_terms' ) ) {
    /**
     * Get the array of objects terms
     *
     * @param $type A type of term to display
     *
     * @return $terms mixed|array
     *
     * @since  1.3.1
     */
    function yit_get_terms( $case, $taxonomy, $instance = false ) {

        $exclude = apply_filters( 'yith_wcan_exclude_terms', array() );

        switch ( $case ) {

            case 'all':
                $terms = get_terms( $taxonomy, array( 'hide_empty' => true, 'exclude' => $exclude ) );
                break;

            case 'hierarchical':
                $terms = yit_reorder_terms_by_parent( get_terms( $taxonomy, array( 'hide_empty' => true, 'exclude' => $exclude ) ) );
                break;

            case 'parent' :
                $terms = get_terms( $taxonomy, array( 'hide_empty' => true, 'parent' => false, 'exclude' => $exclude ) );
                break;

            default:
                $args = array( 'hide_empty' => true, 'exclude' => $exclude );
                if ( 'parent' == $instance['display'] ) {
                    $args['parent'] = false;
                }

                $terms = get_terms( $taxonomy, $args );

                if ( 'hierarchical' == $instance['display'] ) {
                    $terms = yit_reorder_terms_by_parent( $terms );
                }
                break;
        }

        return $terms;
    }
}

if ( ! function_exists( 'yit_term_is_child' ) ) {
    /**
     * Return true if the term is a child, false otherwise
     *
     * @param $term The term object
     *
     * @return bool
     *
     * @since 1.3.1
     */
    function yit_term_is_child( $term ) {

        return ( isset( $term->parent ) && $term->parent != 0 ) ? true : false;
    }
}

if ( ! function_exists( 'yit_term_is_parent' ) ) {
    /**
     * Return true if the term is a parent, false otherwise
     *
     * @param $term The term object
     *
     * @return bool
     *
     * @since 1.3.1
     */
    function yit_term_is_parent( $term ) {

        return ( isset( $term->parent ) && $term->parent == 0 ) ? true : false;
    }
}

if ( ! function_exists( 'yit_term_has_child' ) ) {
    /**
     * Return true if the term has a child, false otherwise
     *
     * @param $term     The term object
     * @param $taxonomy the taxonomy to search
     *
     * @return bool
     *
     * @since 1.3.1
     */
    function yit_term_has_child( $term, $taxonomy ) {
        global $woocommerce;

        $count       = 0;
        $child_terms = get_terms( $taxonomy, array( 'child_of' => $term->term_id ) );
        foreach ( $child_terms as $term ) {
            $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
            $count += sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );
        }

        return empty( $count ) ? false : true;
    }
}

if ( ! function_exists( 'yith_wcan_get_option' ) ) {
    /**
     * Retreive the plugin option
     *
     * @param mixed|bool $option_name The option name. If false return alla options array
     *
     *
     * @return mixed|array|string The option(s)
     *
     * @since    1.3.1
     */
    function yith_wcan_get_option( $option_name = false, $default = false ) {
        $options = get_option( 'yit_wcan_options' );

        if ( ! $option_name ) {
            return $options;
        }

        return isset( $options[$option_name] ) ? $options[$option_name] : $default;
    }
}

if ( ! function_exists( 'yit_get_filter_args' ) ) {
    /**
     * Retreive the filter query args option
     *
     * @return array The option(s)
     *
     * @since    1.4
     */
    function yit_get_filter_args( $check_price_filter = true ) {
        $filter_value = array();
        $regexs       = array( '/^filter_[a-zA-Z0-9]/', '/^query_type_[a-zA-Z0-9]/', '/product_tag/' );

        /* Support to YITH WooCommerce Brands */
        if ( defined( 'YITH_WCBR_PREMIUM_INIT' ) && YITH_WCBR_PREMIUM_INIT ) {
            $brands_taxonomy = YITH_WCBR::$brands_taxonomy;
            $regexs[]        = "/{$brands_taxonomy}/";
        }

        if ( ! empty( $_GET ) ) {
            foreach ( $regexs as $regex ) {
                foreach ( $_GET as $query_var => $value ) {
                    if ( preg_match( $regex, $query_var ) ) {
                        $filter_value[$query_var] = $value;
                    }
                }
            }
        }

        if ( $check_price_filter ) {
            // WooCommerce Price Filter
            if ( isset( $_GET['min_price'] ) ) {
                $link = $filter_value['min_price'] = $_GET['min_price'];
            }

            if ( isset( $_GET['max_price'] ) ) {
                $link = $filter_value['max_price'] = $_GET['max_price'];
            }
        }

        // WooCommerce In Stock/On Sale filters
        if ( isset( $_GET['instock_filter'] ) ) {
            $link = $filter_value['instock_filter'] = $_GET['instock_filter'];
        }

        if ( isset( $_GET['onsale_filter'] ) ) {
            $link = $filter_value['onsale_filter'] = $_GET['onsale_filter'];
        }

        if ( isset( $_GET['orderby'] ) ) {
            $link = $filter_value['orderby'] = $_GET['orderby'];
        }

        return $filter_value;
    }
}

if ( ! function_exists( 'yit_check_active_price_filter' ) ) {
    /**
     * Check if there is an active price filter
     *
     * @return bool True if the the filter is active, false otherwise
     *
     * @since    1.4
     */
    function yit_check_active_price_filter( $min_price, $max_price ) {
        return isset( $_GET['min_price'] ) && $_GET['min_price'] == $min_price && isset( $_GET['max_price'] ) && $_GET['max_price'] == $max_price;
    }
}

if ( ! function_exists( 'yit_remove_price_filter_query_args' ) ) {
    /**
     * Remove min_price and max_price query args from filters array value
     *
     * @return array The array params
     *
     * @since    1.4
     */
    function yit_remove_price_filter_query_args( $filter_value ) {
        foreach ( array( 'min_price', 'max_price' ) as $remove ) {
            unset( $filter_value[$remove] );
        }

        return $filter_value;
    }
}