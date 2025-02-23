<?php

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

function conectarDB(InputInterface $input, OutputInterface $output, $showException = true) : \Doctrine\DBAL\Connection
{
    $io = new CvdwSymfonyStyle($input, $output);
    $config = new Configuration();
    $connectionParams = array(
        'dbname' => $_ENV['DB_DATABASE'],
        'user' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'driver' => $_ENV['DB_CONNECTION'],
    );


    if ($_ENV['DB_CONNECTION'] == 'pdo_pgsql') {
        $connectionParams['driverOptions'] = array(
            \PDO::ATTR_PERSISTENT => true,
        );
        $connectionParams['options'] = array(
            'search_path' => $_ENV['DB_SCHEMA']
        );
    }

    // Se connectionParams tiver algum dado vazio, retorna o objecto \Doctrine\DBAL\Connection
    foreach ($connectionParams as $key => $value) {
        if ($value == null) {
            return DriverManager::getConnection($connectionParams, $config);
        }
    }

    // Se connectionParams tiver algum dado vasío, com excecao de driver, na
    $conn = DriverManager::getConnection($connectionParams, $config);
    
    if (!$conn->isConnected()) {
        try {
            $conn->connect();

            if($_ENV['DB_CONNECTION'] == 'pdo_pgsql'){
                if(isset($_ENV['DB_SCHEMA']) && $_ENV['DB_SCHEMA'] <> ""){
                    $schema = $_ENV['DB_SCHEMA'];
                } else {
                    $schema = 'public';
                }
                $conn->executeQuery("CREATE SCHEMA IF NOT EXISTS $schema");
                $conn->executeQuery("SET search_path TO $schema");
            }

        } catch (\Exception $e) {
            if($showException){
                $io->error('Não foi possível conectar ao banco de dados. (3)');
                $io->error('Encontrei esse erro: ' . $e->getMessage());
            }
        }
    }
    return $conn;
}
