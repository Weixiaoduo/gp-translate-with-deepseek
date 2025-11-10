<?php
/**
 * Locales class file.
 *
 * @package Wenpai\GpDeepseekTranslate
 */

namespace Wenpai\GpDeepseekTranslate;

/**
 * Locales class.
 */
class Locales {

	/**
	 * Is the locale supported?
	 *
	 * @param string $locale The locale to check.
	 *
	 * @return bool
	 */
	public static function is_supported( string $locale ): bool {
		$locales = self::get_data();

		// Check exact match first
		if ( in_array( $locale, $locales, true ) ) {
			return true;
		}

		// Check if the base language is supported (e.g., zh-cn -> zh)
		$base_locale = substr( $locale, 0, 2 );
		if ( in_array( $base_locale, $locales, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the supported locales.
	 *
	 * @see https://api-docs.deepseek.com/
	 *
	 * @return array
	 */
	public static function get_data(): array {
		$locales = array(
			'af', // Afrikaans.
			'ar', // Arabic.
			'hy', // Armenian.
			'az', // Azerbaijani.
			'be', // Belarusian.
			'bs', // Bosnian.
			'bg', // Bulgarian.
			'ca', // Catalan.
			'zh', // Chinese.
			'hr', // Croatian.
			'cs', // Czech.
			'da', // Danish.
			'nl', // Dutch.
			'en', // English.
			'et', // Estonian.
			'fi', // Finnish.
			'fr', // French.
			'gl', // Galician.
			'de', // German.
			'el', // Greek.
			'he', // Hebrew.
			'hi', // Hindi.
			'hu', // Hungarian.
			'is', // Icelandic.
			'id', // Indonesian.
			'it', // Italian.
			'ja', // Japanese.
			'kn', // Kannada.
			'kk', // Kazakh.
			'ko', // Korean.
			'lv', // Latvian.
			'lt', // Lithuanian.
			'mk', // Macedonian.
			'ms', // Malay.
			'mr', // Marathi.
			'mi', // Maori.
			'ne', // Nepali.
			'no', // Norwegian.
			'fa', // Persian.
			'pl', // Polish.
			'pt', // Portuguese.
			'ro', // Romanian.
			'ru', // Russian.
			'sr', // Serbian.
			'sk', // Slovak.
			'sl', // Slovenian.
			'es', // Spanish.
			'sw', // Swahili.
			'sv', // Swedish.
			'tl', // Tagalog.
			'ta', // Tamil.
			'th', // Thai.
			'tr', // Turkish.
			'uk', // Ukrainian.
			'ur', // Urdu.
			'vi', // Vietnamese.
			'cy', // Welsh.
		);

		return $locales;
	}
}
