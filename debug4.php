<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Let's look at the match expression in show() method
// Find the line number where 'match (' appears
for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'match (') !== false || strpos($lines[$i], 'match(') !== false) {
        echo "Line " . ($i + 1) . ": " . $lines[$i] . "\n";
        // Show surrounding lines
        for ($j = max(0, $i - 2); $j <= min(count($lines) - 1, $i + 10); $j++) {
            echo "  " . ($j + 1) . ": " . $lines[$j] . "\n";
        }
        echo "\n";
    }
}

// Check around line 501-510 specifically
echo "=== Lines 495-515 ===\n";
for ($i = 494; $i <= 515; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}
