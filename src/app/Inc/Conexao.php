<?php

namespace Manzano\CvdwCli\Inc;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\EnvironmentManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Conexao
{
    public static function conectarDB(InputInterface $input, OutputInterface $output, bool $showException = true, ?EnvironmentManager $environmentManager = null): \Doctrine\DBAL\Connection
    {
        $console = new CvdwSymfonyStyle($input, $output);
        $config = new Configuration();

        if ($environmentManager === null) {
            $environmentManager = new EnvironmentManager();
        }

        $connectionParams = [
            'dbname' => $environmentManager->getDbDatabase(),
            'user' => $environmentManager->getDbUsername(),
            'password' => $environmentManager->getDbPassword(),
            'host' => $environmentManager->getDbHost(),
            'port' => (int) $environmentManager->getDbPort(),
            'driver' => $environmentManager->getDbConnection(),
        ];

        if ($environmentManager->getDbConnection() == 'pdo_pgsql') {
            $connectionParams['driverOptions'] = [
                \PDO::ATTR_PERSISTENT => true,
            ];
            $connectionParams['options'] = [
                'search_path' => $environmentManager->getDbSchema(),
            ];
        }

        foreach ($connectionParams as $_ => $value) {
            if ($value == null) {
                return DriverManager::getConnection($connectionParams, $config);
            }
        }

        $conn = DriverManager::getConnection($connectionParams, $config);

        if (! $conn->isConnected()) {
            try {
                $conn->connect();

                if ($environmentManager->getDbConnection() == 'pdo_pgsql') {
                    if ($environmentManager->has('DB_SCHEMA') && $environmentManager->getDbSchema() <> "") {
                        $schema = $environmentManager->getDbSchema();
                    } else {
                        $schema = 'public';
                    }
                    $conn->executeQuery("CREATE SCHEMA IF NOT EXISTS $schema");
                    $conn->executeQuery("SET search_path TO $schema");
                }

            } catch (\Exception $e) {
                if ($showException) {
                    $console->error('Não foi possível conectar ao banco de dados. (3)');
                    $console->error('Encontrei esse erro: ' . $e->getMessage());
                }
            }
        }

        return $conn;
    }
}

// Função global para compatibilidade
if (! function_exists('conectarDB')) {
    function conectarDB(InputInterface $input, OutputInterface $output, bool $showException = true, ?EnvironmentManager $environmentManager = null): \Doctrine\DBAL\Connection
    {
        return \Manzano\CvdwCli\Inc\Conexao::conectarDB($input, $output, $showException, $environmentManager);
    }
}
