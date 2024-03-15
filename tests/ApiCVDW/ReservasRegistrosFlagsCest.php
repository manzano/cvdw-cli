<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasRegistrosFlagsCest extends Common
{
    public function getReservasRegistrosFlags(ApiTester $I)
    {
        $I->sendGet('/reservas/registros/flags', ['pagina' => 1, 'registros' => 500]);
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
            'idreserva' => 'integer|null',
            'data_criacao_reserva' => 'datetime|null',
            'empreendimento' => 'string|null',
            'idempreendimento' => 'integer|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'idunidade' => 'integer|null',
            'cliente' => 'string|null',
            'idcliente' => 'integer|null',
            'corretor' => 'string|null',
            'idcorretor' => 'integer|null',
            'data_sincronizacao' => 'datetime|null',
            'flag_inicio' => 'string|null',
            'data_entrada_flag_inicio' => 'datetime|null',
            'flag_inicio_semproposta' => 'string|null',
            'data_entrada_flag_inicio_semproposta' => 'datetime|null',
            'flag_vencida' => 'string|null',
            'data_entrada_flag_vencida' => 'datetime|null',
            'flag_vendida' => 'string|null',
            'data_entrada_flag_vendida' => 'datetime|null',
            'flag_distrato' => 'string|null',
            'data_entrada_flag_distrato' => 'datetime|null',
            'flag_analisada' => 'string|null',
            'data_entrada_flag_analisada' => 'datetime|null',
            'flag_aprovada' => 'string|null',
            'data_entrada_flag_aprovada' => 'datetime|null',
            'flag_aprovada_comercial' => 'string|null',
            'data_entrada_flag_aprovada_comercial' => 'datetime|null',
            'flag_vender_sienge' => 'string|null',
            'data_entrada_flag_vender_sienge' => 'datetime|null',
            'flag_cancelada_sienge' => 'string|null',
            'data_entrada_flag_cancelada_sienge' => 'datetime|null',
            'flag_cancelada' => 'string|null',
            'data_entrada_flag_cancelada' => 'datetime|null',
            'flag_pode_faturar' => 'string|null',
            'data_entrada_flag_pode_faturar' => 'datetime|null',
            'flag_documentos' => 'string|null',
            'data_entrada_flag_documentos' => 'datetime|null',
            'flag_contrato' => 'string|null',
            'data_entrada_flag_contrato' => 'datetime|null',
            'flag_aguardando_faturamento' => 'string|null',
            'data_entrada_flag_aguardando_faturamento' => 'datetime|null',
            'flag_faturada' => 'string|null',
            'data_entrada_flag_faturada' => 'datetime|null',
            'flag_pendente_assinatura_eletronica' => 'string|null',
            'data_entrada_flag_pendente_assinatura_eletronica' => 'datetime|null',
            'flag_pendente_assinatura_eletronica_segundo_envelope' => 'string|null',
            'data_entrada_flag_pendente_assinatura_eletronica_segundo_envelope' => 'datetime|null',
            'flag_assinatura_clientes_associados' => 'string|null',
            'data_entrada_flag_assinatura_clientes_associados' => 'datetime|null',
            'flag_assinatura_todas_partes' => 'string|null',
            'data_entrada_flag_assinatura_todas_partes' => 'datetime|null',
            'flag_reprovada_comercial' => 'string|null',
            'data_entrada_flag_reprovada_comercial' => 'datetime|null',
            'flag_pendente_comercial' => 'string|null',
            'data_entrada_flag_pendente_comercial' => 'datetime|null',
            'flag_devolucao_erp' => 'string|null',
            'data_entrada_flag_devolucao_erp' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}