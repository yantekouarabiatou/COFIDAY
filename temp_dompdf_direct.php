<?php
require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$dompdf->loadHtml('<h1>test</h1>');
$dompdf->render();
$tmp='storage/app/temp/test_dom.pdf';
if (!is_dir(dirname($tmp))) mkdir(dirname($tmp),0755,true);
file_put_contents($tmp,$dompdf->output());
echo "size=".filesize($tmp)."\n";
$f=fopen($tmp,'rb');
fseek($f,-50,SEEK_END);
echo fread($f,50);
fclose($f);
