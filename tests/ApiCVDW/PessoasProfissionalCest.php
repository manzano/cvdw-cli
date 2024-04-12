<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasProfissionalCest extends Common
{
    public function getPessoasProfissional(ApiTester $I)
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

        $I->sendGet('/pessoas/profissional', $bodyContent);

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
            'idpessoa' => 'string|null',
            'idpessoa_int' => 'string|null',
            'trabalho_nome_empresa' => 'string|null',
            'trabalho_cnpj' => 'string|null',
            'trabalho_pais' => 'string|null',
            'trabalho_site' => 'string|null',
            'profissao_select' => 'string|null',
            'profissao' => 'string|null',
            'trabalho_cargo' => 'string|null',
            'remuneracao_bruta' => 'string|null',
            'remuneracao_liquida' => 'string|null',
            'trabalho_participacao_proprietario' => 'string|null',
            'trabalho_telefone' => 'string|null',
            'trabalho_celular' => 'string|null',
            'trabalho_fax' => 'string|null',
            'trabalho_email' => 'string|null',
            'trabalho_cep' => 'string|null',
            'logradouro_trabalho' => 'string|null',
            'trabalho_endereco' => 'string|null',
            'trabalho_bairro' => 'string|null',
            'trabalho_numero' => 'string|null',
            'complemento_trabalho' => 'string|null',
            'trabalho_estado' => 'string|null',
            'trabalho_cidade' => 'string|null',
            'trabalho_endereco_pais' => 'string|null',
            'trabalho_anterior_nome_empresa' => 'string|null',
            'trabalho_anterior_cnpj' => 'string|null',
            'trabalho_anterior_data_admissao' => 'string|null',
            'trabalho_anterior_data_desligamento' => 'string|null',
            'possui_outras_rendas' => 'string|null',
            'valor_mensal_bruto' => 'string|null',
            'valor_mensal_liquido' => 'string|null',
            'renda_proveniente_de' => 'string|null',
            'renda_comprovada_atraves_de' => 'string|null',
            'possui_aplicacao_financeira' => 'string|null',
            'valor_total_aplicacao' => 'string|null',
            'rendimento_mensal_aplicacao' => 'string|null',
            'banco_aplicacao' => 'string|null',
            'pretende_utilizar_fgts' => 'string|null',
            'valor_fgts' => 'string|null',
            'possui_imovel' => 'string|null',
            'possui_onus_imovel' => 'string|null',
            'cep_imovel_proprietario' => 'string|null',
            'endereco_imovel_proprietario' => 'string|null',
            'bairro_imovel_proprietario' => 'string|null',
            'estado_imovel_proprietario' => 'string|null',
            'cidade_imovel_proprietario' => 'string|null',
            'endereco_pais_imovel_proprietario' => 'string|null'
        ], '$.dados[0]');
        */

    }

    public function getPessoasProfissionalComDataReferencia(ApiTester $I)
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

        $I->sendGet('/pessoas/profissional', $bodyContent);

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