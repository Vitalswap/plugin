<?php

/**
 * Plugin Name: VitalSwap 
 * Plugin URI: https://VitalSwap.com
 * Description: VitalSwap payment gateway for WooCommerce
 * Version: 2.0
 * Author: VitalSwap
 * Author URI: https://VitalSwap.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins: woocommerce
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.1
 * Text Domain: vitalswap
 * Domain Path: /languages
 */

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

if (!defined('ABSPATH')) {
	exit;
}

define('WC_VitalSwap_MAIN_FILE', __FILE__);
define('WC_VitalSwap_URL', untrailingslashit(plugins_url('/', __FILE__)));

define('WC_VitalSwap_VERSION', '2.0');

/**
 * Initialise VitalSwap payment gateway.
 */
function wc_VitalSwap_init()
{


	if (!class_exists('WC_Payment_Gateway')) {
		add_action('admin_notices', 'vitalswap_WC_VitalSwap_wc_missing_notice');
		return;
	}

	add_action('admin_init', 'vitalswap_WC_VitalSwap_testmode_notice');

	require_once __DIR__ . '/includes/class-wc-gateway-vitalswap.php';


	add_filter('woocommerce_payment_gateways', 'vitalswap_wc_add_VitalSwap_gateway', 99);

	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'vitalswap_woo_VitalSwap_plugin_action_links');
}
add_action('plugins_loaded', 'wc_VitalSwap_init', 99);



/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function vitalswap_woo_VitalSwap_plugin_action_links($links)
{

	$settings_link = array(
		'settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=VitalSwap') . '" title="' . __('View VitalSwap WooCommerce Settings', 'vitalswap') . '">' . __('Settings', 'vitalswap') . '</a>',
	);

	return array_merge($settings_link, $links);
}

/**
 * Add VitalSwap Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function vitalswap_wc_add_VitalSwap_gateway($methods)
{

	if (class_exists('WC_Subscriptions_Order') && class_exists('WC_Payment_Gateway_CC')) {
		$methods[] = 'WC_Gateway_VitalSwap_Subscriptions';
	} else {
		$methods[] = 'WC_Gateway_VitalSwap';
	}

	if ('NGN' === get_woocommerce_currency()) {

		$settings        = get_option('woocommerce_VitalSwap_settings', '');
		$custom_gateways = isset($settings['custom_gateways']) ? $settings['custom_gateways'] : '';

		switch ($custom_gateways) {
			case '5':
				$methods[] = 'WC_Gateway_VitalSwap_One';
				$methods[] = 'WC_Gateway_VitalSwap_Two';
				$methods[] = 'WC_Gateway_VitalSwap_Three';
				$methods[] = 'WC_Gateway_VitalSwap_Four';
				$methods[] = 'WC_Gateway_VitalSwap_Five';
				break;

			case '4':
				$methods[] = 'WC_Gateway_VitalSwap_One';
				$methods[] = 'WC_Gateway_VitalSwap_Two';
				$methods[] = 'WC_Gateway_VitalSwap_Three';
				$methods[] = 'WC_Gateway_VitalSwap_Four';
				break;

			case '3':
				$methods[] = 'WC_Gateway_VitalSwap_One';
				$methods[] = 'WC_Gateway_VitalSwap_Two';
				$methods[] = 'WC_Gateway_VitalSwap_Three';
				break;

			case '2':
				$methods[] = 'WC_Gateway_VitalSwap_One';
				$methods[] = 'WC_Gateway_VitalSwap_Two';
				break;

			case '1':
				$methods[] = 'WC_Gateway_VitalSwap_One';
				break;

			default:
				break;
		}
	}

	return $methods;
}

/**
 * Display a notice if WooCommerce is not installed
 */
function vitalswap_WC_VitalSwap_wc_missing_notice()
{
	/* translators: WooCommerce installation link  */
	echo esc_html('<div class="error"><p><strong>' . sprintf(__('VitalSwap requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'vitalswap'), '<a href="' . admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539') . '" class="thickbox open-plugin-details-modal">here</a>') . '</strong></p></div>');
}

/**
 * Display the test mode notice.
 **/
function vitalswap_WC_VitalSwap_testmode_notice()
{

	if (!class_exists(Notes::class)) {
		return;
	}

	if (!class_exists(WC_Data_Store::class)) {
		return;
	}

	if (!method_exists(Notes::class, 'get_note_by_name')) {
		return;
	}

	$test_mode_note = Notes::get_note_by_name('vitalswap-test-mode');

	if (false !== $test_mode_note) {
		return;
	}

	$VitalSwap_settings = get_option('woocommerce_VitalSwap_settings');
	$test_mode         = $VitalSwap_settings['testmode'] ?? '';

	if ('yes' !== $test_mode) {
		Notes::delete_notes_with_name('vitalswap-test-mode');

		return;
	}

	$note = new Note();
	$note->set_title(__('VitalSwap test mode enabled', 'vitalswap'));
	$note->set_content(__('VitalSwap test mode is currently enabled. Remember to disable it when you want to start accepting live payment on your site.', 'vitalswap'));
	$note->set_type(Note::E_WC_ADMIN_NOTE_INFORMATIONAL);
	$note->set_layout('plain');
	$note->set_is_snoozable(false);
	$note->set_name('vitalswap-test-mode');
	$note->set_source('vitalswap');
	$note->add_action('disable-vitalswap-test-mode', __('Disable VitalSwap test mode', 'vitalswap'), admin_url('admin.php?page=wc-settings&tab=checkout&section=VitalSwap'));
	$note->save();
}

add_action(
	'before_woocommerce_init',
	function () {
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
		}
	}
);




function vitalswap_template_array()
{

	$temps['checkout.php'] = "VitalSwap Checkout";
	$temps['thank_you.php'] = "VitalSwap Thank you";

	return $temps;
}

function vitalswap_template_register($page_templates, $theme, $post)
{
	$templates = vitalswap_template_array();

	foreach ($templates as $key => $value) {
		$page_templates[$key] = $page_templates[$value];
	}

	return $page_templates;
}
add_filter('theme_page_templates', 'vitalswap_template_register', 10, 3);
