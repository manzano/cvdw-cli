<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsGanhosCest extends Common
{
    public function getLeadsGanhos(ApiTester $i)
    {
        sleep(3);
        $startTime = time();
        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];
        $i->sendGet('/leads/ganhos', $bodyContent);
        sleep(3);
        $endTime = time();
        $duration = $endTime - $startTime;
        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType($responseContent);
        $primeiraLinhaDados = $i->grabDataFromResponseByJsonPath('$.dados[0]');
        codecept_debug("Referência do primeiro item: " . $primeiraLinhaDados[0]['referencia']);
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $i->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');
            codecept_debug("Data do primeiro item: " . $referencia_data[0]);
            $i->validarFormatoDaData($referencia_data[0], 'Y-m-d H:i:s');
        }
    }

    public function getLeadsGanhosComDataReferencia(ApiTester $i)
    {
        
        sleep(3);
        $startTime = time();
        $now = new \DateTime();
        $now->modify('-45 days');
        $formattedDate = $now->format('Y-m-d');
        $bodyContent = ['pagina' => 1, 'registros' => 1, 'a_partir_data_referencia' => $formattedDate];
        codecept_debug("Body: " . $formattedDate);
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];
        $i->sendGet('/leads/ganhos', $bodyContent);
        sleep(3);
        $endTime = time();
        $duration = $endTime - $startTime;
        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType($responseContent);
        $primeiraLinhaDados = $i->grabDataFromResponseByJsonPath('$.dados[0]');
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $i->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');
            // verifica se $referencia_data[0] é maior que $formattedDate
            $timestamp_referencia = strtotime($referencia_data[0]);
            $timestamp_filtro = strtotime($formattedDate);
            codecept_debug("Data do primeiro item: " . $referencia_data[0] . " -> $timestamp_referencia");
            codecept_debug("Data do filtro: " . $formattedDate . " -> $timestamp_filtro");
            if($timestamp_referencia >= $timestamp_filtro){
                codecept_debug("Filtro é menor!");
                Assert::assertTrue(true);
            } else {
                codecept_debug("Filtro é maior!");
                Assert::assertTrue(false);
            }
        }
    }
}