<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasContratosCest extends Common
{
    public function getReservasContratos(ApiTester $I)
    {
        $I->sendGet('/reservas/contratos', ['pagina' => 1, 'registros' => 500]);
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
            'idreservacontrato' => 'integer|null',
            'idreserva' => 'integer|null',
            'ativo' => 'string|null',
            'data_cad' => 'datetime|null',
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
            'data_entrega' => 'datetime|null',
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
            'data_iniciado_geracao_word' => 'datetime|null',
            'data_sincronizacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}