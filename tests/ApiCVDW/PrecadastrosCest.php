<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PrecadastrosCest extends Common
{
    public function getPrecadastros(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/precadastros', ['pagina' => 1, 'registros' => 1]);
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

        //$I->validarFormatoDaData('referencia_data', 'Y-m-d H:i:s', $dados[0]);

        //print_r($dados[0]);
        // Estrutura de 'dados[0]'
        /*
        $I->seeResponseMatchesJsonType([
            'referencia' => 'string',
            'idprecadastro' => 'integer|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'condicao_aprovada' => 'string|null',
            'idempreendimento' => 'integer|null',
            'empreendimento' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idempresa' => 'integer|null',
            'empresa' => 'string|null',
            'pessoa' => 'string|null',
            'cep_cliente' => 'string|null',
            'idusuario_correspondente' => 'integer|null',
            'usuario_correspondente' => 'string|null',
            'idpessoa' => 'integer|null',
            'idlead' => 'string|null',
            'valor_avaliacao' => 'integer|null',
            'valor_aprovado' => 'integer|null',
            'valor_subsidio' => 'integer|null',
            'valor_total' => 'integer|null',
            'valor_fgts' => 'integer|null',
            'saldo_devedor' => 'integer|null',
            'prazo' => 'string|null',
            'observacoes' => 'string|null',
            'tabela' => 'string|null',
            'valor_prestacao' => 'integer|null',
            'carta_credito' => 'string|null',
            'vencimento_aprovacao' => 'string|null',
            'idmotivo_reprovacao' => 'integer|null',
            'motivo_reprovacao' => 'string|null',
            'descricao_motivo_reprovacao' => 'text|null',
            'idmotivo_cancelamento' => 'integer|null',
            'motivo_cancelamento' => 'string|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'sla_vencimento' => 'string|null',
            'data_cad' => 'string|null',
            'empresa_correspondente' => 'string|null',
            'idsituacao_anterior' => 'integer|null',
            'situacao_anterior' => 'string|null',
            'data_ultima_alteracao_situacao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}