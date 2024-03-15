<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AtendimentosCest extends Common
{
    public function getAtendimentos(ApiTester $I)
    {
        $I->sendGet('/atendimentos', ['pagina' => 1, 'registros' => 500]);
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
            'idatendimento' => 'integer|null',
            'protocolo' => 'string|null',
            'data_cad' => 'datetime|null',
            'prioridade' => 'string|null',
            'tempo_finalizado' => 'integer|null',
            'telefone_atendimento' => 'string|null',
            'nome_cliente' => 'string|null',
            'email_cliente' => 'string|null',
            'tempo_resposta' => 'integer|null',
            'encerrado_primeiro_contato' => 'string|null',
            'humor_cliente' => 'string|null',
            'idcanal' => 'integer|null',
            'idatendimento_vinculo' => 'string|null',
            'previsao_conclusao' => 'string|null',
            'idlead' => 'integer|null',
            'ativo_painel' => 'string|null',
            'avaliacao' => 'integer|null',
            'canal' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'text|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'regiao' => 'string|null',
            'situacao' => 'string|null',
            'idprotocolo' => 'integer|null',
            'assunto' => 'string|null',
            'subassunto' => 'string|null',
            'usuario' => 'string|null',
            'data_situacao' => 'datetime|null',
            'idclassificacao' => 'integer|null',
            'classificacao' => 'string|null',
            'idtipo' => 'integer|null',
            'tipo' => 'string|null',
            'imobiliaria' => 'string|null',
            'corretor' => 'string|null',
            'idsituacao' => 'integer|null',
            'idresponsavel' => 'integer|null',
            'responsavel' => 'string|null',
            'idunidade' => 'string|null',
            'idcorretor' => 'integer|null',
            'idimobiliaria' => 'string|null',
            'sigla_empreendimento' => 'string|null',
            'codigointerno_cliente' => 'string|null',
            'tags' => 'text|null',
            'quantidade_mensagens' => 'integer|null',
            'quantidade_interacoes' => 'integer|null',
            'sinalizador_juridico' => 'string|null',
            'times' => 'string|null',
            'origem' => 'string|null',
            'data_modificacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}