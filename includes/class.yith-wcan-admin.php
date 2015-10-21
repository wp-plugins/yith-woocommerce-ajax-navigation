<?php
/**
 * Admin class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( ! defined( 'YITH_WCAN' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAN_Admin' ) ) {
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

        /* @var YIT_Plugin_Panel_WooCommerce */
        protected $_panel;

        /**
         * @var string Main Panel Option
         */
        protected $_main_panel_option;

        /**
         * @var string The panel page
         */
        protected $_panel_page = 'yith_wcan_panel';

        /**
         * @var string Official plugin documentation
         */
        protected $_official_documentation = 'https://yithemes.com/docs-plugins/yith-woocommerce-ajax-product-filter';

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter';

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_live = 'http://plugins.yithemes.com/yith-woocommerce-ajax-product-filter/shop/';

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
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

            /* Plugin Option Panel */
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
            add_action( 'yit_panel_wcan_description', array( $this, 'frontend_description_option' ), 10, 3 );
            add_action( 'yith_wcan_premium_tab', array( $this, 'premium_tab' ) );
            add_action( 'yith_wcan_custom_style_tab', array( $this, 'custom_style_tab' ) );

            /* Plugin Informations */
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCAN_DIR . '/' . basename( YITH_WCAN_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            /* Add WordPress Pointer */
            add_action( 'admin_init', array( $this, 'register_pointer' ) );

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
        public function init() {
        }


        /**
         * Enqueue admin styles and scripts
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
        public function enqueue_styles_scripts() {
            global $pagenow;

            if ( 'widgets.php' == $pagenow || 'admin.php' == $pagenow ) {
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_style( 'yith_wcan_admin', YITH_WCAN_URL . 'assets/css/admin.css', array( 'yit-plugin-style' ), $this->version );

                wp_enqueue_script( 'wp-color-picker' );
                wp_enqueue_script( 'yith_wcan_admin', YITH_WCAN_URL . 'assets/js/yith-wcan-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, true );
            }

            if( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'yith_wcan_panel' == $_GET['page'] && isset( $_GET['tab'] ) && 'custom-style' == $_GET['tab'] ){
                wp_enqueue_style( 'codemirror-style', YITH_WCAN_URL . 'assets/3rd-party/codemirror/lib/codemirror.css', array( 'yit-plugin-style' ) );
                wp_enqueue_script( 'codemirror-script', YITH_WCAN_URL . 'assets/3rd-party/codemirror/lib/codemirror.js', array( 'jquery' ), false, true );
                wp_enqueue_script( 'codemirror-script-css', YITH_WCAN_URL . 'assets/3rd-party/codemirror/mode/css/css.js', array( 'codemirror-script' ), false, true );
                wp_enqueue_script( 'yith-wcan-codemirror-init', YITH_WCAN_URL . 'assets/js/yith-wcan-editor.js', array( 'jquery' ), $this->version, true );
            }
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'frontend'      => __( 'Front end', 'yith-woocommerce-ajax-navigation' ),
                'custom-style'  => __( 'Custom Style', 'yith-woocommerce-ajax-navigation' ),
                'premium'       => __( 'Premium Version', 'yith-woocommerce-ajax-navigation' )
            );

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Ajax Product Filter', 'yith-woocommerce-ajax-navigation' ),
                'menu_title'       => __( 'Ajax Product Filter', 'yith-woocommerce-ajax-navigation' ),
                'capability'       => 'manage_options',
                'parent'           => 'wcan',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => apply_filters( 'yith_wcan_settings_tabs', $admin_tabs ),
                'options-path'     => YITH_WCAN_DIR . '/settings',
                'plugin-url'       => YITH_WCAN_URL
            );

            /* === Fixed: not updated theme  === */
            if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel( $args );
            $this->_main_panel_option = "yit_{$args['parent']}_options";

            $this->save_default_options();

            do_action( 'yith_wcan_after_option_panel', $args );
        }

 		/**
         * Add default option to panel
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
		public function save_default_options(){
            $options = get_option( $this->_main_panel_option );
            if( $options === false ){
                add_option( $this->_main_panel_option, $this->_panel->get_default_options());
            }
        }


        /**
         * Premium Tab
         */
        public function premium_tab() {
            require_once( YITH_WCAN_DIR . 'templates/admin/premium.php' );
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @use plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {
            $premium_live_text = defined( 'YITH_WCAN_FREE_INIT' ) ? __( 'Premium live demo', 'yith-woocommerce-ajax-navigation' ) : __( 'Live demo', 'yith-woocommerce-ajax-navigation' );
            $links[]           = '<a href="' . $this->_premium_live . '" target="_blank">' . $premium_live_text . '</a>';

            if ( defined( 'YITH_WCAN_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-ajax-navigation' ) . '</a>';
            }

            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( ( defined( 'YITH_WCAN_INIT' ) && YITH_WCAN_INIT == $plugin_file ) || ( defined( 'YITH_WCAN_FREE_INIT' ) && YITH_WCAN_FREE_INIT == $plugin_file ) ) {
                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-ajax-navigation' ) . '</a>';
            }
            return $plugin_meta;
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing . '?refer_id=1030585';
        }

        /**
         * Add WP Pointer
         */
        public function register_pointer() {

            if( defined( 'YITH_WCAN_PREMIUM' ) && YITH_WCAN_PREMIUM ){
                return;
            }

            if ( ! class_exists( 'YIT_Pointers' ) ) {
                include_once( 'plugin-fw/lib/yit-pointers.php' );
            }

            $message = __( 'Dear users,
                            we would like to inform you that the YITH WooCommerce Ajax Navigation plugin will change its name in YITH WooCommerce Ajax Product Filter from the next update. Also, the plugin textdomain will change too from "yit" to "yith-woocommerce-ajax-navigation".
                            This modification solves the issues about textdomain conflicts generated by some translation/multilanguage plugins you have identified in the past weeks.
                            If updating the plugin some language files are no more recognized by WordPress, you will just have to rename the language files in the correct format. After renaming the files, you can update/translate the .po file following the classic procedure for translations.', 'yith-woocommerce-ajax-navigation' );

            $plugin_name = _x( 'YITH WooCommerce Ajax Product Filter', 'Admin(Pointers): Plugin Title', 'yith-woocommerce-ajax-navigation' );

            $premium_message = sprintf( '%s, <a href="%s" target"_blank">%s</a>.', __( 'YITH WooCommerce Product Filter has been updated with new available options', 'yith-woocommerce-ajax-navigation' ), $this->_premium_landing, __( 'discover the PREMIUM version', 'yith-woocommerce-ajax-navigation' ) );

            $args = array();
            foreach ( array( 'plugins', 'update' ) as $screen ) {
                $args[] = array(
                    'screen_id'  => $screen,
                    'pointer_id' => 'yith_wcan_panel',
                    'target'     => '#toplevel_page_yit_plugin_panel',
                    'content'    => sprintf( '<h3> %s </h3> <p> %s </p> <p>%s</p>',
                        $plugin_name,
                        $message,
                        $premium_message
                    ),
                    'position'   => array( 'edge' => 'left', 'align' => 'center' ),
                    'init'       => YITH_WCAN_FREE_INIT
                );
            }

            YIT_Pointers()->register( $args );
        }

        /**
         * Add the frontend tab description
         *
         * @param $option
         * @param $db_value
         * @param $custom_attributes
         *
         * @return  string The description text
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function frontend_description_option( $option, $db_value, $custom_attributes  ){
            echo "<p>{$option['desc']}</p>";
        }

        /**
         * Custom Style tab
         */
        public function custom_style_tab(){
            require_once( YITH_WCAN_DIR . 'templates/admin/editor.php' );
        }
    }
}
