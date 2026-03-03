<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Check lines around 706-710
echo "=== Lines 706-712 ===\n";
for ($i = 705; $i <= 711; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 784-792 ===\n";
for ($i = 783; $i <= 791; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 903-912 ===\n";
for ($i = 902; $i <= 911; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 933-942 ===\n";
for ($i = 932; $i <= 941; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== Lines 957-966 ===\n";
for ($i = 956; $i <= 965; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}
