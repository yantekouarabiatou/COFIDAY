<?php
$lines = file('c:/wamp64/www/COFTIME/app/Http/Controllers/CongeController.php');
for ($i = 1125; $i <= 1135; $i++) {
    echo ($i + 1) . ': ' . $lines[$i] . PHP_EOL;
}
