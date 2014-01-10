<?php
/**
 * Main class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.1.1
 */

if ( !defined( 'YITH_WCAN' ) ) { exit; } // Exit if accessed directly

if( !class_exists( 'YITH_WCAN' ) ) {
    /**
     * YITH WooCommerce Ajax Navigation Widget
     *
     * @since 1.0.0
     */
class YITH_WCAN_Reset_Navigation_Widget extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'yith-woo-ajax-reset-navigation yith-woo-ajax-navigation woocommerce widget_layered_nav', 'description' => __( 'Reset all filters setted by YITH WooCommerce Ajax Navigation', 'yit') );
        $control_ops = array('width' => 400, 'height' => 350);
        parent::__construct('yith-woo-ajax-reset-navigation', __('YITH WooCommerce Ajax Reset Navigation', 'yit'), $widget_ops, $control_ops);
    }


    function widget( $args, $instance ) {
        global $_chosen_attributes, $woocommerce, $_attributes_array;

        extract( $args );

        if ( ! is_post_type_archive( 'product' ) && ! is_tax( array_merge( $_attributes_array, array( 'product_cat', 'product_tag' ) ) ) )
            return;

        // Price
        $min_price = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : 0;
        $max_price = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : 0;

        ob_start();

        if ( count( $_chosen_attributes ) > 0 || $min_price > 0 || $max_price > 0 ) {
            $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title'], $instance, $this->id_base) : '';
            $label = isset($instance['label']) ? apply_filters('yith-wcan-reset-navigation-label', $instance['label'], $instance, $this->id_base) : '';

            //clean the url
            $link = yit_curPageURL();
            foreach( $_chosen_attributes as $taxonomy => $data ) {
                $taxonomy_filter 	= str_replace( 'pa_', '', $taxonomy );
                $link = remove_query_arg( 'filter_' . $taxonomy_filter, $link );
            }
            if( isset( $_GET['min_price'] ) ) {
                $link = remove_query_arg( 'min_price', $link );
            }
            if( isset( $_GET['max_price'] ) ) {
                $link = remove_query_arg( 'max_price', $link );
            }

            echo $before_widget;
            if( $title ) {
                echo $before_title . $title . $after_title;
            }

            echo "<div class='yith-wcan'><a class='yith-wcan-reset-navigation button' href='{$link}'>". __( $label, 'yit' ) ."</a></div>";
            echo $after_widget;
            echo ob_get_clean();
        } else {
            ob_end_clean();
            echo substr($before_widget, 0, strlen($before_widget) - 1) . ' style="display:none">' . $after_widget;
        }
    }


function form( $instance ) {
    global $woocommerce;

    $defaults = array(
        'title' => '',
        'label' => __('Reset All Filters', 'yit')
    );

    $instance = wp_parse_args( (array) $instance, $defaults ); ?>

    <p>
        <label>
            <strong><?php _e( 'Title', 'yit' ) ?>:</strong><br />
            <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
        </label>
    </p>
    <p>
        <label>
            <strong><?php _e( 'Button Label', 'yit' ) ?>:</strong><br />
            <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" value="<?php echo $instance['label']; ?>" />
        </label>
    </p>

<?php
}

    function update( $new_instance, $old_instance ) {
        global $woocommerce;

        $instance = $old_instance;

        if ( empty( $new_instance['title'] ) )
            $new_instance['title'] = $woocommerce->attribute_label( $new_instance['attribute'] );

        $instance['label'] = strip_tags($new_instance['label']);

        return $instance;
    }

}
}