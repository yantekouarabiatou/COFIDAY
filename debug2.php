<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Count occurrences of 'compact('
$count = substr_count($content, 'compact(');
echo "Number of 'compact(' occurrences: $count\n";

// Check around line 1126-1135 specifically
for ($i = 1125; $i <= 1135; $i++) {
    echo "Line " . ($i + 1) . ": [" . strlen($lines[$i]) . " chars] " . $lines[$i] . "\n";
}
