<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Look for any unusual syntax patterns

// 1. Find all lines with =>
echo "=== All lines with => ===\n";
for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], '=>') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
    }
}
