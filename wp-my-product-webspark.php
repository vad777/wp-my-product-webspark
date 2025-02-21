<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://#
 * @since             1.0.0
 * @package           Wp_My_Product_Webspark
 *
 * @wordpress-plugin
 * Plugin Name:       Wp My Product Webspark
 * Plugin URI:        https://#
 * Description:       Extends standard WooCommerce functionality
 * Version:           1.0.0
 * Author:            Webspark
 * Author URI:        https://#/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-my-product-webspark
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_MY_PRODUCT_WEBSPARK_VERSION', '1.0.0' );


function wpmpw_check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('WooCommerce must be installed and active!', 'wpmpw'));
    }
}
register_activation_hook(__FILE__, 'wpmpw_check_woocommerce_active');


function wpmpw_add_my_account_menu_items($items)
{
    $items['add-product'] = 'Add Product';
    $items['my-products'] = 'My Products';
    return $items;
}

add_filter('woocommerce_account_menu_items', 'wpmpw_add_my_account_menu_items');


include_once plugin_dir_path(__FILE__) . 'includes/add-product.php';
include_once plugin_dir_path(__FILE__) . 'includes/my-products.php';
require_once plugin_dir_path(__FILE__) . 'includes/email-init.php';


function wpmpw_delete_product() {
    if (isset($_GET['delete_product']) && is_user_logged_in()) {
        $product_id = intval($_GET['delete_product']);
        $product = get_post($product_id);

        if ($product && $product->post_author == get_current_user_id()) {
            wp_trash_post($product_id);
            wp_redirect(wc_get_account_endpoint_url('my-products'));
            exit;
        }
    }
}
add_action('template_redirect', 'wpmpw_delete_product');


function wpmpw_enqueue_styles() {
    wp_enqueue_style('wpmpw-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('wpmpw-script', plugin_dir_url(__FILE__) . 'assets/js/script.js');
}
add_action('wp_enqueue_scripts', 'wpmpw_enqueue_styles');

add_action('pre_get_posts', function ($query) {
    if (!is_admin() && isset($query->query_vars['woocommerce_my_account_endpoint'])
        && $query->query_vars['woocommerce_my_account_endpoint'] === 'my-products') {
        $paged = get_correct_paged();
        $query->set('paged', $paged);
        $query->set('post_type', 'product');
    }
});