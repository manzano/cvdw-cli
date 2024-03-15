<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasCest extends Common
{
    public function getPessoas(ApiTester $I)
    {
        $I->sendGet('/pessoas', ['pagina' => 1, 'registros' => 500]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseMatchesJsonType([
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ]);

        // Testar a estrutura de 'dados'
        $dados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        Assert::assertNotEmpty($dados); // Assegura que 'dados' não está vazio

        // Estrutura de 'dados[0]'
        $I->seeResponseMatchesJsonType([
            'referencia' => 'integer|string',
            'referencia_data' => 'datetime',
            'idpessoa' => 'integer|null',
            'idpessoa_int' => 'string|null',
            'idpessoa_legado' => 'string|null',
            'data_cad' => 'datetime|null',
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
            'data_nasc' => 'datetime|null',
            'estado_civil' => 'string|null',
            'data_casamento' => 'datetime|null',
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
            'rg_data_emissao' => 'datetime|null',
            'rne' => 'string|null',
            'rne_orgao_emissor' => 'string|null',
            'rne_data_emissao' => 'datetime|null',
            'passaporte' => 'string|null',
            'passaporte_orgao_emissor' => 'string|null',
            'passaporte_data_emissao' => 'datetime|null',
            'cnh' => 'string|null',
            'cnh_orgao_emissor' => 'string|null',
            'data_primeira_habilitacao_cnh' => 'datetime|null',
            'data_fim_validade_cnh' => 'datetime|null',
            'cnh_data_emissao' => 'datetime|null',
            'rnm' => 'string|null',
            'rnm_orgao_emissor' => 'string|null',
            'rnm_data_emissao' => 'datetime|null',
            'filiacao_mae' => 'string|null',
            'filiacao_pai' => 'string|null',
            'razao_social' => 'string|null',
            'segmento_razao' => 'string|null',
            'razao_social_anterior' => 'string|null',
            'sucessao' => 'string|null',
            'forma_constituicao' => 'string|null',
            'data_constituicao' => 'datetime|null',
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
            'relacionamento_ppe_data_nasc' => 'datetime|null',
            'relacionamento_ppe_cpf' => 'string|null',
            'numero_pis' => 'string|null',
            'observacoes' => 'text|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}