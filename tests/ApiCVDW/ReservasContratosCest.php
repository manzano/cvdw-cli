<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasContratosCest extends Common
{
    public function getReservasContratos(ApiTester $I)
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

        $I->sendGet('/reservas/contratos', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        codecept_debug("Referência do primeiro item: " . $primeiraLinhaDados[0]['referencia']);
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $I->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');
            codecept_debug("Data do primeiro item: " . $referencia_data[0]);
            $I->validarFormatoDaData($referencia_data[0], 'Y-m-d H:i:s');
        }
        // Estrutura de 'dados[0]'
        /*
        $I->seeResponseMatchesJsonType([
            'referencia' => 'string',
            'idreservacontrato' => 'integer|null',
            'idreserva' => 'integer|null',
            'ativo' => 'string|null',
            'data_cad' => 'string|null',
            'idcontrato' => 'integer|null',
            'idgrupo' => 'integer|null',
            'idtipo' => 'integer|null',
            'idusuariovalida' => 'integer|null',
            'idusuario_imobiliaria_valida' => 'integer|null',
            'idcorretor_valida' => 'integer|null',
            'idusuarioassina' => 'integer|null',
            'idusuario_imobiliaria_assina' => 'integer|null',
            'idcorretor_assina' => 'integer|null',
            'idusuariocancela' => 'integer|null',
            'idusuario_imobiliaria_cancela' => 'integer|null',
            'idcorretor_cancela' => 'integer|null',
            'assinado' => 'string|null',
            'validado' => 'string|null',
            'cancelado' => 'string|null',
            'visualizacao' => 'integer|null',
            'idusuario' => 'integer|null',
            'idusuario_imobiliaria' => 'integer|null',
            'idcorretor' => 'integer|null',
            'data_entrega' => 'string|null',
            'justificativa' => 'string|null',
            'nao_assinado' => 'string|null',
            'nao_validado' => 'string|null',
            'arquivo' => 'string|null',
            'arquivo_tipo' => 'string|null',
            'arquivo_tamanho' => 'integer|null',
            'arquivo_servidor' => 'string|null',
            'idusuario_correspondente' => 'integer|null',
            'idusuario_correspondente_valida' => 'integer|null',
            'idusuario_correspondente_assina' => 'integer|null',
            'idusuario_correspondente_cancela' => 'integer|null',
            'idlancamento' => 'integer|null',
            'idreserva_contrato_lancamento' => 'integer|null',
            'imagem_comprimida' => 'string|null',
            'arquivo_servidor_word' => 'string|null',
            'tipo_assinatura_digital' => 'string|null',
            'iniciado_geracao_word' => 'string|null',
            'data_iniciado_geracao_word' => 'string|null',
            'data_sincronizacao' => 'string|null'
        ], '$.dados[0]');
        */

    }

    public function getReservasContratosComDataReferencia(ApiTester $I)
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

        $I->sendGet('/reservas/contratos', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $I->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');

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

            // Agora, compara os timestamps
            //$I->assertTrue($timestamp_referencia >= $timestamp_filtro);

        }

    }    
}