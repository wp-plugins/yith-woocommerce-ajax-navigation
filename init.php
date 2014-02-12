<?php
/**
 * Plugin Name: YITH WooCommerce Ajax Navigation
 * Plugin URI: http://yithemes.com/
 * Description: YITH WooCommerce Ajax Navigation allows user to filter products in Shop page without reloading the page.
 * Version: 1.3.0
 * Author: Your Inspiration Themes
 * Author URI: http://yithemes.com/
 * Text Domain: yit
 * Domain Path: /languages/
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.0
 */
/*  Copyright 2013  Your Inspiration Themes  (email : plugins@yithemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Required functions
 */
if( !defined('YITH_FUNCTIONS') ) {
    require_once( 'yit-common/yit-functions.php' );
}

function yith_wcan_constructor() {
    global $woocommerce;
    if ( ! isset( $woocommerce ) ) return;

    load_plugin_textdomain( 'yit', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

    define( 'YITH_WCAN', true );
    define( 'YITH_WCAN_URL', plugin_dir_url( __FILE__ ) );
    define( 'YITH_WCAN_DIR', plugin_dir_path( __FILE__ ) );
    define( 'YITH_WCAN_VERSION', '1.3.0' );

    // Load required classes and functions
    require_once('functions.yith-wcan.php');
    require_once('class.yith-wcan-admin.php');
    require_once('class.yith-wcan-frontend.php');
    require_once('class.yith-wcan-helper.php');
    require_once('widgets/class.yith-wcan-navigation-widget.php');
    require_once('widgets/class.yith-wcan-reset-navigation-widget.php');
    require_once('class.yith-wcan.php');

    // Let's start the game!
    global $yith_wcan;
    $yith_wcan = new YITH_WCAN();
}
add_action( 'plugins_loaded', 'yith_wcan_constructor' );
