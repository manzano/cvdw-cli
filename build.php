<?php

// Remover todo os arquivos do diretório build
$files = glob(__DIR__ . '/build/*'); // get all file names
foreach($files as $file){ // iterate files
    if(is_file($file)) {
        unlink($file); // delete file
    }
}

chmod(__DIR__ . '/build', 0755);

//shell_exec("php src/cvdw completion > src/completion.sh && chmod +x src/completion.sh");
//shell_exec("source src/completion.sh");

try {
    $phar = new Phar('build/cvdw.phar', 0, 'cvdw.phar');

    // Define os itens a serem excluídos (como caminhos relativos)
    $exclusions = [
        '.env',
        'build.php',
        'build/',
        'producao/',
        'monitor.py',
        '.git/',
        '.idea/',
        'composer.json',
        'composer.lock',
        'src/completion.sh',
        'phpstan.neon',
        'README.md',
    ];

    // Diretório base para os arquivos
    $baseDir = __DIR__;

    // Cria um objeto RecursiveDirectoryIterator para iterar sobre os arquivos no diretório base
    $directory = new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS);

    // Cria um objeto RecursiveIteratorIterator para iterar recursivamente
    $iterator = new RecursiveIteratorIterator($directory);

    // Cria um filtro para excluir os itens definidos no array $exclusions
    $filtered = new CallbackFilterIterator($iterator, function ($file) use ($exclusions, $baseDir) {
        // Transforma o caminho absoluto em relativo
        $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file->getPathname());

        // Verifica se o arquivo atual ou seu diretório pai está na lista de exclusões
        foreach ($exclusions as $exclusion) {
            if (strpos($relativePath, $exclusion) === 0) {
                return false;
            }
        }
        return true;
    });

    // Constrói o arquivo .phar usando o iterador filtrado e especificando o diretório base
    $phar->buildFromIterator($filtered, $baseDir);

    $phar->setDefaultStub('src/cvdw', 'src/cvdw');
    $phar->stopBuffering();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

chmod('build/cvdw.phar', 0755);
chmod('build/', 0755);

echo "\033[0;32mCVDW - Build complete!\033[0m\n";
