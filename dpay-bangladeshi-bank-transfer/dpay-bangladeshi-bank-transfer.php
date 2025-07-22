<?php
/*
Plugin Name: DPay Bangladeshi Bank Transfer
Plugin URI: https://github.com/Raisul447/dpay-bangladeshi-bank-transfer
Description: A WooCommerce payment gateway for Bangladeshi bank transfers that supports manual payment channels such as NPSB, RTGS, and BEFTN via DPay.
Version: 1.0.0
Author: Raisul Islam
Author URI: https://shagor.dev
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Text Domain: dpay-bangladeshi-bank-transfer
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// Include the gateway class
add_action('plugins_loaded', 'dpay_init_gateway_class');
function dpay_init_gateway_class() {
    if (class_exists('WC_Payment_Gateway')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-dpay-gateway.php';
    }
}

// Add the gateway to WooCommerce
add_filter('woocommerce_payment_gateways', 'dpay_add_gateway_class');
function dpay_add_gateway_class($methods) {
    $methods[] = 'WC_Gateway_DPay_Bank';
    return $methods;
}

// Add settings link under plugin name
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dpay_settings_link');
function dpay_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=dpay_bank">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
