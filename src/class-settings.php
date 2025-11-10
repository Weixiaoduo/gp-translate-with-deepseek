<?php
/**
 * Settings class file.
 *
 * @package Wenpai\GpDeepseekTranslate
 */

namespace Wenpai\GpDeepseekTranslate;

/**
 * Settings class.
 */
class Settings {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ), 10 );
	}

	/**
	 * Initialize settings.
	 *
	 * @return void
	 */
	public function init_settings(): void {
		// Section: DeepSeek API.
		add_settings_section(
			'gpdeepseek_section',
			__( 'DeepSeek API', 'gp-translate-with-deepseek' ),
			array( $this, 'render_section' ),
			'gpdeepseek_settings'
		);

		// Option: API Key.
		$this->register_field_api_key();

		// Option: Model.
		$this->register_field_model();

		// Option: Custom Prompt.
		$this->register_field_custom_prompt();

		// Option: Temperature.
		$this->register_field_temperature();
	}

	/**
	 * Render section.
	 *
	 * @return void
	 */
	public function render_section(): void {
		esc_html_e( 'Settings for DeepSeek API access.', 'gp-translate-with-deepseek' );
	}

	/**
	 * Register settings field API Key.
	 *
	 * @return void
	 */
	public function register_field_api_key(): void {
		$field_name    = 'gpdeepseek_api_key';
		$section_name  = 'gpdeepseek_section';
		$settings_name = 'gpdeepseek_settings';

		register_setting(
			$settings_name,
			$field_name,
			array(
				'label'             => __( 'DeepSeek API Key', 'gp-translate-with-deepseek' ),
				'description'       => __( 'Enter the DeepSeek API Key.', 'gp-translate-with-deepseek' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			),
		);

		add_settings_field(
			$field_name,
			__( 'DeepSeek API Key', 'gp-translate-with-deepseek' ),
			array( $this, 'render_field_api_key' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Register settings for Model.
	 *
	 * @return void
	 */
	public function register_field_model(): void {
		$field_name    = 'gpdeepseek_model';
		$section_name  = 'gpdeepseek_section';
		$settings_name = 'gpdeepseek_settings';

		register_setting(
			$settings_name,
			$field_name,
			array(
				'label'             => __( 'DeepSeek Model', 'gp-translate-with-deepseek' ),
				'description'       => __( 'Select the DeepSeek Model.', 'gp-translate-with-deepseek' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			),
		);

		add_settings_field(
			$field_name,
			__( 'DeepSeek Model', 'gp-translate-with-deepseek' ),
			array( $this, 'render_field_model' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Register settings for DeepSeek Custom Prompt.
	 *
	 * @return void
	 */
	public function register_field_custom_prompt(): void {
		$field_name    = 'gpdeepseek_custom_prompt';
		$section_name  = 'gpdeepseek_section';
		$settings_name = 'gpdeepseek_settings';

		register_setting(
			$settings_name,
			$field_name,
			array(
				'label'             => __( 'DeepSeek Custom Prompt', 'gp-translate-with-deepseek' ),
				'description'       => __( 'Enter your custom prompt for DeepSeek translation suggestions.', 'gp-translate-with-deepseek' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			),
		);

		add_settings_field(
			$field_name,
			__( 'DeepSeek Custom Prompt', 'gp-translate-with-deepseek' ),
			array( $this, 'render_field_custom_prompt' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Register settings for DeepSeek Temperature.
	 *
	 * @return void
	 */
	public function register_field_temperature(): void {
		$field_name    = 'gpdeepseek_temperature';
		$section_name  = 'gpdeepseek_section';
		$settings_name = 'gpdeepseek_settings';

		register_setting(
			$settings_name,
			$field_name,
			array(
				'label'             => __( 'DeepSeek Temperature', 'gp-translate-with-deepseek' ),
				'description'       => __( 'Enter the DeepSeek Temperature.', 'gp-translate-with-deepseek' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'show_in_rest'      => false,
			),
		);

		add_settings_field(
			$field_name,
			__( 'DeepSeek Temperature', 'gp-translate-with-deepseek' ),
			array( $this, 'render_field_temperature' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Render settings field API Key.
	 *
	 * @return void
	 */
	public function render_field_api_key(): void {
		$field_name = 'gpdeepseek_api_key';

		$api_key = get_option( $field_name, '' );
		?>
		<input type="text" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
		<p class="description"><?php esc_html_e( 'Enter the DeepSeek API Key.', 'gp-translate-with-deepseek' ); ?></p>
		<?php
	}

	/**
	 * Render settings for DeepSeek Model.
	 *
	 * @return void
	 */
	public function render_field_model(): void {
		$field_name = 'gpdeepseek_model';

		$models = array(
			'deepseek-chat',
			'deepseek-reasoner',
		);

		$model = get_option( $field_name, 'deepseek-chat' );
		?>
		<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>">
		<?php foreach ( $models as $model_name ) { ?>
			<option value="<?php echo esc_attr( $model_name ); ?>" <?php selected( $model, $model_name ); ?>><?php echo esc_html( $model_name ); ?></option>
		<?php } ?>
		</select>
		<p class="description"><?php esc_html_e( 'Select the DeepSeek Model.', 'gp-translate-with-deepseek' ); ?> <?php esc_html_e( 'Default:', 'gp-translate-with-deepseek' ); ?><code>deepseek-chat</code></p>
		<?php
	}

	/**
	 * Render settings for DeepSeek Custom Prompt.
	 *
	 * @return void
	 */
	public function render_field_custom_prompt(): void {
		$field_name = 'gpdeepseek_custom_prompt';

		$custom_prompt = get_option( $field_name, '' );
		?>
		<textarea name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" class="large-text"><?php echo esc_textarea( $custom_prompt ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Enter your custom prompt for DeepSeek translation suggestions.', 'gp-translate-with-deepseek' ); ?></p>
		<?php
	}

	/**
	 * Render settings for DeepSeek Temperature.
	 *
	 * @return void
	 */
	public function render_field_temperature(): void {
		$field_name = 'gpdeepseek_temperature';

		$temp = get_option( $field_name, 0 );
		?>
		<input type="text" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $temp ); ?>" class="regular-text">
		<p class="description"><?php esc_html_e( 'Enter the DeepSeek Temperature.', 'gp-translate-with-deepseek' ); ?> <?php esc_html_e( 'Default:', 'gp-translate-with-deepseek' ); ?><code>0</code></p>
		<?php
	}
}
