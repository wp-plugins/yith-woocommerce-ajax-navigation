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

if ( ! class_exists( 'YITH_WCAN' ) ) {
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
        public $version;

        /**
         * Frontend object
         *
         * @var string
         * @since 1.0.0
         */
        public $frontend = null;


        /**
         * Admin object
         *
         * @var string
         * @since 1.0.0
         */
        public $admin = null;


        /**
         * Main instance
         *
         * @var string
         * @since 1.4.0
         */
        protected static $_instance = null;

        /**
         * Constructor
         *
         * @return mixed|YITH_WCAN_Admin|YITH_WCAN_Frontend
         * @since 1.0.0
         */
        public function __construct() {

            $this->version = YITH_WCAN_VERSION;

            /* Load Plugin Framework */
            add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );

            /* Register Widget */
            add_action( 'widgets_init', array( $this, 'registerWidgets' ) );

            $this->required();

            $this->init();
        }

        /**
         * Load and register widgets
         *
         * @access public
         * @since  1.0.0
         */
        public function registerWidgets() {
            $widgets = apply_filters( 'yith_wcan_widgets', array(
                    'YITH_WCAN_Navigation_Widget',
                    'YITH_WCAN_Reset_Navigation_Widget',
                )
            );

            foreach( $widgets as $widget ){
                register_widget( $widget );
            }
        }

        /**
		 * Load plugin framework
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT' ) || ! defined( 'YIT_CORE_PLUGIN' ) ) {
                require_once( YITH_WCAN_DIR . 'plugin-fw/yit-plugin.php' );
            }
		}

        /**
		 * Main plugin Instance
		 *
		 * @return YITH_Vendors Main instance
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}


        /**
         * Load required files
         *
         * @since 1.4
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function required(){
            $required = apply_filters( 'yith_wcan_required_files', array(
                    'includes/functions.yith-wcan.php',
                    'includes/class.yith-wcan-admin.php',
                    'includes/class.yith-wcan-frontend.php',
                    'includes/class.yith-wcan-helper.php',
                    'widgets/class.yith-wcan-navigation-widget.php',
                    'widgets/class.yith-wcan-reset-navigation-widget.php',
                )
            );

            foreach( $required as $file ){
                file_exists( YITH_WCAN_DIR . $file ) && require_once( YITH_WCAN_DIR . $file );
            }
        }

        public function init() {
            if ( is_admin() ) {
                $this->admin = new YITH_WCAN_Admin( $this->version );
            }
            else {
                $this->frontend = new YITH_WCAN_Frontend( $this->version );
            }
        }

    }
}