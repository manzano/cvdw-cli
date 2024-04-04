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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/reservas/comissoes/programacao', $bodyContent);
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