=== YITH WooCommerce Ajax Product Filter ===

Contributors: yithemes
Tags: woocommerce ajax product filter download, woocommerce, widget, ajax, ajax filtered nav, ajax navigation, ajax filtered navigation, woocommerce layered navigation, woocommerce layered nav, product filter, product filters, ajax product filter, woocommerce ajax product filter, woocommerce filters, sidebar filter, sidebar ajax filter, ajax price filter, price filter, product sorting, woocommerce filter, taxonomy filter, attribute filter, attributes filter, woocommerce product sort, ajax sort, woocommerce ajax product filter, advanced product filters, ajax product filters, filters, woocommerce ajax product filters, woocommerce product filters, woocommerce product filters, category filter, attribute filters, woocommerce products filter, woocommerce price filter, yit, yith, yithemes
Requires at least: 4.0
Tested up to: 4.3
Stable tag: 2.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

YITH WooCommerce Ajax Product Filter offers you the perfect way to filter all products of your WooCommerce shop.

== Description ==

= Filter by the specific product you are looking for =

A powerful WooCommerce plugin: WooCommerce product filter widget, WooCommerce Ajax Product Filter lets you apply the filters you need to display the correct WooCommerce variations of the products you are looking for.
Choose among color, label, list and dropdown and your WooCommerce filtering search will display those specific products that suit perfectly your needs.
An extremely helpful WooCommerce plugin to help customers find what they really want.
All this can be done in a quick and very intuitive way that will certainly help your WooCommerce store improve in quality and usability.


Working demos for YITH WooCommerce Ajax Product Filter are available here:
**[LIVE DEMO 1](http://live.yithemes.com/globe/shop/?layout-shop=sidebar-right)** - **[LIVE DEMO 2](http://preview.yithemes.com/bazar/shop/)**

Full documentation for YITH WooCommerce Ajax Product Filter is available [here](http://yithemes.com/docs-plugins/yith-woocommerce-ajax-product-filter/).

**Main Features of YITH WooCommerce Ajax Product Filter:**

* Filter WooCommerce products with YITH WooCommerce Ajax Product Filter widget (4 layouts available)
 * List
 * Dropdown
 * Color
 * Label
* Reset all applied filters with YITH WooCommerce Ajax Reset Filter widget

= Premium features of YITH WooCommerce Ajax Product Filter: =

* Two additional layouts for the YITH WooCommerce Ajax Product Filter widget (BiColor, Tags), in addition to compatibility with the plugin YITH WooCommerce Brands
* Customizable reset button (in the YITH WooCommerce Ajax Reset Filter widget)
* WooCommerce Search filter for products of a specific price range available thanks to the YITH WooCommerce Ajax List Price Filter widget
* Search filter for products on sale/available
* Ajax sorting for products displayed in the page (by rate, price, popularity, most recent)
* Upload of an icon as customized loader
* Customization of the WooCommerce Price Filter widget


YITH WooCommerce Ajax Product Filter is available in combination with many other plugins in [**YITH Essential Kit for WooCommerce #1**](https://wordpress.org/plugins/yith-essential-kit-for-woocommerce-1/), a bundle of indispensable tools to make your WooCommerce site look more professional and be more user-friendly. Learn more about all of WooCommerce plugins included and boost your WooCommerce site with a simple click!


= Compatibility with WooCommerce plugins =

YITH WooCommerce Ajax Product Filter has been tested and compatibility is certain with the following WooCommerce plugins that you can add to your site:

* [YITH WooCommerce Multi Vendor](https://wordpress.org/plugins/yith-woocommerce-product-vendors/)
* [YITH WooCommerce Brands Add-On](https://wordpress.org/plugins/yith-woocommerce-brands-add-on/)
* [YITH Product Size Charts for WooCommerce](https://wordpress.org/plugins/yith-product-size-charts-for-woocommerce/)

Nevertheless, it could be compatible with many other WooCommerce plugins that have not been tested yet. If you want to inform us about compatibility with other plugins, please, [email to us](mailto:plugins@yithemes.com "Your Inspiration Themes").

== Installation ==

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site.
3. Activate `YITH WooCommerce Ajax Product Filter` from Plugins page.

== Frequently Asked Questions ==

= Why isn't the widget displayed in my sidebar? =
In order to display the widget, you need to assign it to the sidebar in the Shop page and you also need to add WooCommerce Product Attributes to your product. Read the "Getting Started" section of the documentation to learn how to add them.

= Translation issue with the version 2.0.0 =
Dear users,
we would like to inform you that the plugin YITH WooCommerce Ajax Navigation will change its name into YITH WooCommerce Ajax Product Filter from the next update.
In addition to the name, with the new release the plugin, textdomain will change too from "yit" to "yith_wc_ajxnav".
This change solves issues concerning textdomain conflicts generated by some translation/multilanguage plugins you have identified in the past weeks.
It may be possible that, with the plugin update, some language files will not be recognized by WordPress. In this case, you will just have to rename the language files with the correct format, changing the old textdomain with the new one.
For example, if your language files were named yit-en_GB.po and yit-en_GB.mo, you will just have to rename them respectively as yith_wc_ajxnav-en_GB.po and yith_wc_ajxnav-en_GB.mo.
After renaming the files, you can update/translate the .po file following the classic procedure for translations.

= The widget with WooCommerce filters is not working =
= The page doesn't update after clicking on a WooCommerce filter =

The issue could be related to the fact you are using a non-standard template for a WooCommerce shop page. To solve it, you should ask to the theme's author to use WooCommerce standard HTML classes. 
As an alternative:
**For version prior to 2.2.0:** 

you can use this piece of code in functions.php file of your theme:

`
if( ! function_exists( 'yith_wcan_frontend_classes' ) ){
	 function yith_wcan_frontend_classes(){
	  return array(
	            'container'    => 'YOUR_SHOP_CONTAINER',
	            'pagination'   => 'YOUR_PAGINATION_CONTAINER',
	            'result_count' => 'YOUR_RESULT_COUNT_CONTAINER'

	        ); 
	 }
}

add_filter( 'yith_wcan_ajax_frontend_classes', 'yith_wcan_frontend_classes' );
`

If you don't know which classes you should use, ask to the developer of your theme.

**From version 2.3.0 or later:**

You don't have to write manually the code anymore, as you can just go to YITH Plugin -> Ajax Product Filter -> Front End and set easily the parameters from the text fields.

If you don't know which classes you should use, ask to the developer of your theme.

= PAAMAYIM NEKUDOTAYIM Error after update 2.1.0 =

After the update 2.1.0, some users of YITH WooCommerce Ajax Product Filter are experiencing the error: "Parse error: syntax error, unexpected T_PAAMAYIM_NEKUDOTAYIM". This is caused by the PHP version of your server that is older than the 5.3. To solve the issue, you just have to update the plugin to the version 2.1.1.

= Is it compatible with all WordPress themes? =

Compatibility with all themes is impossible, because they are too many, but generally if themes are developed according to WordPress and WooCommerce guidelines, YITH plugins are compatible with them.
Yet, we can grant compatibility with themes developed by YIThemes, because they are constantly updated and tested with our plugins. Sometimes, especially when new versions are released, it might only require some time for them to be all updated, but you can be sure that they will be tested and will be working in a few days. 


= How can I get support if my WooCommerce plugin is not working? =

If you have problems with our WooCommerce plugins or something is not working as it should, first follow this preliminary steps:

* Test the plugin with a WordPress default theme, to be sure that the error is not caused by the theme you are currently using.
* Deactivate all plugins you are using and check if the problem is still occurring.
* Ensure that you plugin version, your theme version and your WordPress and WooCommerce version (if required) are updated and that the problem you are experiencing has not already been solved in a later plugin update.

If none of the previous listed actions helps you solve the problem, then, submit a ticket in the forum and describe your problem accurately, specify WordPress and WooCommerce versions you are using and any other information that might help us solve your problem as quickly as possible. Thanks! 


= How can I get more features for my WooCommerce plugin? =

You can get more features with the premium version of YITH WooCommerce Ajax Product Filter, available on [YIThemes page]( https://yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/). Here you can read more about the premium features of the plugin and make it give it its best shot!


= How can I try the full-featured plugin? =

If you want to see a demonstration version of the premium plugin, you can see it installed on two different WooCommerce sites, either in [this page]( http://plugins.yithemes.com/yith-woocommerce-ajax-product-filter/?preview) or in [this page](http://preview.yithemes.com/bazar/shop/). Browse it and try all options available so that you can see how your plugin looks like.

== Screenshots ==

1. Admin - Appearance -> Widget: WooCommerce Filter Widget List Style
2. Admin - Appearance -> Widget: WooCommerce Filter Widget Color Style
3. Admin - Appearance -> Widget: WooCommerce Filter Widget Label Style
4. Admin - Appearance -> Widget: WooCommerce Filter Widget Dropdown Style
5. Admin - Appearance -> Widget: WooCommerce Filter Reset Button
6. Frontend: WooCommerce Widget in sidebar
7. Frontend: Dropdown style
8. Frontend: Reset button and active filters
9. Admin: YIT Plugins -> Ajax Product Filter -> Front end
10. Admin: YIT Plugins -> Ajax Product Filter -> Custom Style

== Changelog ==

= 2.3.1 =

* Added: Support to YITH Infinite Scrolling plugin
* Fixed: No pagination container issue after filter applied
* Fixed: js error yit_wcan not defined
* Fixed: issue with blank label

= 2.3.0 =

* Added: Custom Style Section
* Added: New frontend options for script configuration
* Updated: Plugin Core Framework
* Updated: Languages file

= 2.2.0 =

* Added: Support to WordPress 4.3
* Updated: Language files
* Fixed: Color lost after change widget style with WordPress 4.3
* Fixed: Warning when switch from color to label style

= 2.1.2 =

* Added: Support to WooCommerce 2.4
* Updated: Plugin Framework
* Fixed: Tag list and child term support 

= 2.1.1 =

* Tweak: Support to PAAMAYIM NEKUDOTAYIM in PHP Version < 5.3

= 2.1.0 =

* Added: Frontend classes option panel
* Added: yith_wcan_ajax_frontend_classes filter
* Added: plugin works in product category page
* Added: WPML and String translation support
* Updated: language pot file
* Updated: Italian translation
* Tweak: Shop uri management
* Fixed: wrong filter link in product category page
* Fixed: Widget doesn't work fine in Shop Category Page
* Fixed: Remove trailing slash in widget shop uri
* Fixed: Prevent double instance in singleton class
* Fixed: The widget doesn't work with WPML with Label and Color style

= 2.0.4 =

* Added: Filter 'yith_wcan_product_taxonomy_type' to widget product tax type
* Tweak: YITH WooCommerce Brands Add-on support in taxonomy page


= 2.0.3 =

* Added: Support to Sortable attribute
* Fixed: Color lost after change widget style

= 2.0.2 =

* Fixed: Empty filters appear after update to 2.0.0

= 2.0.1 =

* Fixed: Unable to active plugin

= 2.0.0 =

* Tweak: Plugin core framework
* Updated: Languages file
* Fixed: Prevent warning issue with no set color/label
* Fixed: Textdomain conflict
* Fixed: Filter doesn't work if shop page is on front
* Removed: old default.po catalog language file

= 1.4.1 =

* Fixed: Wrong attribute show with WooCommerce 2.2

= 1.4.0 =

* Added: Support to WC 2.2
* Updated: Plugin Core Framework
* Fixed: Widget error on empty title
* Fixed: Ajax load on widget type switching

= 1.3.2 =

* Fixed: Wrong enqueue of the main css file
* Added: Filter yith_wcan_exclude_terms

= 1.3.1 =

* Added: Attribute order (All, Hieralchical or Only Parent style)
* Fixed: Dropdown Style on Firefox
* Fixed: Blank box on attribute without label (Label Style)
* Fixed: Blank box on attribute without color (Color Stle)

= 1.3.0 =

* Added: Support to WooCommerce 2.1.X
* Fixed: One filter bug on sidebar

= 1.2.1 =

* Fixed: Width of select dropdown too large

= 1.2.0 =

* Added: Dropdown style
* Added: Support to Wordpress 3.8
* Fixed: Error with non-latin languages
* Fixed: Improved WPML compatibility

= 1.1.2 =

* Added: Title to the color filters
* Removed: Limit of 3 characters in the label text input

= 1.1.1 =

* Minor bugs fixes

= 1.1.0 =

* Added new widget YITH WooCommerce Ajax Reset Navigation

= 1.0.0 =

* Initial release

== Translators ==

= Available Languages =
* English (Default)
* Italiano

If you have created your own language pack, or have an update for an existing one, you can send [gettext PO and MO file](http://codex.wordpress.org/Translating_WordPress "Translating WordPress")
[use](http://yithemes.com/contact/ "Your Inspiration Themes") so we can bundle it into YITH WooCommerce Ajax Navigation Languages.

== Documentation ==

Full documentation is available [here](http://yithemes.com/docs-plugins/yith_woocommerce_ajax_navigation/).

== Upgrade notice ==

= 2.2.0 =

* WordPress 4.3 Support

= 2.1.2 =

* WooCommerce 2.4 Support

= 2.1.1 =

* Tweak: Support to PAAMAYIM NEKUDOTAYIM in PHP Version < 5.3

= 2.0.0 =

New plugin core added.

= 1.0.0 =

Initial release