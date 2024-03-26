<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasComissoesProgramacaoCest extends Common
{
    public function getReservasComissoesProgramacao(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/reservas/comissoes/programacao', ['pagina' => 1, 'registros' => 1]);
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
            'idprogramacao' => 'integer|null',
            'idreserva' => 'integer|null',
            'idreservascondicoes' => 'integer|null',
            'idcondicoes_parcelas' => 'integer|null',
            'idregra' => 'integer|null',
            'contrato' => 'string|null',
            'parcela' => 'integer|null',
            'para' => 'string|null',
            'quem' => 'integer|null',
            'valor' => 'integer|null',
            'data_previsao' => 'string|null',
            'data_previsao_pagadoria' => 'string|null',
            'forma_pagamento' => 'string|null',
            'idgestaocontrato_lancamento' => 'integer|null',
            'idreserva_comissao_parcela_lancamento' => 'integer|null',
            'idlancamento' => 'integer|null',
            'idprogramacao_lancamento' => 'integer|null',
            'data_cad' => 'string|null'
        ], '$.dados[0]');
        */

    }
}