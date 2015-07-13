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
            add_action( 'wp_ajax_yith_wcan_select_type', array( $this, 'ajax_print_terms' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

            add_action( 'yith_wcan_premium_tab', array( $this, 'premium_tab' ) );

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

            $return['content'] = yith_wcan_attributes_table(
                $type,
                $attribute,
                $_POST['id'],
                $_POST['name'],
                json_decode( $_POST['value'] ),
                false
            );

            echo json_encode( $return );
            die();
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
                'premium' => __( 'Premium Version', 'yith_wc_ajxnav' )
            );

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Ajax Product Filter', 'yith_wc_ajxnav' ),
                'menu_title'       => __( 'Ajax Product Filter', 'yith_wc_ajxnav' ),
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
        }

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
            $premium_live_text = defined( 'YITH_WCAN_FREE_INIT' ) ? __( 'Premium live demo', 'yith_wc_product_vendors' ) : __( 'Live demo', 'yith_wc_product_vendors' );
            $links[]           = '<a href="' . $this->_premium_live . '" target="_blank">' . $premium_live_text . '</a>';

            if ( defined( 'YITH_WCAN_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith_wc_product_vendors' ) . '</a>';
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
                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith_wc_product_vendors' ) . '</a>';
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

        public function register_pointer() {

            if( defined( 'YITH_WCAN_PREMIUM' ) && YITH_WCAN_PREMIUM ){
                return;
            }

            if ( ! class_exists( 'YIT_Pointers' ) ) {
                include_once( 'plugin-fw/lib/yit-pointers.php' );
            }

            $message = __( 'Dear users,
                            we would like to inform you that the YITH WooCommerce Ajax Navigation plugin will change its name in YITH WooCommerce Ajax Product Filter from the next update. Also, the plugin textdomain will change too from "yit" to "yith_wc_ajxnav".
                            This modification solves the issues about textdomain conflicts generated by some translation/multilanguage plugins you have identified in the past weeks.
                            If updating the plugin some language files are no more recognized by WordPress, you will just have to rename the language files in the correct format. After renaming the files, you can update/translate the .po file following the classic procedure for translations.', 'yith_wc_ajxnav' );

            $plugin_name = __( 'YITH WooCommerce Ajax Product Filter', 'yith_wc_ajxnav' );

            $premium_message = sprintf( '%s, <a href="%s" target"_blank">%s</a>.', __( 'YITH WooCommerce Product Filter has been updated with new available options', 'yith_wc_ajxnav' ), $this->_premium_landing, __( 'discover the PREMIUM version', 'yith_wc_ajxnav' ) );

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
    }
}
