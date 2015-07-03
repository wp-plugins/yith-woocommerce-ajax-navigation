<?php
/**
 * Frontend class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( ! defined( 'YITH_WCAN' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAN_Frontend' ) ) {
    /**
     * Frontend class.
     * The class manage all the frontend behaviors.
     *
     * @since 1.0.0
     */
    class YITH_WCAN_Frontend {
        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version;

        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct( $version ) {
            $this->version = $version;

            //Actions
            add_action( 'init', array( $this, 'init' ) );
            add_action( 'init', array( $this, 'woocommerce_layered_nav_init' ), 99 );

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

            // YITH WCAN Loaded
            do_action( 'yith_wcan_loaded' );
        }


        /**
         * Init method:
         *  - default options
         *
         * @access public
         * @since  1.0.0
         */
        public function init() {

        }


        /**
         * Enqueue frontend styles and scripts
         *
         * @access public
         * @return void
         * @since  1.0.0
         */
        public function enqueue_styles_scripts() {
            if ( yith_wcan_can_be_displayed() ) {
                $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

                wp_enqueue_style( 'yith_wcan_admin', YITH_WCAN_URL . 'assets/css/frontend.css', false, $this->version );
                wp_enqueue_script( 'yith_wcan_frontend', YITH_WCAN_URL . 'assets/js/yith-wcan-frontend' . $suffix . '.js', array( 'jquery' ), $this->version, true );

                $args = array(
                    'container'    => '.products',
                    'pagination'   => 'nav.woocommerce-pagination',
                    'result_count' => '.woocommerce-result-count'
                );
                wp_localize_script( 'yith_wcan_frontend', 'yith_wcan', apply_filters( 'yith-wcan-frontend-args', $args ) );
            }
        }


        /**
         * Layered Nav Init
         *
         * @package    WooCommerce/Widgets
         * @access     public
         * @return void
         */
        public function woocommerce_layered_nav_init() {

            if ( is_active_widget( false, false, 'yith-woo-ajax-navigation', true ) && ! is_admin() ) {

                global $_chosen_attributes, $woocommerce;

                $_chosen_attributes = array();

                /* FIX TO WOOCOMMERCE 2.1 */
                if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
                    $attribute_taxonomies = wc_get_attribute_taxonomies();
                }
                else {
                    $attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
                }


                if ( $attribute_taxonomies ) {
                    foreach ( $attribute_taxonomies as $tax ) {

                        $attribute = wc_sanitize_taxonomy_name( $tax->attribute_name );

                        /* FIX TO WOOCOMMERCE 2.1 */
                        if ( function_exists( 'wc_attribute_taxonomy_name' ) ) {
                            $taxonomy = wc_attribute_taxonomy_name( $attribute );
                        }
                        else {
                            $taxonomy = $woocommerce->attribute_taxonomy_name( $attribute );
                        }

                        $name            = 'filter_' . $attribute;
                        $query_type_name = 'query_type_' . $attribute;

                        if ( ! empty( $_GET[$name] ) && taxonomy_exists( $taxonomy ) ) {

                            $_chosen_attributes[ $taxonomy ]['terms'] = explode( ',', $_GET[ $name ] );

                            if ( empty( $_GET[ $query_type_name ] ) || ! in_array( strtolower( $_GET[ $query_type_name ] ), array( 'and', 'or' ) ) )
                                $_chosen_attributes[ $taxonomy ]['query_type'] = apply_filters( 'woocommerce_layered_nav_default_query_type', 'and' );
                            else
                                $_chosen_attributes[ $taxonomy ]['query_type'] = strtolower( $_GET[ $query_type_name ] );

                        }
                    }
                }

                if ( version_compare( preg_replace( '/-beta-([0-9]+)/', '', $woocommerce->version ), '2.1', '<' ) ) {
                    add_filter( 'loop_shop_post_in', 'woocommerce_layered_nav_query' );
                }
                else {
                    add_filter( 'loop_shop_post_in', array( WC()->query, 'layered_nav_query' ) );
                }


            }
        }


    }
}
