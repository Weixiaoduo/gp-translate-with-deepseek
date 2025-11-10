<?php
/**
 * Plugin Name:       GP Translate with DeepSeek
 * Plugin URI:        https://www.weixiaoduo.com/plugins/gp-translate-with-deepseek
 *
 * Description:       GlotPress Translate with DeepSeek AI.
 * Tags:              glotpress, translate, machine translate, deepseek, ai
 *
 * Requires at least: 4.9
 * Requires PHP:      7.4
 * Version:           1.0.0
 *
 * Author:            Weixiaoduo.com
 * Author URI:        https://www.weixiaoduo.com/
 *
 * License:           GPLv2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       gp-translate-with-deepseek
 * Domain Path:       /languages
 *
 * @package Wenpai\GpDeepseekTranslate
 */

namespace Wenpai\GpDeepseekTranslate;

// If this file is accessed directly, then abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GPDEEPSEEK_TD', 'gp-translate-with-deepseek' );
define( 'GPDEEPSEEK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GPDEEPSEEK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Load plugin textdomain.
 *
 * @return void
 */
function load_textdomain() {
	load_plugin_textdomain(
		'gp-translate-with-deepseek',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'Wenpai\GpDeepseekTranslate\load_textdomain' );

/**
 * Setup plugin data.
 *
 * @return void
 */
function setup() {
	global $gpdeepseek_translate;

	// Load plugin classes.
	require_once trailingslashit( __DIR__ ) . 'src/class-config.php';
	require_once trailingslashit( __DIR__ ) . 'src/class-locales.php';
	require_once trailingslashit( __DIR__ ) . 'src/class-translate.php';

	require_once trailingslashit( __DIR__ ) . 'src/class-admin-page.php';
	require_once trailingslashit( __DIR__ ) . 'src/class-settings.php';
	require_once trailingslashit( __DIR__ ) . 'src/class-profile.php';
	require_once trailingslashit( __DIR__ ) . 'src/class-frontend.php';
	require_once trailingslashit( __DIR__ ) . 'src/class-ajax.php';

	$gpdeepseek_translate['admin-page'] = new Admin_Page();
	$gpdeepseek_translate['settings']   = new Settings();
	$gpdeepseek_translate['profile']    = new Profile();
	$gpdeepseek_translate['frontend']   = new Frontend();
	$gpdeepseek_translate['ajax']       = new Ajax();
}
add_action( 'after_setup_theme', 'Wenpai\GpDeepseekTranslate\setup' );
