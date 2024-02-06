<?php

// Substitua o caminho abaixo pelo caminho do seu arquivo .phar
$pharFile = 'build/cvdw.phar';

$phar = new Phar($pharFile);
foreach (new RecursiveIteratorIterator($phar) as $file) {
    // Obtém o caminho do arquivo relativo ao phar
    // Se $file contiver /vendor/ em seu caminho, ele será ignorado
    if (strpos($file, '/vendor/') !== false) {
        continue;
    }
    echo str_replace('phar://' . $pharFile . '/', '', $file) . PHP_EOL;
}