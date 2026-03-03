<?php
$file = 'c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php';
$content = file_get_contents($file);

// Search for where the problem might be - look for unclosed brackets/parentheses before line 1128
$lines = explode("\n", $content);

// Look for function definitions around line 1050-1128
for ($i = 1050; $i <= 1128; $i++) {
    $line = $lines[$i] ?? '';
    // Look for function or method definitions
    if (preg_match('/function\s+\w+/', $line) || preg_match('/public\s+function/', $line)) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}

// Also try to parse the file content to find the exact error
echo "\nTrying to find syntax issues...\n";

// Look for the word 'function' and count braces
$funcContent = substr($content, 0, strpos($content, 'return view(\'pages.conges.solde\''));
$openBrace = substr_count($funcContent, '{');
$closeBrace = substr_count($funcContent, '}');
echo "Before line 1126 - Open braces: $openBrace, Close braces: $closeBrace\n";

// Check for anonymous functions (closures)
$closures = preg_match_all('/function\s*\(/', $funcContent);
echo "Number of closures in file: $closures\n";
