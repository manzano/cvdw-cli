<?php
    /*
        - Ler o diretorio ../../src/app/Objetos, somente arquivos .yaml
        - Ler o .yaml e gerar um arquivo .php com o mesmo nome com a primeira letra maiuscula cocatenado com Cest.php na pasta _temp
          Ex.: AssistenciasCest.php
        - O conteudo do arquivo .php deve ser o mesmo do arquivo _template.php
    */

    include '../../vendor/autoload.php';

    $dir = '../../src/app/Objetos';
    $files = scandir($dir);
    $files = array_diff($files, array('.', '..'));

    // Remover todos os arquivos de _temp
    $temp = scandir('./');
    $temp = array_diff($temp, array('.', '..','Common.php','_template.tmp','_gerar.php'));
    foreach ($temp as $file) {
        // Se for Arquivo
        if (is_file($file)) {
            unlink($file);
        }
    }

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        $info = pathinfo($path);
        if ($info['extension'] == 'yaml') {
            // Separar o nome por _ e deixar a primeira letra maiuscula e concatenar todas com Cest.php
            
            // Converter o yaml com o parser do Symfony
            $yaml = file_get_contents($path);
            echo $info['filename'] . PHP_EOL;
            $yaml = \Symfony\Component\Yaml\Yaml::parse($yaml);
            
            

            $name = explode('_', $info['filename']);
            $name = array_map('ucfirst', $name);
            $name = implode('', $name);
            $content = file_get_contents('_template.tmp');

            $content = str_replace('{{NOME_CLASSE}}', $name, $content);
            $content = str_replace('{{PATH}}', $yaml['path'], $content);

            //print_r($yaml['response']);

            $retornoGeral = [];
            foreach ($yaml['response'] as $key => $value) {
                if(!isset($value['type'])){
                    $value['type'] = "array";
                }
                $retornoGeral[] = "'$key' => '".$value['type']."'";
            }
            $retornoGeral = implode(",\n            ", $retornoGeral);
            $content = str_replace('{{RETORNO_GERAL}}', $retornoGeral, $content);

            $retornoDados = [];
            foreach ($yaml['response']['dados'] as $key => $value) {
                if($key == 'referencia'){
                    $value['type'] = 'integer|string';
                }
                if (!isset($value['type'])) {
                    $value['type'] = "array";
                }
                $null = '';
                $naoNulos = ['referencia', 'referencia_data'];
                if(!in_array($key, $naoNulos)){
                    $null = '|null';
                }
                $retornoDados[] = "'$key' => '" . $value['type'] . "{$null}'";
            }
            $retornoDados = implode(",\n            ", $retornoDados);
            $content = str_replace('{{RETORNO_DADOS}}', $retornoDados, $content);


            file_put_contents($name . 'Cest.php', $content);
        }
    }

    echo 'Arquivos gerados com sucesso!';