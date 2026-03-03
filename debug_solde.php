<?php
$file = 'c:/wamp64/www/COFTIME/resources/views/pages/conges/solde.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Show lines around 400-410
echo "=== Lines 398-415 ===\n";
for ($i = 397; $i <= 414; $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

// Also search for any unclosed brackets or parentheses
$openBracket = substr_count($content, '[');
$closeBracket = substr_count($content, ']');
$openParen = substr_count($content, '(');
$closeParen = substr_count($content, ')');

echo "\nTotal [ : $openBracket, ] : $closeBracket\n";
echo "Total ( : $openParen, ) : $closeParen\n";
