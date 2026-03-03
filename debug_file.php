<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);
$tokens = token_get_all($content);

foreach ($tokens as $i => $token) {
    if (is_array($token) && $token[0] === T_STRING) {
        echo "Line {$token[2]}: T_STRING => {$token[1]}" . PHP_EOL;
    }
}

// Also try to get specific line 1128
$lines = explode("\n", $content);
echo "Line 1128: " . ($lines[1127] ?? 'NOT FOUND') . PHP_EOL;
