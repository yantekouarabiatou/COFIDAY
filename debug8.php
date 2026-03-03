<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Show hex dump of line 1128 to see if there are any hidden characters
$line1128 = $lines[1127];
echo "Line 1128 raw: " . var_export($line1128, true) . "\n";
echo "Line 1128 hex: ";
for ($i = 0; $i < strlen($line1128); $i++) {
    printf("%02X ", ord($line1128[$i]));
}
echo "\n";

// Also check the previous line
$line1127 = $lines[1126];
echo "\nLine 1127 raw: " . var_export($line1127, true) . "\n";

// Check the next few lines
for ($i = 1127; $i <= 1132; $i++) {
    echo "Line " . ($i + 1) . " length: " . strlen($lines[$i]) . "\n";
}
