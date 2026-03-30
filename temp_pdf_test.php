<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$pdf = Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>test</h1>');
$tmp = 'storage/app/temp/test_dom.pdf';
if (!is_dir(dirname($tmp))) mkdir(dirname($tmp), 0755, true);
$pdf->save($tmp);
echo 'size=' . filesize($tmp) . "\n";
$f = fopen($tmp,'rb');
fseek($f,-50,SEEK_END);
echo fread($f,50);
fclose($f);
