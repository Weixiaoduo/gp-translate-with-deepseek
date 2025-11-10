#!/bin/bash

# Quick check script for GP Translate with DeepSeek
# Run from plugin directory

echo "=== GP Translate with DeepSeek - Quick Check ==="
echo ""

# Check if we're in the right directory
if [ ! -f "gp-translate-with-deepseek.php" ]; then
    echo "❌ Error: Please run this script from the plugin directory"
    exit 1
fi

echo "📁 Current directory: $(pwd)"
echo ""

# Check files
echo "=== File Check ==="
files=(
    "gp-translate-with-deepseek.php"
    "src/class-config.php"
    "src/class-translate.php"
    "src/class-frontend.php"
    "src/class-ajax.php"
    "assets/gpdeepseek_translate.js"
    "vendor/autoload.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file (MISSING)"
    fi
done
echo ""

# Check JavaScript file content
echo "=== JavaScript Check ==="
if grep -q "deepseek_translate" "assets/gpdeepseek_translate.js"; then
    echo "✅ JavaScript uses correct identifiers"
else
    echo "❌ JavaScript still uses old identifiers"
fi
echo ""

# Check PHP namespaces
echo "=== Namespace Check ==="
if grep -q "namespace Wenpai\\\\GpDeepseekTranslate" "src/class-translate.php"; then
    echo "✅ PHP classes use correct namespace"
else
    echo "❌ PHP classes use old namespace"
fi
echo ""

# Check vendor
echo "=== Composer Dependencies ==="
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    echo "✅ Composer dependencies installed"
    echo "   Packages:"
    if [ -f "vendor/composer/installed.json" ]; then
        cat vendor/composer/installed.json | grep -o '"name": "[^"]*"' | head -5
    fi
else
    echo "❌ Composer dependencies NOT installed"
    echo "   Run: composer install --no-dev"
fi
echo ""

echo "=== Next Steps ==="
echo "1. Visit: http://your-site.local/wp-content/plugins/gp-translate-with-deepseek/diagnostic.php"
echo "2. Activate plugin in WordPress admin"
echo "3. Add DeepSeek API key in Settings > GP Translate with DeepSeek"
echo "4. Go to GlotPress translation page and look for 'Translate with DeepSeek' button"
