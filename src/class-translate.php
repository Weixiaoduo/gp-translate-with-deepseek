<?php
/**
 * Translate class file.
 *
 * @package Wenpai\GpDeepseekTranslate
 */

namespace Wenpai\GpDeepseekTranslate;

use GP;
use GP_Locales;
use WP_Error;

/**
 * Translate class.
 */
class Translate {

	/**
	 * Singleton instance.
	 *
	 * @var Translate
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Translate
	 */
	public static function instance(): Translate {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * This function is used to bulk translate a set of strings.
	 *
	 * @param string|object $locale The locale to translate to.
	 * @param array         $strings The strings to translate.
	 *
	 * @return array|WP_Error The translated strings or an error.
	 */
	public function translate_batch( $locale, $strings ) {
		if ( is_object( $locale ) ) {
			$locale = $locale->slug;
		}

		return $this->deepseek_translate_batch( $locale, $strings );
	}

	/**
	 * Translate the text (Source language is always English).
	 *
	 * @param string $text   The text to translate.
	 * @param string $locale The locale to translate to.
	 *
	 * @return string
	 */
	public function translate( string $text, string $locale ) {
		// Check if the locale is supported.
		if ( ! Locales::is_supported( $locale ) ) {
			return new WP_Error( 'gpdeepseek_locale_not_supported', sprintf( 'Locale %s is not supported.', $locale ) );
		}

		$api_key = Config::get_api_key();
		if ( empty( $api_key ) ) {
			return new WP_Error( 'gpdeepseek_no_api_key', 'DeepSeek API key is not configured.' );
		}

		// get locale object.
		$locale_obj = GP_Locales::by_slug( $locale );
		if ( ! $locale_obj ) {
			return new WP_Error( 'gp_set_no_locale', 'Locale not found in GlotPress!' );
		}

		// get prompt.
		$base_prompt   = sprintf( 'Translate the following text to %s language: ', $locale_obj->english_name );
		$custom_prompt = Config::get_custom_prompt();
		$prompt        = $custom_prompt . ' ' . $base_prompt . ' ' . $text;

		// build request body.
		$body = array(
			'model'             => Config::get_model() ?: 'deepseek-chat',
			'messages'          => array(
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'temperature'       => Config::get_temperature() ?: 0,
			'max_tokens'        => 1000,
			'frequency_penalty' => 0,
			'presence_penalty'  => 0,
		);

		// Make API request using WordPress HTTP API.
		$response = wp_remote_post(
			'https://api.deepseek.com/v1/chat/completions',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		// Check for HTTP errors.
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'gpdeepseek_http_error', $response->get_error_message() );
		}

		// Get response body.
		$response_body = wp_remote_retrieve_body( $response );
		$response_code = wp_remote_retrieve_response_code( $response );

		// Check HTTP status code.
		if ( $response_code !== 200 ) {
			return new WP_Error(
				'gpdeepseek_http_error',
				sprintf( 'HTTP Error %d: %s', $response_code, $response_body ),
				$response_code
			);
		}

		// Decode JSON response.
		$data = json_decode( $response_body );

		// Check for JSON decode errors.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'gpdeepseek_json_error', 'Failed to decode API response: ' . json_last_error_msg() );
		}

		// Check for API errors.
		if ( isset( $data->error ) ) {
			$error_message = isset( $data->error->message ) ? $data->error->message : 'Unknown API error';
			$error_code    = isset( $data->error->code ) ? $data->error->code : 'unknown';
			return new WP_Error( 'gpdeepseek_api_error', $error_message, $error_code );
		}

		// Check if response is valid.
		if ( ! isset( $data->choices ) || ! is_array( $data->choices ) || empty( $data->choices ) ) {
			return new WP_Error( 'gpdeepseek_invalid_response', 'Invalid response structure from DeepSeek API' );
		}

		// Get translation.
		$translation = isset( $data->choices[0]->message->content ) ? $data->choices[0]->message->content : '';

		// Check if translation is empty.
		if ( empty( $translation ) ) {
			return new WP_Error( 'gpdeepseek_empty_translation', 'Empty translation received from DeepSeek' );
		}

		return $translation;
	}

	/**
	 * This function is used to bulk translate a set of strings using DeepSeek.
	 *
	 * @param string $locale The locale to translate to.
	 * @param array  $strings The strings to translate.
	 *
	 * @return array|WP_Error The translated strings or an error.
	 */
	protected function deepseek_translate_batch( $locale, $strings ) {
		if ( ! Locales::is_supported( $locale ) ) {
			return new WP_Error( 'gpdeepseek_translate', sprintf( "The locale %s isn't supported by DeepSeek.", $locale ) );
		}

		// If we don't have any strings, throw an error.
		if ( count( $strings ) === 0 ) {
			return new WP_Error( 'gpdeepseek_translate', 'No strings found to translate.' );
		}

		// If we have too many strings, process in chunks.
		if ( count( $strings ) > 100 ) {
			return new WP_Error( 'gpdeepseek_translate', 'Maximum 100 strings allowed per batch.' );
		}

		// For large batches (>20), process in chunks.
		if ( count( $strings ) > 20 ) {
			return $this->translate_in_chunks( $locale, $strings, 20 );
		}

		// For small batches (≤20), translate directly.
		return $this->translate_single_chunk( $locale, $strings );
	}

	/**
	 * Translates strings in chunks to handle large batches.
	 *
	 * @param string $locale The locale to translate to.
	 * @param array  $strings The strings to translate.
	 * @param int    $chunk_size The size of each chunk.
	 *
	 * @return array|WP_Error The translated strings or an error.
	 */
	protected function translate_in_chunks( $locale, $strings, $chunk_size = 20 ) {
		$chunks           = array_chunk( $strings, $chunk_size );
		$all_translations = array();
		$total_chunks     = count( $chunks );

		foreach ( $chunks as $chunk_index => $chunk ) {
			// Translate this chunk.
			$chunk_translations = $this->translate_single_chunk( $locale, $chunk );

			// Check for errors.
			if ( is_wp_error( $chunk_translations ) ) {
				// On error, use original strings for this chunk.
				$all_translations = array_merge( $all_translations, $chunk );
			} else {
				// Merge successful translations.
				$all_translations = array_merge( $all_translations, $chunk_translations );
			}

			// Add a small delay between chunks to avoid rate limiting (except for last chunk).
			if ( $chunk_index < $total_chunks - 1 ) {
				sleep( 1 );
			}
		}

		return $all_translations;
	}

	/**
	 * Translates a single chunk of strings.
	 *
	 * @param string $locale The locale to translate to.
	 * @param array  $strings The strings to translate.
	 *
	 * @return array|WP_Error The translated strings or an error.
	 */
	protected function translate_single_chunk( $locale, $strings ) {
		// Get locale object.
		$locale_obj = GP_Locales::by_slug( $locale );
		if ( ! $locale_obj ) {
			return new WP_Error( 'gp_set_no_locale', 'Locale not found in GlotPress!' );
		}

		$api_key = Config::get_api_key();
		if ( empty( $api_key ) ) {
			return new WP_Error( 'gpdeepseek_no_api_key', 'DeepSeek API key is not configured.' );
		}

		// Build numbered list for batch translation.
		$numbered_strings = array();
		foreach ( $strings as $index => $string ) {
			$numbered_strings[] = ( $index + 1 ) . '. ' . $string;
		}
		$batch_text = implode( "\n", $numbered_strings );

		// Build prompt for batch translation.
		$custom_prompt = Config::get_custom_prompt();
		$prompt        = sprintf(
			"%s\n\nTranslate the following numbered texts to %s language. Keep the numbers and format:\n\n%s\n\nProvide translations in the same numbered format.",
			$custom_prompt,
			$locale_obj->english_name,
			$batch_text
		);

		// Build request body.
		$body = array(
			'model'             => Config::get_model() ?: 'deepseek-chat',
			'messages'          => array(
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'temperature'       => Config::get_temperature() ?: 0,
			'max_tokens'        => 4000,
			'frequency_penalty' => 0,
			'presence_penalty'  => 0,
		);

		// Make API request.
		$response = wp_remote_post(
			'https://api.deepseek.com/v1/chat/completions',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 60,
			)
		);

		// Check for HTTP errors.
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'gpdeepseek_http_error', $response->get_error_message() );
		}

		// Get response body.
		$response_body = wp_remote_retrieve_body( $response );
		$response_code = wp_remote_retrieve_response_code( $response );

		// Check HTTP status code.
		if ( $response_code !== 200 ) {
			return new WP_Error(
				'gpdeepseek_http_error',
				sprintf( 'HTTP Error %d', $response_code ),
				$response_code
			);
		}

		// Decode JSON response.
		$data = json_decode( $response_body );

		// Check for JSON decode errors.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'gpdeepseek_json_error', 'Failed to decode API response' );
		}

		// Check for API errors.
		if ( isset( $data->error ) ) {
			$error_message = isset( $data->error->message ) ? $data->error->message : 'Unknown API error';
			return new WP_Error( 'gpdeepseek_api_error', $error_message );
		}

		// Check if response is valid.
		if ( ! isset( $data->choices[0]->message->content ) ) {
			return new WP_Error( 'gpdeepseek_invalid_response', 'Invalid response structure' );
		}

		// Parse the batch response.
		$batch_response = $data->choices[0]->message->content;
		$translations   = $this->parse_batch_response( $batch_response, count( $strings ) );

		// If parsing failed, fallback to individual translation.
		if ( is_wp_error( $translations ) ) {
			// Fallback: translate one by one.
			$translations = array();
			foreach ( $strings as $string ) {
				$result = $this->translate( $string, $locale );
				if ( is_wp_error( $result ) ) {
					$translations[] = $string; // Keep original on error.
				} else {
					$translations[] = $this->clean_translation( $result );
				}
			}
		} else {
			// Clean up translations.
			foreach ( $translations as $index => $translation ) {
				$translations[ $index ] = $this->clean_translation( $translation );
			}
		}

		return $translations;
	}

	/**
	 * Cleans up the translation string.
	 *
	 * @param string $text The string to clean.
	 *
	 * @return string
	 */
	protected function parse_batch_response( $response_text, $expected_count ) {
		// Parse numbered list from AI response.
		$lines       = explode( "\n", trim( $response_text ) );
		$translations = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );
			// Match pattern: "1. Translation" or "1) Translation" or "1: Translation".
			if ( preg_match( '/^(\d+)[.):]\s*(.+)$/', $line, $matches ) ) {
				$translations[] = trim( $matches[2] );
			}
		}

		// Verify we got all translations.
		if ( count( $translations ) !== $expected_count ) {
			return new WP_Error(
				'gpdeepseek_parse_error',
				sprintf(
					'Expected %d translations but got %d. Will fallback to individual translation.',
					$expected_count,
					count( $translations )
				)
			);
		}

		return $translations;
	}

	protected function clean_translation( $text ) {
		// Skip cleaning for WP_Error objects.
		if ( is_wp_error( $text ) ) {
			return $text;
		}

		$text = preg_replace_callback(
			'/% (s|d)/i',
			function ( $m ) { // phpcs:ignore
				return '"%".strtolower($m[1])';
			},
			$text
		);
		$text = preg_replace_callback(
			'/% (\d+) \$ (s|d)/i',
			function ( $m ) { // phpcs:ignore
				return '"%".$m[1]."\\$".strtolower($m[2])';
			},
			$text
		);

		return $text;
	}

	/**
	 * Handles bulk translation action.
	 *
	 * @param object $project The current project object.
	 * @param object $locale The current locale object.
	 * @param object $translation_set The current translation set object.
	 * @param array  $bulk The current bulk action array.
	 *
	 * @return void
	 */
	public function gp_translation_set_bulk_action_post( $project, $locale, $translation_set, $bulk ) {
		// Status counters.
		$count            = array();
		$count['err_api'] = 0;
		$count['err_add'] = 0;
		$count['added']   = 0;
		$count['skipped'] = 0;

		$singulars    = array();
		$original_ids = array();

		// Loop through each of the passed in strings and translate them.
		foreach ( $bulk['row-ids'] as $row_id ) {
			// Split the $row_id by '-' and get the first one (which will be the id of the original).
			$original_id = gp_array_get( explode( '-', $row_id ), 0 );
			// Get the original based on the above id.
			$original = GP::$original->get( $original_id );

			// If there is no original or it's a plural, skip it.
			if ( ! $original || $original->plural ) {
				++$count['skipped'];
				continue;
			}

			// Add the original to the queue to translate.
			$singulars[]    = $original->singular;
			$original_ids[] = $original_id;
		}

		// Translate all the originals that we found.
		// $translate = Translate::instance();.
		$results = $this->translate_batch( $locale, $singulars );

		// Did we get an error?
		if ( is_wp_error( $results ) ) {
			gp_notice_set( $results->get_error_message(), 'error' );
			return;
		}

		// Merge the results back in to the original id's and singulars
		// This will create an array like ($items = array( array( id, single, result), array( id, single, result), ... ).
		$items = gp_array_zip( $original_ids, $singulars, $results );

		// If we have no items, something went wrong and stop processing.
		if ( ! $items ) {
			return;
		}

		// Loop through the items and store them in the database.
		foreach ( $items as $item ) {
			// Break up the item back in to individual components.
			list( $original_id, $singular, $translation ) = $item;

			// Did we get an error?
			if ( is_wp_error( $translation ) ) {
				++$count['err_api'];
				continue;
			}

			$warnings = GP::$translation_warnings->check( $singular, null, array( $translation ), $locale );

			// Build a data array to store.
			$data                       = array();
			$data['original_id']        = $original_id;
			$data['user_id']            = get_current_user_id();
			$data['translation_set_id'] = $translation_set->id;
			$data['translation_0']      = $translation;
			$data['status']             = 'fuzzy';
			$data['warnings']           = $warnings;

			// Insert the item in to the database.
			$inserted = GP::$translation->create( $data );
			if ( $inserted ) {
				++$count['added'];
			} else {
				++$count['err_add'];
			}
		}

		$this->set_bulk_action_notice( $count );
	}

	/**
	 * Set notice for bulk action.
	 *
	 * @param array $count The count array.
	 *
	 * @return void
	 */
	protected function set_bulk_action_notice( $count ) {
		// If there are no errors, display how many translations were added.
		if ( 0 === $count['err_api'] && 0 === $count['err_add'] ) {
			// translators: %d is the number of translations added.
			gp_notice_set( sprintf( __( '%d fuzzy translation from DeepSeek were added.', 'gp-translate-with-deepseek' ), $count['added'] ) );
			return;
		}

		$messages = array();

		if ( $count['added'] ) {
			// translators: %d is the number of translations added.
			$messages[] = sprintf( __( 'Added: %d.', 'gp-translate-with-deepseek' ), $count['added'] );
		}

		if ( $count['err_api'] ) {
			// translators: %d is the number of errors from DeepSeek.
			$messages[] = sprintf( __( 'Error from DeepSeek: %d.', 'gp-translate-with-deepseek' ), $count['err_api'] );
		}

		if ( $count['err_add'] ) {
			// translators: %d is the number of errors adding translations.
			$messages[] = sprintf( __( 'Error adding: %d.', 'gp-translate-with-deepseek' ), $count['err_add'] );
		}

		if ( $count['skipped'] ) {
			// translators: %d is the number of skipped translations.
			$messages[] = sprintf( __( 'Skipped: %d.', 'gp-translate-with-deepseek' ), $count['skipped'] );
		}

		// Create a message string and add it to the GlotPress notices.
		gp_notice_set( implode( ' ', $messages ), 'error' );
	}
}
