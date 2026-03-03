<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Check lines around 200-202 for missing commas
echo "=== Lines 198-205 ===\n";
for ($i = 197; $i <= 204; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 214-220 ===\n";
for ($i = 213; $i <= 219; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 238-245 ===\n";
for ($i = 237; $i <= 244; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 290-298 ===\n";
for ($i = 289; $i <= 297; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 448-455 ===\n";
for ($i = 447; $i <= 454; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}
