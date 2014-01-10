<?php
/**
 * Main class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.1.2
 */

if ( !defined( 'YITH_WCAN' ) ) { exit; } // Exit if accessed directly

if( !class_exists( 'YITH_WCAN' ) ) {
    /**
     * YITH WooCommerce Ajax Navigation Widget
     *
     * @since 1.0.0
     */
    class YITH_WCAN_Navigation_Widget extends WP_Widget {

        function __construct() {
            $widget_ops = array('classname' => 'yith-woo-ajax-navigation woocommerce widget_layered_nav', 'description' => __( 'Narrow down the products list without reloading the page', 'yit') );
            $control_ops = array('width' => 400, 'height' => 350);
            parent::__construct('yith-woo-ajax-navigation', __('YITH WooCommerce Ajax Navigation', 'yit'), $widget_ops, $control_ops);
        }


        function widget( $args, $instance ) {
            global $_chosen_attributes, $woocommerce, $_attributes_array;

            extract( $args );

            if ( ! is_post_type_archive( 'product' ) && ! is_tax( array_merge( $_attributes_array, array( 'product_cat', 'product_tag' ) ) ) )
                return;

            $current_term 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->term_id : '';
            $current_tax 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->taxonomy : '';

            $title 			= apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
            $taxonomy 		= $woocommerce->attribute_taxonomy_name($instance['attribute']);
            $query_type 	= isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';
            $display_type 	= isset( $instance['type'] ) ? $instance['type'] : 'list';

            if ( ! taxonomy_exists( $taxonomy ) )
                return;

            $terms = get_terms( $taxonomy, array( 'hide_empty' => '1' ) );

            if ( count( $terms ) > 0 ) {

                ob_start();

                $found = false;

                echo $before_widget . $before_title . $title . $after_title;

                // Force found when option is selected - do not force found on taxonomy attributes
                if ( ! $_attributes_array || ! is_tax( $_attributes_array ) )
                    if ( is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) )
                        $found = true;

                if ( $display_type == 'list' ) {
                    // List display
                    echo "<ul class='yith-wcan-list yith-wcan'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                            $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                            set_transient( $transient_name, $_products_in_term );
                        }

                        $option_is_set = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id )
                                continue;

                            if ( $count > 0 && $current_term !== $term->term_id )
                                $found = true;

                            if ( $count == 0 && ! $option_is_set )
                                continue;

                        // If this is an OR query, show all options so search can be expanded
                        } else {

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id )
                                continue;

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 )
                                $found = true;

                        }

                        $arg = 'filter_' . sanitize_title( $instance['attribute'] );

                        $current_filter = ( isset( $_GET[ $arg ] ) ) ? explode( ',', $_GET[ $arg ] ) : array();

                        if ( ! is_array( $current_filter ) )
                            $current_filter = array();

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term->term_id, $current_filter ) )
                            $current_filter[] = $term->term_id;

                        // Base Link decided by current page
                        if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
                            $link = home_url();
                        } elseif ( is_post_type_archive( 'product' ) || is_page( woocommerce_get_page_id('shop') ) ) {
                            $link = get_post_type_archive_link( 'product' );
                        } else {
                            $link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
                        }

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

                                    if ( ! empty( $data['terms'] ) )
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );

                                    if ( $data['query_type'] == 'or' )
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) )
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );

                        if ( isset( $_GET['max_price'] ) )
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );

                        // Current Filter = this widget
                        if ( isset( $_chosen_attributes[ $taxonomy ] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) {

                            $class = 'class="chosen"';

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
                                $link = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
                            }

                        } else {

                            $class = '';
                            $link = add_query_arg( $arg, implode( ',', $current_filter ), $link );

                        }

                        // Search Arg
                        if ( get_search_query() )
                            $link = add_query_arg( 's', get_search_query(), $link );

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) )
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );

                        // Query type Arg
                        if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[ $taxonomy ]['terms'] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) )
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );

                        echo '<li ' . $class . '>';

                        echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';

                        echo $term->name;

                        echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

                        echo ' <small class="count">' . $count . '</small></li>';

                    }

                    echo "</ul>";

                } elseif ( $display_type == 'color' ) {
                    // List display
                    echo "<ul class='yith-wcan-color yith-wcan yith-wcan-group'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                            $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                            set_transient( $transient_name, $_products_in_term );
                        }

                        $option_is_set = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id )
                                continue;

                            if ( $count > 0 && $current_term !== $term->term_id )
                                $found = true;

                            if ( $count == 0 && ! $option_is_set )
                                continue;

                            // If this is an OR query, show all options so search can be expanded
                        } else {

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id )
                                continue;

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 )
                                $found = true;

                        }

                        $arg = 'filter_' . sanitize_title( $instance['attribute'] );

                        $current_filter = ( isset( $_GET[ $arg ] ) ) ? explode( ',', $_GET[ $arg ] ) : array();

                        if ( ! is_array( $current_filter ) )
                            $current_filter = array();

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term->term_id, $current_filter ) )
                            $current_filter[] = $term->term_id;

                        // Base Link decided by current page
                        if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
                            $link = home_url();
                        } elseif ( is_post_type_archive( 'product' ) || is_page( woocommerce_get_page_id('shop') ) ) {
                            $link = get_post_type_archive_link( 'product' );
                        } else {
                            $link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
                        }

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

                                    if ( ! empty( $data['terms'] ) )
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );

                                    if ( $data['query_type'] == 'or' )
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) )
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );

                        if ( isset( $_GET['max_price'] ) )
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );

                        // Current Filter = this widget
                        if ( isset( $_chosen_attributes[ $taxonomy ] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) {

                            $class = 'class="chosen"';

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
                                $link = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
                            }

                        } else {

                            $class = '';
                            $link = add_query_arg( $arg, implode( ',', $current_filter ), $link );

                        }

                        // Search Arg
                        if ( get_search_query() )
                            $link = add_query_arg( 's', get_search_query(), $link );

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) )
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );

                        // Query type Arg
                        if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[ $taxonomy ]['terms'] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) )
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );

                        echo '<li ' . $class . '>';

                        echo ( $count > 0 || $option_is_set ) ? '<a style="background-color:' . $instance['colors'][$term->term_id] . ';" href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '" title="' . $term->name . '" >' : '<span style="background-color:' . $instance['colors'][$term->term_id] . ';" >';

                        echo $term->name;

                        echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

                        //echo ' <small class="count">' . $count . '</small></li>';

                    }

                    echo "</ul>";

                } elseif ( $display_type == 'label' ) {
                    // List display
                    echo "<ul class='yith-wcan-label yith-wcan yith-wcan-group'>";

                    foreach ( $terms as $term ) {

                        // Get count based on current view - uses transients
                        $transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

                        if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

                            $_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

                            set_transient( $transient_name, $_products_in_term );
                        }

                        $option_is_set = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

                        // If this is an AND query, only show options with count > 0
                        if ( $query_type == 'and' ) {

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id )
                                continue;

                            if ( $count > 0 && $current_term !== $term->term_id )
                                $found = true;

                            if ( $count == 0 && ! $option_is_set )
                                continue;

                            // If this is an OR query, show all options so search can be expanded
                        } else {

                            // skip the term for the current archive
                            if ( $current_term == $term->term_id )
                                continue;

                            $count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

                            if ( $count > 0 )
                                $found = true;

                        }

                        $arg = 'filter_' . sanitize_title( $instance['attribute'] );

                        $current_filter = ( isset( $_GET[ $arg ] ) ) ? explode( ',', $_GET[ $arg ] ) : array();

                        if ( ! is_array( $current_filter ) )
                            $current_filter = array();

                        $current_filter = array_map( 'esc_attr', $current_filter );

                        if ( ! in_array( $term->term_id, $current_filter ) )
                            $current_filter[] = $term->term_id;

                        // Base Link decided by current page
                        if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
                            $link = home_url();
                        } elseif ( is_post_type_archive( 'product' ) || is_page( woocommerce_get_page_id('shop') ) ) {
                            $link = get_post_type_archive_link( 'product' );
                        } else {
                            $link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
                        }

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

                                    if ( ! empty( $data['terms'] ) )
                                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );

                                    if ( $data['query_type'] == 'or' )
                                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                                }
                            }
                        }

                        // Min/Max
                        if ( isset( $_GET['min_price'] ) )
                            $link = add_query_arg( 'min_price', $_GET['min_price'], $link );

                        if ( isset( $_GET['max_price'] ) )
                            $link = add_query_arg( 'max_price', $_GET['max_price'], $link );

                        // Current Filter = this widget
                        if ( isset( $_chosen_attributes[ $taxonomy ] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) {

                            $class = 'class="chosen"';

                            // Remove this term is $current_filter has more than 1 term filtered
                            if ( sizeof( $current_filter ) > 1 ) {
                                $current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
                                $link = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
                            }

                        } else {

                            $class = '';
                            $link = add_query_arg( $arg, implode( ',', $current_filter ), $link );

                        }

                        // Search Arg
                        if ( get_search_query() )
                            $link = add_query_arg( 's', get_search_query(), $link );

                        // Post Type Arg
                        if ( isset( $_GET['post_type'] ) )
                            $link = add_query_arg( 'post_type', $_GET['post_type'], $link );

                        // Query type Arg
                        if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[ $taxonomy ]['terms'] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) )
                            $link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );

                        echo '<li ' . $class . '>';

                        echo ( $count > 0 || $option_is_set ) ? '<a title="' . $term->name . '" href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';

                        echo $instance['labels'][$term->term_id];

                        echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

                        //echo ' <small class="count">' . $count . '</small></li>';

                    }
                    echo "</ul>";

                } // End display type conditional

                echo $after_widget;

                if ( ! $found )  {
                    ob_end_clean();
                    echo substr($before_widget, 0, strlen($before_widget) - 1) . ' style="display:none">' . $after_widget;
                } else {
                    echo ob_get_clean();
                }
            }
        }


        function form( $instance ) {
            global $woocommerce;

            $defaults = array(
                'title' => '',
                'attribute' => '',
                'query_type' => 'and',
                'type' => 'list',
                'colors' => '',
                'labels' => ''
            );

            $instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <p>
                <label>
                    <strong><?php _e( 'Title', 'yit' ) ?>:</strong><br />
                    <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
                </label>
            </p>

            <p><label for="<?php echo $this->get_field_id('attribute'); ?>"><strong><?php _e('Attribute:', 'yit') ?></strong></label>
            <select class="yith_wcan_attributes widefat" id="<?php echo esc_attr( $this->get_field_id('attribute') ); ?>" name="<?php echo esc_attr( $this->get_field_name('attribute') ); ?>">
                <?php yith_wcan_dropdown_attributes( $instance['attribute'] ); ?>
            </select></p>

            <p><label for="<?php echo $this->get_field_id( 'query_type' ); ?>"><?php _e( 'Query Type:', 'yit' ) ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'query_type' ) ); ?>">
                    <option value="and" <?php selected( $instance['query_type'], 'and' ); ?>><?php _e( 'AND', 'yit' ); ?></option>
                    <option value="or" <?php selected( $instance['query_type'], 'or' ); ?>><?php _e( 'OR', 'yit' ); ?></option>
                </select></p>

            <p><label for="<?php echo $this->get_field_id('type'); ?>"><strong><?php _e('Type:', 'yit') ?></strong></label>
                <select class="yith_wcan_type widefat" id="<?php echo esc_attr( $this->get_field_id('type') ); ?>" name="<?php echo esc_attr( $this->get_field_name('type') ); ?>">
                    <option value="list" <?php selected( 'list', $instance['type'] ) ?>><?php _e( 'List', 'yit' ) ?></option>
                    <option value="color" <?php selected( 'color', $instance['type'] ) ?>><?php _e( 'Color', 'yit' ) ?></option>
                    <option value="label" <?php selected( 'label', $instance['type'] ) ?>><?php _e( 'Label', 'yit' ) ?></option>
                </select></p>

            <div class="yith_wcan_placeholder">
                <?php yith_wcan_attributes_table(
                        $instance['type'],
                        $instance['attribute'],
                        'widget-' . $this->id . '-',
                        'widget-' . $this->id_base . '[' . $this->number . ']',
                        $instance['type'] == 'color' ? $instance['colors'] : ( $instance['type'] == 'label' ? $instance['labels'] : array() )
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

            $instance = $old_instance;

            if ( empty( $new_instance['title'] ) )
                $new_instance['title'] = $woocommerce->attribute_label( $new_instance['attribute'] );

            $instance['title'] = strip_tags( $new_instance['title'] );
            $instance['attribute'] = stripslashes( $new_instance['attribute'] );
            $instance['query_type'] = stripslashes( $new_instance['query_type'] );
            $instance['type'] = stripslashes( $new_instance['type'] );
            $instance['colors'] = $new_instance['colors'];
            $instance['labels'] = $new_instance['labels'];

            return $instance;
        }

    }
}