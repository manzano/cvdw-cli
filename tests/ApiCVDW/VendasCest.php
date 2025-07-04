<?php

namespace Tests\ApiCVDW;

use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;
use Tests\Support\ApiTester;

class VendasCest extends Common
{
    protected array $responseContent = [
        'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array',
    ];

    protected int $tempoAceitavel = 5;
    protected string $formatoDataReferencia = 'Y-m-d H:i:s';
    public ?string $dataReferenciaTeste = null;

    public function getVendas(ApiTester $i)
    {
        $qtdRegistrosEsperado = 50;

        usleep(3000000); // Aguarda 3 segundos (em microsegundos)

        $startTime = microtime(true);

        $getContent = ['pagina' => 1, 'registros_por_pagina' => $qtdRegistrosEsperado];
        $i->sendGet('/vendas', $getContent);

        $duration = microtime(true) - $startTime;
        if ($duration > $this->tempoAceitavel) {
            Assert::markTestIncomplete("A requisição demorou mais de {$this->tempoAceitavel} segundos.");
        }

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType($this->responseContent);

        $resposta = $i->grabResponse();
        $data = json_decode($resposta, true);

        if (! isset($data['dados']) || ! is_array($data['dados'])) {
            Assert::fail("O campo 'dados' não foi encontrado ou não é um array.");
        }

        Assert::assertCount($qtdRegistrosEsperado, $data['dados']);

        $this->dataReferenciaTeste = $data['dados'][0]['referencia_data'] ?? null;
        if (empty($this->dataReferenciaTeste)) {
            Assert::fail("Não foi possível definir 'dataReferenciaTeste'.");
        }
    }

    public function getVendasComDataReferencia(ApiTester $i)
    {
        if (empty($this->dataReferenciaTeste)) {
            Assert::fail("O valor de 'dataReferenciaTeste' não foi definido no teste anterior.");
        }

        $qtdRegistrosEsperado = 5;
        usleep(3000000);

        $startTime = microtime(true);

        $getContent = [
            'pagina' => 1,
            'registros_por_pagina' => $qtdRegistrosEsperado,
            'a_partir_data_referencia' => urlencode($this->dataReferenciaTeste),
        ];

        $i->sendGet('/vendas', $getContent);

        $duration = microtime(true) - $startTime;
        if ($duration > $this->tempoAceitavel) {
            Assert::markTestIncomplete("A requisição demorou mais de {$this->tempoAceitavel} segundos.");
        }

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType($this->responseContent);

        $resposta = $i->grabResponse();
        $data = json_decode($resposta, true);

        if (! isset($data['dados']) || ! is_array($data['dados'])) {
            Assert::fail("O campo 'dados' não foi encontrado ou não é um array.");
        }

        Assert::assertCount($qtdRegistrosEsperado, $data['dados']);

        $timestamp_referencia = strtotime($data['dados'][0]['referencia_data']);
        $timestamp_filtro = strtotime($this->dataReferenciaTeste);

        if ($timestamp_referencia < $timestamp_filtro) {
            Assert::fail("A data da referência ({$data['dados'][0]['referencia_data']}) é menor que a data do filtro.");
        }
    }
}
