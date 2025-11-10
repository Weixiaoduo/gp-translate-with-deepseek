=== GP Translate with DeepSeek ===
Contributors: WenPai
Tags: glotpress, translate, machine translate, deepseek, ai
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A DeepSeek AI translate plugin for GlotPress as a WordPress plugin.

== Description ==

A DeepSeek AI translate plugin for [GlotPress as a WordPress plugin](https://wordpress.org/plugins/glotpress/).

DeepSeek offers powerful AI models with competitive pricing and performance. This plugin integrates DeepSeek's translation capabilities directly into your GlotPress workflow.

Note: This plugin assumes the source language is English as support for automated translation from other source languages is limited.

= Configuration =

Once you have installed GP Translate with DeepSeek, go to your WordPress admin screen and select "Settings > GP Translate with DeepSeek".

You will have few fields to configure:

	1. Global API Key
	2. DeepSeek Model (deepseek-chat or deepseek-reasoner)
	3. Temperature (where lower values indicating greater determinism and higher values indicating more randomness)
	4. Custom Prompt (if you would like to adjust what the AI is returning, eg. glossary tips)

Each user can adjust, change these configuration on his profile page.

= DeepSeek API =

* Login/signup [DeepSeek Platform](https://platform.deepseek.com/)
* Go to your account and navigate to [API Keys](https://platform.deepseek.com/api_keys)
* Create new API key and put it into `Global API Key` of GP Translate with DeepSeek.

= Available Models =

* **deepseek-chat**: General-purpose translation model for everyday use
* **deepseek-reasoner**: Advanced reasoning model for complex translations requiring deeper context understanding

= Setting the API key =

To set the API key for all users, go to the WordPress Dashboard, then Settings, then "GP Translate with DeepSeek" and set the API key.

To set if for a specific user, go to the users profile and scroll down to the "GP Translate with DeepSeek" section and set the API key.

Note, if both a global and user API key are set, the user API key will override the global API key.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/gp-translate-with-deepseek` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Run `composer install` in the plugin directory to install dependencies
4. Configure the plugin through Settings > GP Translate with DeepSeek
5. Add your DeepSeek API key


== Changelog ==

= 1.0.0 =
* Initial release.
* Support for DeepSeek Chat API integration.
* Two model options: deepseek-chat and deepseek-reasoner.
* Individual and bulk translation support.
* Per-user configuration override capability.
