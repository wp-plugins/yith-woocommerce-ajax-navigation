<?php
/**
 * Main class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( ! defined( 'YITH_WCAN' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAN_Navigation_Widget' ) ) {
        /**
         * YITH WooCommerce Ajax Navigation Widget
         *
         * @since 1.0.0
         */
    class YITH_WCAN_Navigation_Widget extends WP_Widget {

        function __construct() {
            $widget_ops  = array( 'classname' => 'yith-woo-ajax-navigation woocommerce widget_layered_nav', 'description' => __( 'Filter the product list without reloading the page', 'yith_wc_ajxnav' ) );
            $control_ops = array( 'width' => 400, 'height' => 350 );
            add_action('wp_ajax_yith_wcan_select_type', array( $this, 'ajax_print_terms') );
            parent::__construct( 'yith-woo-ajax-navigation', __( 'YITH WooCommerce Ajax Product Filter', 'yith_wc_ajxnav' ), $widget_ops, $control_ops );
        }


        function widget( $args, $instance ) {
            global $_chosen_attributes, $woocommerce, $_attributes_array;

            extract( $args );

            $attributes_array = ! empty( $_attributes_array ) ? $_attributes_array : array();

            if ( ! is_post_type_archive( 'product' ) && ! is_tax( array_merge( $attributes_array, apply_filters( 'yith_wcan_product_taxonomy_type', array( 'product_cat', 'product_tag' ) ) ) ) ) {
                return;
            }

            $current_term    = $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->term_id : '';
            $current_tax     = $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->taxonomy : '';
            $title           = apply_filters( 'yith_widget_title_ajax_navigation', ( isset( $instance['title'] ) ? $instance['title'] : '' ), $instance, $this->id_base );
            $query_type      = isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';
            $display_type    = isset( $instance['type'] ) ? $instance['type'] : 'list';
            $is_child_class  = 'yit-wcan-child-terms';
            $is_chosen_class = 'chosen';
            $terms_type_list = ( isset( $instance['display'] ) && ( $display_type == 'list' || $display_type == 'select' ) ) ? $instance['display'] : 'all';

            $instance['attribute'] = empty( $instance['attribute'] ) ? '' : $instance['attribute'];

            /* FIX TO WOOCOMMERCE 2.1 */
            if ( function_exists( 'wc_attribute_taxonomy_name' ) ) {
                $taxonomy = wc_attribute_taxonomy_name( $instance['attribute'] );
            }
            else {
                $taxonomy = $woocommerce->attribute_taxonomy_name( $instance['attribute'] );
            }

            if ( ! taxonomy_exists( $taxonomy ) ) {
                return;
            }

            $taxonomy        = apply_filters( 'yith_wcan_get_terms_params', $taxonomy, $instance, 'taxonomy_name' );
            $terms_type_list = apply_filters( 'yith_wcan_get_terms_params', $terms_type_list, $instance, 'terms_type' );

            $terms = yit_get_terms( $terms_type_list, $taxonomy, $instance );

            if ( count( $terms ) > 0 ) {

                ob_start();

                $found = false;

                echo $before_widget;

                if ( ! empty( $title ) ) {
                    echo  $before_title . apply_filters( 'widget_title', $title ) . $after_title;
                }

                // Force found when option is selected - do not force found on taxonomy attributes
                if ( ! $_attributes_array || ! is_tax( $_attributes_array ) ) {
                    if ( is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
                        $found = true;
                    }
                }

                if ( in_array( $display_type, apply_filters( 'yith_wcan_display_type_list', array( 'list' ) ) ) ) {
                    // List display
                    echo "<ul class='yith-wcan-list yith-wcan'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        //if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                        $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                        set_transient( $transient_name, $_products_in_term );
                        //}

                        $option_is_set = ( isset( $_chosen_attributes[$taxonomy] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) );

                        $term_param = apply_filters( 'yith_wcan_term_param_uri', $term->term_id, $display_type, $term );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term_param ) {
                                continue;
                            }

                            if ( $count > 0 && $current_term !== $term_param ) {
                                $found = true;
                            }

                            if ( ( $terms_type_list != 'hierarchical' || ! yit_term_has_child( $term, $taxonomy ) ) && $count == 0 && ! $option_is_set ) {
                                continue;
                            }

                            // If this is an OR query, show all options so search can be expanded
                        }
                        else {

                            // skip the term for the current archive
                            if ( $current_term == $term_param ) {
                                continue;
                            }

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 ) {
                                $found = true;
                            }

                        }

                        $arg = apply_filters( 'yith_wcan_list_type_query_arg', 'filter_' . sanitize_title( $instance['attribute'] ), $display_type, $term );

                        $current_filter = ( isset( $_GET[$arg] ) ) ? explode(apply_filters( 'yith_wcan_list_filter_operator', ',', $display_type ), apply_filters( "yith_wcan_list_filter_query_{$arg}", $_GET[$arg] ) ) : array();

                        if ( ! is_array( $current_filter ) ) {
                            $current_filter = array();
                        }

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term_param, $current_filter ) ) {
                            $current_filter[] = $term_param;
                        }

                        if ( defined( 'SHOP_IS_ON_FRONT' ) || is_shop() ) {
                            $link = get_post_type_archive_link( 'product' );
                        }

                        elseif( is_product_category() ){
                            $link = get_term_link( get_queried_object()->slug, 'product_cat' );
                        }

                        else {
                            $link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
                        }

                        $link = untrailingslashit( $link );

                        // All current filters
                        if ( $_chosen_attributes ) {
                            foreach ( $_chosen_attributes as $name => $data ) {
                                if ( $name !== $taxonomy ) {

                                    // Exclude query arg for current term archive term
                                    while ( in_array( $current_term, $data['terms'] ) ) {
                                        $key = array_search( $current_term, $data );
                                        unset( $data['terms'][$key] );
                                    }

                                    // Remove pa_ and sanitize
                                    $filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );

                                    if ( ! empty( $data['terms'] ) ) {
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
                                    }

                                    if ( $data['query_type'] == 'or' ) {
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                    }
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) ) {
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );
                        }

                        if ( isset( $_GET['max_price'] ) ) {
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );
                        }

                        if ( isset( $_GET['product_tag'] ) && $display_type != 'tags' ) {
                            $link = add_query_arg( 'product_tag', urlencode( $_GET['product_tag'] ), $link );
                        }

                        $check_for_current_widget = isset( $_chosen_attributes[$taxonomy] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] );

                        // Current Filter = this widget
                        if ( apply_filters( 'yith_wcan_list_type_current_widget_check', $check_for_current_widget, $current_filter, $display_type, $term_param ) ) {
                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_chosen_class}  {$is_child_class}'" : "class='{$is_chosen_class}'";

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term_param ) );
                                $link                        = add_query_arg( $arg, implode( apply_filters( 'yith_wcan_list_filter_operator', ',', $display_type ), $current_filter_without_this ), $link );
                            }
                        }

                        else {
                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_child_class}'" : '';
                            $link  = add_query_arg( $arg, implode( apply_filters( 'yith_wcan_list_filter_operator', ',', $display_type ), $current_filter ), $link );
                        }

                        // Search Arg
                        if ( get_search_query() ) {
                            $link = add_query_arg( 's', get_search_query(), $link );
                        }

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) ) {
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );
                        }

                        $is_attribute = apply_filters( 'yith_wcan_is_attribute_check', true );

                        // Query type Arg
                        if ( $is_attribute && $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) ) {
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );
                        }


                        echo '<li ' . $class . '>';

                        echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';

                        echo $term->name;

                        echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

                        if ( $count != 0 && apply_filters( "{$args['widget_id']}-show_product_count", true, $instance ) ) {
                            echo ' <small class="count">' . $count . '</small><div class="clear"></div></li>';
                        }
                    }

                    echo "</ul>";

                }
                elseif ( $display_type == 'select' ) {
                    $dropdown_label = __( 'Filters:', 'yith_wc_ajxnav' );
                    ?>

                    <a class="yit-wcan-select-open" href="#"><?php echo apply_filters( 'yith_wcan_dropdown_default_label', $dropdown_label ) ?></a>

                    <?php
                    // Select display
                    echo "<div class='yith-wcan-select-wrapper'>";

                    echo "<ul class='yith-wcan-select yith-wcan'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        //if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                        $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                        set_transient( $transient_name, $_products_in_term );
                        //}

                        $option_is_set = ( isset( $_chosen_attributes[$taxonomy] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id ) {
                                continue;
                            }

                            if ( $count > 0 && $current_term !== $term->term_id ) {
                                $found = true;
                            }

                            if ( ( $terms_type_list != 'hierarchical' || ! yit_term_has_child( $term, $taxonomy ) ) && $count == 0 && ! $option_is_set ) {
                                continue;
                            }

                            // If this is an OR query, show all options so search can be expanded
                        }
                        else {

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id ) {
                                continue;
                            }

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 ) {
                                $found = true;
                            }

                        }

                        $arg = 'filter_' . urldecode( sanitize_title( $instance['attribute'] ) );

                        $current_filter = ( isset( $_GET[$arg] ) ) ? explode( ',', $_GET[$arg] ) : array();

                        if ( ! is_array( $current_filter ) ) {
                            $current_filter = array();
                        }

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term->term_id, $current_filter ) ) {
                            $current_filter[] = $term->term_id;
                        }

                        if ( defined( 'SHOP_IS_ON_FRONT' ) || is_shop() ) {
                            $link = get_post_type_archive_link( 'product' );
                        }

                        elseif( is_product_category() ){
                            $link = get_term_link( get_queried_object()->slug, 'product_cat' );
                        }

                        else {
                            $link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
                        }

                        $link = untrailingslashit( $link );

                        // All current filters
                        if ( $_chosen_attributes ) {
                            foreach ( $_chosen_attributes as $name => $data ) {
                                if ( $name !== $taxonomy ) {

                                    // Exclude query arg for current term archive term
                                    while ( in_array( $current_term, $data['terms'] ) ) {
                                        $key = array_search( $current_term, $data );
                                        unset( $data['terms'][$key] );
                                    }

                                    // Remove pa_ and sanitize
                                    $filter_name = urldecode( sanitize_title( str_replace( 'pa_', '', $name ) ) );

                                    if ( ! empty( $data['terms'] ) ) {
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
                                    }

                                    if ( $data['query_type'] == 'or' ) {
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                    }
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) ) {
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );
                        }

                        if ( isset( $_GET['max_price'] ) ) {
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );
                        }

                        if ( isset( $_GET['product_tag'] ) ) {
                            $link = add_query_arg( 'product_tag', urlencode( $_GET['product_tag'] ), $link );
                        }

                        // Current Filter = this widget
                        if ( isset( $_chosen_attributes[$taxonomy] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) {

                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_chosen_class}  {$is_child_class}'" : "class='{$is_chosen_class}'";

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
                                $link                        = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
                            }

                        }
                        else {

                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_child_class}'" : '';
                            $link  = add_query_arg( $arg, implode( ',', $current_filter ), $link );

                        }

                        // Search Arg
                        if ( get_search_query() ) {
                            $link = add_query_arg( 's', get_search_query(), $link );
                        }

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) ) {
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );
                        }

                        // Query type Arg
                        if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) ) {
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );
                        }

                        echo '<li ' . $class . '>';

                        echo ( $count > 0 || $option_is_set ) ? '<a data-type="select" href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';

                        echo $term->name;

                        echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

                        echo '</li>';

                    }

                    echo "</ul>";

                    echo "</div>";

                }
                elseif ( $display_type == 'color' ) {
                    // List display
                    echo "<ul class='yith-wcan-color yith-wcan yith-wcan-group'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        //if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                        $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                        set_transient( $transient_name, $_products_in_term );
                        //}

                        $option_is_set = ( isset( $_chosen_attributes[$taxonomy] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id ) {
                                continue;
                            }

                            if ( $count > 0 && $current_term !== $term->term_id ) {
                                $found = true;
                            }

                            if ( $count == 0 && ! $option_is_set ) {
                                continue;
                            }

                            // If this is an OR query, show all options so search can be expanded
                        }
                        else {

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id ) {
                                continue;
                            }

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 ) {
                                $found = true;
                            }

                        }

                        $arg = 'filter_' . sanitize_title( $instance['attribute'] );

                        $current_filter = ( isset( $_GET[$arg] ) ) ? explode( ',', $_GET[$arg] ) : array();

                        if ( ! is_array( $current_filter ) ) {
                            $current_filter = array();
                        }

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term->term_id, $current_filter ) ) {
                            $current_filter[] = $term->term_id;
                        }

                        if ( defined( 'SHOP_IS_ON_FRONT' ) || is_shop() ) {
                            $link = get_post_type_archive_link( 'product' );
                        }

                        elseif( is_product_category() ){
                            $link = get_term_link( get_queried_object()->slug, 'product_cat' );
                        }

                        else {
                            $link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
                        }

                        $link = untrailingslashit( $link );

                        // All current filters
                        if ( $_chosen_attributes ) {
                            foreach ( $_chosen_attributes as $name => $data ) {
                                if ( $name !== $taxonomy ) {

                                    // Exclude query arg for current term archive term
                                    while ( in_array( $current_term, $data['terms'] ) ) {
                                        $key = array_search( $current_term, $data );
                                        unset( $data['terms'][$key] );
                                    }

                                    // Remove pa_ and sanitize
                                    $filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );

                                    if ( ! empty( $data['terms'] ) ) {
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
                                    }

                                    if ( $data['query_type'] == 'or' ) {
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                    }
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) ) {
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );
                        }

                        if ( isset( $_GET['max_price'] ) ) {
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );
                        }

                        if ( isset( $_GET['product_tag'] ) ) {
                            $link = add_query_arg( 'product_tag', urlencode( $_GET['product_tag'] ), $link );
                        }

                        // Current Filter = this widget
                        if ( isset( $_chosen_attributes[$taxonomy] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) {

                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_chosen_class}  {$is_child_class}'" : "class='{$is_chosen_class}'";

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
                                $link                        = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
                            }
                        }
                        else {
                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_child_class}'" : '';
                            $link  = add_query_arg( $arg, implode( ',', $current_filter ), $link );
                        }

                        // Search Arg
                        if ( get_search_query() ) {
                            $link = add_query_arg( 's', get_search_query(), $link );
                        }

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) ) {
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );
                        }

                        // Query type Arg
                        if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) ) {
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );
                        }

                        $term_id = yit_wcan_localize_terms( $term->term_id, $taxonomy );

                        if ( ! empty( $instance['colors'][$term_id] ) ) {
                            $li_style = apply_filters( "{$args['widget_id']}-li_style", 'background-color:' . $instance['colors'][$term_id] . ';', $instance );

                            echo '<li ' . $class . '>';

                            echo ( $count > 0 || $option_is_set ) ? '<a style="' . $li_style . '" href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '" title="' . $term->name . '" >' : '<span style="background-color:' . $instance['colors'][$term_id] . ';" >';

                            echo $term->name;

                            echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';
                        }
                    }

                    echo "</ul>";

                }
                elseif ( $display_type == 'label' ) {
                    // List display
                    echo "<ul class='yith-wcan-label yith-wcan yith-wcan-group'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        //if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                        $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                        set_transient( $transient_name, $_products_in_term );
                        //}

                        $option_is_set = ( isset( $_chosen_attributes[$taxonomy] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id ) {
                                continue;
                            }

                            if ( $count > 0 && $current_term !== $term->term_id ) {
                                $found = true;
                            }

                            if ( $count == 0 && ! $option_is_set ) {
                                continue;
                            }

                            // If this is an OR query, show all options so search can be expanded
                        }
                        else {

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id ) {
                                continue;
                            }

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 ) {
                                $found = true;
                            }

                        }

                        $arg = 'filter_' . sanitize_title( $instance['attribute'] );

                        $current_filter = ( isset( $_GET[$arg] ) ) ? explode( ',', $_GET[$arg] ) : array();

                        if ( ! is_array( $current_filter ) ) {
                            $current_filter = array();
                        }

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term->term_id, $current_filter ) ) {
                            $current_filter[] = $term->term_id;
                        }

                        if ( defined( 'SHOP_IS_ON_FRONT' ) || is_shop() ) {
                            $link = get_post_type_archive_link( 'product' );
                        }

                        elseif( is_product_category() ){
                            $link = get_term_link( get_queried_object()->slug, 'product_cat' );
                        }

                        else {
                            $link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
                        }

                        $link = untrailingslashit( $link );

                        // All current filters
                        if ( $_chosen_attributes ) {
                            foreach ( $_chosen_attributes as $name => $data ) {
                                if ( $name !== $taxonomy ) {

                                    // Exclude query arg for current term archive term
                                    while ( in_array( $current_term, $data['terms'] ) ) {
                                        $key = array_search( $current_term, $data );
                                        unset( $data['terms'][$key] );
                                    }

                                    // Remove pa_ and sanitize
                                    $filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );

                                    if ( ! empty( $data['terms'] ) ) {
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
                                    }

                                    if ( $data['query_type'] == 'or' ) {
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                    }
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) ) {
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );
                        }

                        if ( isset( $_GET['max_price'] ) ) {
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );
                        }

                        if ( isset( $_GET['product_tag'] ) ) {
                            $link = add_query_arg( 'product_tag', urlencode( $_GET['product_tag'] ), $link );
                        }

                        // Current Filter = this widget
                        if ( isset( $_chosen_attributes[$taxonomy] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) {

                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_chosen_class}  {$is_child_class}'" : "class='{$is_chosen_class}'";

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
                                $link                        = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
                            }

                        }
                        else {

                            $class = ( $terms_type_list == 'hierarchical' && yit_term_is_child( $term ) ) ? "class='{$is_child_class}'" : '';
                            $link  = add_query_arg( $arg, implode( ',', $current_filter ), $link );

                        }

                        // Search Arg
                        if ( get_search_query() ) {
                            $link = add_query_arg( 's', get_search_query(), $link );
                        }

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) ) {
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );
                        }

                        // Query type Arg
                        if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array( $_chosen_attributes[$taxonomy]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[$taxonomy]['terms'] ) ) ) {
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );
                        }

                        $term_id = yit_wcan_localize_terms( $term->term_id, $taxonomy );

                        if ( $instance['labels'][$term_id] != '' ) {

                            echo '<li ' . $class . '>';

                            echo ( $count > 0 || $option_is_set ) ? '<a title="' . $term->name . '" href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';

                            echo $instance['labels'][$term_id];

                            echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';
                        }
                    }
                    echo "</ul>";

                }
                else {
                    do_action( "yith_wcan_widget_display_{$display_type}", $args, $instance, $display_type, $terms, $taxonomy );
                }
                // End display type conditional

                echo $after_widget;

                if ( ! apply_filters( 'yith_wcan_found_taxonomy', $found ) ) {
                    ob_end_clean();
                    echo substr( $before_widget, 0, strlen( $before_widget ) - 1 ) . ' style="display:none">' . $after_widget;
                }
                else {
                    echo ob_get_clean();
                }
            }
        }

        function form( $instance ) {
            global $woocommerce;

            $defaults = array(
                'title'      => '',
                'attribute'  => '',
                'query_type' => 'and',
                'type'       => 'list',
                'colors'     => '',
                'multicolor' => array(),
                'labels'     => '',
                'display'    => 'all'
            );

            $instance = wp_parse_args( (array) $instance, $defaults );

            $widget_types = apply_filters( 'yith_wcan_widget_types', array(
                    'list'   => __( 'List', 'yith_wc_ajxnav' ),
                    'color'  => __( 'Color', 'yith_wc_ajxnav' ),
                    'label'  => __( 'Label', 'yith_wc_ajxnav' ),
                    'select' => __( 'Dropdown', 'yith_wc_ajxnav' )
                )
            );
            ?>

            <p>
                <label>
                    <strong><?php _e( 'Title', 'yith_wc_ajxnav' ) ?>:</strong><br />
                    <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
                </label>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'type' ); ?>"><strong><?php _e( 'Type:', 'yith_wc_ajxnav' ) ?></strong></label>
                <select class="yith_wcan_type widefat" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
                    <?php foreach ( $widget_types as $type => $label ) : ?>
                        <option value="<?php echo $type ?>" <?php selected( $type, $instance['type'] ) ?>><?php echo $label ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <?php do_action( 'yith_wcan_after_widget_type' );  ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'query_type' ); ?>"><?php _e( 'Query Type:', 'yith_wc_ajxnav' ) ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'query_type' ) ); ?>">
                    <option value="and" <?php selected( $instance['query_type'], 'and' ); ?>><?php _e( 'AND', 'yith_wc_ajxnav' ); ?></option>
                    <option value="or" <?php selected( $instance['query_type'], 'or' ); ?>><?php _e( 'OR', 'yith_wc_ajxnav' ); ?></option>
                </select></p>

            <p class="yith-wcan-attribute-list" style="display: <?php echo $instance['type'] == 'tags' || $instance['type'] == 'brands' ? 'none' : 'block' ?>;">
                <label for="<?php echo $this->get_field_id( 'attribute' ); ?>"><strong><?php _e( 'Attribute:', 'yith_wc_ajxnav' ) ?></strong></label>
                <select class="yith_wcan_attributes widefat" id="<?php echo esc_attr( $this->get_field_id( 'attribute' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attribute' ) ); ?>">
                    <?php yith_wcan_dropdown_attributes( $instance['attribute'] ); ?>
                </select>
            </p>

            <p id="yit-wcan-display" class="yit-wcan-display-<?php echo $instance['type'] ?>">
                <label for="<?php echo $this->get_field_id( 'display' ); ?>"><strong><?php _e( 'Display (default All):', 'yith_wc_ajxnav' ) ?></strong></label>
                <select class="yith_wcan_type widefat" id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>">
                    <option value="all"          <?php selected( 'all', $instance['display'] ) ?>>          <?php _e( 'All (no hierarchical)', 'yith_wc_ajxnav' ) ?></option>
                    <option value="hierarchical" <?php selected( 'hierarchical', $instance['display'] ) ?>> <?php _e( 'All (hierarchical)', 'yith_wc_ajxnav' ) ?>   </option>
                    <option value="parent"       <?php selected( 'parent', $instance['display'] ) ?>>       <?php _e( 'Only Parent', 'yith_wc_ajxnav' ) ?>        </option>
                </select>
            </p>

            <div class="yith_wcan_placeholder">
                <?php
                $values = array();

                if ( $instance['type'] == 'color' ) {
                    $values = $instance['colors'];
                }

                if ( $instance['type'] == 'multicolor' ) {
                    $values = $instance['multicolor'];
                }

                elseif ( $instance['type'] == 'label' ) {
                    $values = $instance['labels'];
                }

                yith_wcan_attributes_table(
                    $instance['type'],
                    $instance['attribute'],
                    'widget-' . $this->id . '-',
                    'widget-' . $this->id_base . '[' . $this->number . ']',
                    $values,
                    $instance['display']
                );
                ?>
            </div>
            <span class="spinner" style="display: none;"></span>

        <input type="hidden" name="widget_id" value="widget-<?php echo $this->id ?>-" />
        <input type="hidden" name="widget_name" value="widget-<?php echo $this->id_base ?>[<?php echo $this->number ?>]" />

            <script>jQuery(document).trigger('yith_colorpicker');</script>
        <?php
        }

        function update( $new_instance, $old_instance ) {
            global $woocommerce;

            $instance               = $old_instance;
            $instance['title']      = strip_tags( $new_instance['title'] );
            $instance['attribute']  = stripslashes( $new_instance['attribute'] );
            $instance['query_type'] = stripslashes( $new_instance['query_type'] );
            $instance['type']       = stripslashes( $new_instance['type'] );
            $instance['colors']     = $new_instance['colors'];
            $instance['multicolor'] = $new_instance['multicolor'];
            $instance['labels']     = $new_instance['labels'];
            $instance['display']    = $new_instance['display'];

            return $instance;
        }

        /**
         * Print terms for the element selected
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
        public function ajax_print_terms() {
            $type      = $_POST['value'];
            $attribute = $_POST['attribute'];
            $return    = array( 'message' => '', 'content' => $_POST );

            $terms = get_terms( 'pa_' . $attribute, array( 'hide_empty' => '0' ) );

            $settings        = $this->get_settings();
            $widget_settings = $settings[ $this->number ];
            $value           = '';

            if( 'label' == $type ){
                $value = $widget_settings['labels'];
            }

            elseif( 'color' == $type ){
                $value = $widget_settings['colors'];
            }

            elseif( 'multicolor' == $type ) {
                $value = $widget_settings['multicolor'];
            }

            if ( $type ) {
                $return['content'] = yith_wcan_attributes_table(
                    $type,
                    $attribute,
                    $_POST['id'],
                    $_POST['name'],
                    $value,
                    false
                );
            }


            echo json_encode( $return );
            die();
        }
    }
}