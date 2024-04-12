<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasCest extends Common
{
    public function getPessoas(ApiTester $i)
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

        $i->sendGet('/pessoas', $bodyContent);

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
        // Estrutura de 'dados[0]'
        /*
        $i->seeResponseMatchesJsonType([
            'referencia' => 'string',
            'idpessoa' => 'integer|null',
            'idpessoa_int' => 'string|null',
            'idpessoa_legado' => 'string|null',
            'data_cad' => 'string|null',
            'ativo_painel' => 'string|null',
            'ativo_login' => 'string|null',
            'situacao' => 'string|null',
            'validado' => 'string|null',
            'documento_tipo' => 'string|null',
            'documento' => 'string|null',
            'pessoa_estrangeira' => 'string|null',
            'nome' => 'string|null',
            'reconhecimento_firma' => 'string|null',
            'sexo' => 'string|null',
            'data_nasc' => 'string|null',
            'estado_civil' => 'string|null',
            'data_casamento' => 'string|null',
            'pacto_antenupcial_livro' => 'string|null',
            'folha' => 'string|null',
            'cartorio' => 'string|null',
            'pais' => 'string|null',
            'naturalidade' => 'string|null',
            'possui_dependentes' => 'string|null',
            'quantidade_dependentes' => 'integer|null',
            'possui_procuracao' => 'string|null',
            'possui_pacto_antenupcial' => 'string|null',
            'menor_nao_emancipado' => 'string|null',
            'grau_instrucao' => 'string|null',
            'renda_familiar' => 'string|null',
            'marketing_pos_venda' => 'string|null',
            'rg' => 'string|null',
            'rg_orgao_emissor' => 'string|null',
            'rg_data_emissao' => 'string|null',
            'rne' => 'string|null',
            'rne_orgao_emissor' => 'string|null',
            'rne_data_emissao' => 'string|null',
            'passaporte' => 'string|null',
            'passaporte_orgao_emissor' => 'string|null',
            'passaporte_data_emissao' => 'string|null',
            'cnh' => 'string|null',
            'cnh_orgao_emissor' => 'string|null',
            'data_primeira_habilitacao_cnh' => 'string|null',
            'data_fim_validade_cnh' => 'string|null',
            'cnh_data_emissao' => 'string|null',
            'rnm' => 'string|null',
            'rnm_orgao_emissor' => 'string|null',
            'rnm_data_emissao' => 'string|null',
            'filiacao_mae' => 'string|null',
            'filiacao_pai' => 'string|null',
            'razao_social' => 'string|null',
            'segmento_razao' => 'string|null',
            'razao_social_anterior' => 'string|null',
            'sucessao' => 'string|null',
            'forma_constituicao' => 'string|null',
            'data_constituicao' => 'string|null',
            'numero_junta_comercial' => 'string|null',
            'classificacao' => 'string|null',
            'insc_estadual' => 'string|null',
            'insc_municipal' => 'string|null',
            'cep' => 'string|null',
            'logradouro' => 'string|null',
            'endereco' => 'string|null',
            'bairro' => 'string|null',
            'numero' => 'string|null',
            'complemento' => 'string|null',
            'estado' => 'string|null',
            'cidade' => 'string|null',
            'endereco_pais' => 'string|null',
            'tipo_residencia' => 'string|null',
            'reside_com' => 'string|null',
            'valor_aluguel' => 'string|null',
            'tempo_residencia' => 'integer|null',
            'tipo_correspondencia' => 'string|null',
            'politicamente_exposta' => 'string|null',
            'ppe_cargo' => 'string|null',
            'ppe_exercicio' => 'string|null',
            'suspeito' => 'string|null',
            'residente_municipio_fronteira' => 'string|null',
            'relacionamento_ppe' => 'string|null',
            'relacionamento_ppe_nome' => 'string|null',
            'relacionamento_ppe_parentesco' => 'string|null',
            'relacionamento_ppe_cargo' => 'string|null',
            'relacionamento_ppe_exercicio' => 'string|null',
            'relacionamento_ppe_identificacao' => 'string|null',
            'relacionamento_ppe_orgao_emissor' => 'string|null',
            'relacionamento_ppe_data_nasc' => 'string|null',
            'relacionamento_ppe_cpf' => 'string|null',
            'numero_pis' => 'string|null',
            'observacoes' => 'text|null'
        ], '$.dados[0]');
        */

    }

    public function getPessoasComDataReferencia(ApiTester $i)
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

        $i->sendGet('/pessoas', $bodyContent);

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

            // Agora, compara os timestamps
            //$i->assertTrue($timestamp_referencia >= $timestamp_filtro);

        }

    }    
}