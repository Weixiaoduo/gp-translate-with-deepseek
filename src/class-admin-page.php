<?php
/**
 * Admin Page class file.
 *
 * @package Wenpai\GpDeepseekTranslate
 */

namespace Wenpai\GpDeepseekTranslate;

/**
 * Admin Page class.
 */
class Admin_Page {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 10 );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_submenu_page(
			'options-general.php',
			__( 'GP Translate with DeepSeek', 'gp-translate-with-deepseek' ),
			__( 'GP Translate with DeepSeek', 'gp-translate-with-deepseek' ),
			'manage_options',
			'gp-translate-with-deepseek',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'GP Translate with DeepSeek', 'gp-translate-with-deepseek' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'gpdeepseek_settings' );
				do_settings_sections( 'gpdeepseek_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
