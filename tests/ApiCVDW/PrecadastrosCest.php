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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/precadastros', $bodyContent);
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