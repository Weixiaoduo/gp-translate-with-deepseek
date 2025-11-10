<?php
/**
 * Diagnostic script for GP Translate with DeepSeek
 *
 * Usage: Visit this file directly in your browser:
 * http://your-site.local/wp-content/plugins/gp-translate-with-deepseek/diagnostic.php
 *
 * Or run via WP-CLI:
 * wp eval-file diagnostic.php
 */

// Load WordPress
require_once __DIR__ . '/../../../../wp-load.php';

// Check if user is logged in and has appropriate permissions
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please log in as an administrator.');
}

echo "<h1>GP Translate with DeepSeek - Diagnostic Report</h1>";
echo "<pre>";

// 1. Check if plugin is active
echo "=== PLUGIN STATUS ===\n";
$active_plugins = get_option('active_plugins');
$is_active = in_array('gp-translate-with-deepseek/gp-translate-with-deepseek.php', $active_plugins);
echo "Plugin Active: " . ($is_active ? "✅ YES" : "❌ NO - Please activate the plugin!") . "\n\n";

// 2. Check API Key
echo "=== API KEY CONFIGURATION ===\n";
$api_key = get_option('gpdeepseek_api_key');
if ($api_key) {
    echo "Global API Key: ✅ SET (" . substr($api_key, 0, 10) . "...)\n";
} else {
    echo "Global API Key: ❌ NOT SET - Please add your DeepSeek API key in Settings > GP Translate with DeepSeek\n";
}

// Check user API key
$user_id = get_current_user_id();
$user_api_key = get_user_meta($user_id, 'gpdeepseek_api_key', true);
if ($user_api_key) {
    echo "User API Key: ✅ SET (" . substr($user_api_key, 0, 10) . "...)\n";
} else {
    echo "User API Key: ℹ️  Not set (will use global key)\n";
}
echo "\n";

// 3. Check Model Configuration
echo "=== MODEL CONFIGURATION ===\n";
$model = get_option('gpdeepseek_model');
echo "Model: " . ($model ? $model : "Not set (will use default: deepseek-chat)") . "\n";

$temperature = get_option('gpdeepseek_temperature');
echo "Temperature: " . ($temperature ? $temperature : "Not set (will use default: 0)") . "\n";

$custom_prompt = get_option('gpdeepseek_custom_prompt');
echo "Custom Prompt: " . ($custom_prompt ? "✅ SET" : "ℹ️  Not set") . "\n\n";

// 4. Check GlotPress
echo "=== GLOTPRESS STATUS ===\n";
if (class_exists('GP')) {
    echo "GlotPress: ✅ ACTIVE\n";
} else {
    echo "GlotPress: ❌ NOT ACTIVE - Please install and activate GlotPress plugin!\n";
}
echo "\n";

// 5. Check required files
echo "=== FILE CHECK ===\n";
$required_files = [
    'Main plugin file' => __DIR__ . '/gp-translate-with-deepseek.php',
    'Config class' => __DIR__ . '/src/class-config.php',
    'Translate class' => __DIR__ . '/src/class-translate.php',
    'Frontend class' => __DIR__ . '/src/class-frontend.php',
    'Ajax class' => __DIR__ . '/src/class-ajax.php',
    'JavaScript file' => __DIR__ . '/assets/gpdeepseek_translate.js',
    'Vendor autoload' => __DIR__ . '/vendor/autoload.php',
];

foreach ($required_files as $name => $path) {
    echo "$name: " . (file_exists($path) ? "✅ EXISTS" : "❌ MISSING - Run 'composer install'") . "\n";
}
echo "\n";

// 6. Check namespace and class loading
echo "=== CLASS LOADING ===\n";
if (class_exists('Wenpai\\GpDeepseekTranslate\\Config')) {
    echo "Config class: ✅ LOADED\n";
} else {
    echo "Config class: ❌ NOT LOADED\n";
}

if (class_exists('Wenpai\\GpDeepseekTranslate\\Translate')) {
    echo "Translate class: ✅ LOADED\n";
} else {
    echo "Translate class: ❌ NOT LOADED\n";
}

if (class_exists('Wenpai\\GpDeepseekTranslate\\Frontend')) {
    echo "Frontend class: ✅ LOADED\n";
} else {
    echo "Frontend class: ❌ NOT LOADED\n";
}
echo "\n";

// 7. Check WordPress hooks
echo "=== WORDPRESS HOOKS ===\n";
global $wp_filter;

// Check if frontend hooks are registered
$hooks_to_check = [
    'gp_pre_tmpl_load',
    'gp_entry_actions',
    'gp_translation_set_bulk_action',
    'gp_translation_set_bulk_action_post',
    'wp_ajax_gpdeepseek_translate',
];

foreach ($hooks_to_check as $hook) {
    if (isset($wp_filter[$hook])) {
        echo "$hook: ✅ REGISTERED\n";
    } else {
        echo "$hook: ❌ NOT REGISTERED\n";
    }
}
echo "\n";

// 8. Test API connection (if API key is set)
echo "=== API CONNECTION TEST ===\n";
if ($api_key || $user_api_key) {
    echo "Testing DeepSeek API connection...\n";

    // Use the actual API key logic
    $test_api_key = $user_api_key ? $user_api_key : $api_key;

    try {
        require_once __DIR__ . '/vendor/autoload.php';
        $openai = new \Orhanerday\OpenAi\OpenAi($test_api_key);
        $openai->setBaseURL('https://api.deepseek.com/v1');

        $response = $openai->chat([
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => 'Translate to Chinese: Hello']
            ],
            'max_tokens' => 50,
        ]);

        $result = json_decode($response);

        if (isset($result->choices[0]->message->content)) {
            echo "✅ API Connection: SUCCESS\n";
            echo "Test translation result: " . $result->choices[0]->message->content . "\n";
        } elseif (isset($result->error)) {
            echo "❌ API Error: " . $result->error->message . "\n";
            echo "Error code: " . ($result->error->code ?? 'unknown') . "\n";
        } else {
            echo "⚠️  Unexpected API response\n";
        }
    } catch (Exception $e) {
        echo "❌ API Connection Failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "⏭️  Skipping API test - No API key configured\n";
}
echo "\n";

// 9. Recommendations
echo "=== RECOMMENDATIONS ===\n";
$issues = [];

if (!$is_active) {
    $issues[] = "Activate the plugin from WordPress admin panel";
}

if (!$api_key && !$user_api_key) {
    $issues[] = "Add your DeepSeek API key in Settings > GP Translate with DeepSeek";
}

if (!class_exists('GP')) {
    $issues[] = "Install and activate GlotPress plugin";
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    $issues[] = "Run 'composer install --no-dev' in the plugin directory";
}

if (empty($issues)) {
    echo "✅ All checks passed! The plugin should be working correctly.\n";
    echo "\nℹ️  If you still don't see the translation options in GlotPress:\n";
    echo "   1. Make sure you're on a translation page (not projects or sets list)\n";
    echo "   2. Clear your browser cache and reload the page\n";
    echo "   3. Check that the target language is in the supported locales list\n";
    echo "   4. Open browser console (F12) and check for JavaScript errors\n";
} else {
    echo "⚠️  Issues found:\n";
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". $issue\n";
    }
}

echo "</pre>";
