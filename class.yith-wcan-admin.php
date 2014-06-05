<?php
/**
 * Admin class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( !defined( 'YITH_WCAN' ) ) { exit; } // Exit if accessed directly

if( !class_exists( 'YITH_WCAN_Admin' ) ) {
    /**
     * Admin class. 
	 * The class manage all the admin behaviors.
     *
     * @since 1.0.0
     */
    class YITH_WCAN_Admin {
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
		 * @since 1.0.0
		 */
		public function __construct( $version ) {
            $this->version = $version;

			//Actions
			add_action( 'init', array( $this, 'init' ) );
            add_action('wp_ajax_yith_wcan_select_type', array( $this, 'ajax_print_terms') );

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

            // YITH WCAN Loaded
            do_action( 'yith_wcan_loaded' );
		}
		
		
		/**
		 * Init method:
		 *  - default options
		 * 
		 * @access public
		 * @since 1.0.0
		 */
		public function init() {}
		

		/**
		 * Enqueue admin styles and scripts
		 * 
		 * @access public
		 * @return void 
		 * @since 1.0.0
		 */
		public function enqueue_styles_scripts() {
            global $pagenow;

            if( 'widgets.php' == $pagenow ) {
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_style( 'yith_wcan_admin', YITH_WCAN_URL . 'assets/css/admin.css', false, $this->version );

                wp_enqueue_script( 'wp-color-picker' );
                wp_enqueue_script( 'yith_wcan_admin', YITH_WCAN_URL . 'assets/js/yith-wcan-admin.js', array('jquery', 'wp-color-picker'), $this->version, true );
            }
		}

        /**
         * Print terms for the element selected
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
        public function ajax_print_terms() {
            $type = $_POST['value'];
            $attribute = $_POST['attribute'];
            $return = array('message' => '', 'content' => $_POST);

            $terms = get_terms( 'pa_' . $attribute, array('hide_empty'=>'0') );

            $return['content'] = yith_wcan_attributes_table(
                $type,
                $attribute,
                $_POST['id'],
                $_POST['name'],
                json_decode($_POST['type']),
                false
            );

            echo json_encode($return);
            die();
        }

    }
}
