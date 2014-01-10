<?php
/**
 * Main class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.2.0
 */

if ( !defined( 'YITH_WCAN' ) ) { exit; } // Exit if accessed directly

if( !class_exists( 'YITH_WCAN' ) ) {
    /**
     * YITH WooCommerce Ajax Navigation
     *
     * @since 1.0.0
     */
    class YITH_WCAN {
        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = '1.2.0';

        /**
         * Plugin object
         *
         * @var string
         * @since 1.0.0
         */
        public $obj = null;

        /**
         * Constructor
         *
         * @return mixed|YITH_WCAN_Admin|YITH_WCAN_Frontend
         * @since 1.0.0
         */
        public function __construct() {

            // actions
            add_action( 'init', array( $this, 'init' ) );
            add_action( 'widgets_init', array( $this, 'registerWidgets' ) );


            if( is_admin() ) {
                $this->obj = new YITH_WCAN_Admin( $this->version );
            }  else {
                $this->obj = new YITH_WCAN_Frontend( $this->version );
            }

            return $this->obj;
        }


        /**
         * Init method
         *
         * @access public
         * @since 1.0.0
         */
        public function init() {}


        /**
         * Load and register widgets
         *
         * @access public
         * @since 1.0.0
         */
        public function registerWidgets() {
            register_widget( 'YITH_WCAN_Navigation_Widget' );
            register_widget( 'YITH_WCAN_Reset_Navigation_Widget' );
        }

    }
}