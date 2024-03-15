<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasComissoesProgramacaoCest extends Common
{
    public function getReservasComissoesProgramacao(ApiTester $I)
    {
        $I->sendGet('/reservas/comissoes/programacao', ['pagina' => 1, 'registros' => 500]);
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
            'idprogramacao' => 'integer|null',
            'idreserva' => 'integer|null',
            'idreservascondicoes' => 'integer|null',
            'idcondicoes_parcelas' => 'integer|null',
            'idregra' => 'integer|null',
            'contrato' => 'string|null',
            'parcela' => 'integer|null',
            'para' => 'string|null',
            'quem' => 'integer|null',
            'valor' => 'number|null',
            'data_previsao' => 'datetime|null',
            'data_previsao_pagadoria' => 'datetime|null',
            'forma_pagamento' => 'string|null',
            'idgestaocontrato_lancamento' => 'integer|null',
            'idreserva_comissao_parcela_lancamento' => 'integer|null',
            'idlancamento' => 'integer|null',
            'idprogramacao_lancamento' => 'integer|null',
            'data_cad' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}