<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasRegistrosFlagsCest extends Common
{
    public function getReservasRegistrosFlags(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/reservas/registros/flags', ['pagina' => 1, 'registros' => 1]);
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
            'idreserva' => 'integer|null',
            'data_criacao_reserva' => 'string|null',
            'empreendimento' => 'string|null',
            'idempreendimento' => 'integer|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'idunidade' => 'integer|null',
            'cliente' => 'string|null',
            'idcliente' => 'integer|null',
            'corretor' => 'string|null',
            'idcorretor' => 'integer|null',
            'data_sincronizacao' => 'string|null',
            'flag_inicio' => 'string|null',
            'data_entrada_flag_inicio' => 'string|null',
            'flag_inicio_semproposta' => 'string|null',
            'data_entrada_flag_inicio_semproposta' => 'string|null',
            'flag_vencida' => 'string|null',
            'data_entrada_flag_vencida' => 'string|null',
            'flag_vendida' => 'string|null',
            'data_entrada_flag_vendida' => 'string|null',
            'flag_distrato' => 'string|null',
            'data_entrada_flag_distrato' => 'string|null',
            'flag_analisada' => 'string|null',
            'data_entrada_flag_analisada' => 'string|null',
            'flag_aprovada' => 'string|null',
            'data_entrada_flag_aprovada' => 'string|null',
            'flag_aprovada_comercial' => 'string|null',
            'data_entrada_flag_aprovada_comercial' => 'string|null',
            'flag_vender_sienge' => 'string|null',
            'data_entrada_flag_vender_sienge' => 'string|null',
            'flag_cancelada_sienge' => 'string|null',
            'data_entrada_flag_cancelada_sienge' => 'string|null',
            'flag_cancelada' => 'string|null',
            'data_entrada_flag_cancelada' => 'string|null',
            'flag_pode_faturar' => 'string|null',
            'data_entrada_flag_pode_faturar' => 'string|null',
            'flag_documentos' => 'string|null',
            'data_entrada_flag_documentos' => 'string|null',
            'flag_contrato' => 'string|null',
            'data_entrada_flag_contrato' => 'string|null',
            'flag_aguardando_faturamento' => 'string|null',
            'data_entrada_flag_aguardando_faturamento' => 'string|null',
            'flag_faturada' => 'string|null',
            'data_entrada_flag_faturada' => 'string|null',
            'flag_pendente_assinatura_eletronica' => 'string|null',
            'data_entrada_flag_pendente_assinatura_eletronica' => 'string|null',
            'flag_pendente_assinatura_eletronica_segundo_envelope' => 'string|null',
            'data_entrada_flag_pendente_assinatura_eletronica_segundo_envelope' => 'string|null',
            'flag_assinatura_clientes_associados' => 'string|null',
            'data_entrada_flag_assinatura_clientes_associados' => 'string|null',
            'flag_assinatura_todas_partes' => 'string|null',
            'data_entrada_flag_assinatura_todas_partes' => 'string|null',
            'flag_reprovada_comercial' => 'string|null',
            'data_entrada_flag_reprovada_comercial' => 'string|null',
            'flag_pendente_comercial' => 'string|null',
            'data_entrada_flag_pendente_comercial' => 'string|null',
            'flag_devolucao_erp' => 'string|null',
            'data_entrada_flag_devolucao_erp' => 'string|null'
        ], '$.dados[0]');
        */

    }
}