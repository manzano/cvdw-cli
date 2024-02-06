<?php
require __DIR__ . '/../vendor/autoload.php';

retornarEnvs();
require __DIR__ . '/../src/Conexao.php';
conectarDB();

use Doctrine\DBAL\Schema\Schema;

foreach(OBJETOS as $key => $dados){
    // ler json do arquivo $dados['arquivo'] e jogar o json decodificado em memoria
    $objeto = file_get_contents(__DIR__ . "/../src/objetos/{$dados['arquivo']}");
    $objeto = json_decode($objeto, true);

    // Criar uma tabela com o Doctrine DBAL

    $schema = new Schema();
    $myTable = $schema->createTable("{$key}");
    $i = 0;
    echo $key . " => ".count($objeto["dados"])."\n";
    foreach ($objeto["dados"] as $coluna => $especificacao) {
        
        if($i == 0){
            $myTable->addColumn("referencia", "integer", array("unsigned" => true, "notnull" => true));
            $myTable->setPrimaryKey(array("referencia"));
            $myTable->addColumn("data_referencia", "datetime", array("notnull" => true));
            $myTable->addIndex(array("data_referencia"), "data_referencia_idx");
            $myTable->addColumn("{$coluna}", "integer", array("unsigned" => true, "notnull" => true));
            $myTable->addUniqueIndex(array("{$coluna}"), "{$coluna}_idx");
            $i++;
            continue;
        }
        
        $opcoes = array();
        $opcoes["notnull"] = false;
        $opcoes["default"] = null;
        
        if(isset($especificacao['description'])){
            $opcoes["comment"] = $especificacao['description'];
        }
        if (strpos($coluna, "data") !== false) {
            $especificacao["type"] = "datetime";
        }
        if($especificacao["type"] == "int"){
            $especificacao["type"] = "integer";
        }
        if (isset($especificacao["type"]) && $especificacao["type"] == "integer") {
            $opcoes["unsigned"] = true;
        }
        if($especificacao["type"] == "string"){
            $opcoes["length"] = 255;
        }
        if($especificacao["type"] == "number"){
            $especificacao["type"] = "decimal";
            $opcoes["precision"] = 10;
            $opcoes["scale"] = 2;
        }
        $myTable->addColumn("{$coluna}", $especificacao["type"], $opcoes);
    }
    
    //$myTable->setPrimaryKey(array("id"));
    $platform = $conn->getDatabasePlatform();
    $queries = $schema->toSql($platform);
    foreach ($queries as $query) {
        //$query."\n\n";
        try {
            $conn->executeQuery($query);
        } catch (Exception $e) {
            echo $query."\n\n";
            echo $e->getMessage();
        }
    }

}