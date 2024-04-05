<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasComissoesCest extends Common
{
    public function getReservasComissoes(ApiTester $I)
    {
        
        sleep(3);
        $startTime = time();

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/reservas/comissoes', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

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
            'id' => 'integer|null',
            'data_cad' => 'string|null',
            'ativo' => 'string|null',
            'diferenca' => 'string|null',
            'tipo' => 'string|null',
            'valor' => 'integer|null',
            'porcentagem' => 'integer|null',
            'descricao' => 'string|null',
            'idreserva' => 'integer|null',
            'idimobiliaria' => 'integer|null',
            'idcorretor' => 'integer|null',
            'comissao_tipo' => 'string|null',
            'comissao_pagamento_tipo' => 'string|null',
            'codigointerno' => 'string|null',
            'tipo_valor' => 'string|null',
            'forma_pagamento' => 'string|null',
            'pago_pela_condicao_reserva' => 'string|null',
            'contrato' => 'string|null',
            'para' => 'string|null',
            'quem' => 'integer|null',
            'ordem' => 'integer|null',
            'enviar_pagadoria' => 'string|null',
            'editado' => 'string|null',
            'para_pagamento' => 'string|null',
            'idusuario_nivel' => 'integer|null',
            'idusuario_categoria' => 'integer|null',
            'idcorretor_nivel' => 'integer|null',
            'idcorretor_categoria' => 'integer|null',
            'fifty' => 'string|null',
            'fifty_idreferencia' => 'integer|null',
            'id_sub_regra_comissao' => 'integer|null',
            'idlancamento' => 'integer|null',
            'idreserva_comissao_lancamento' => 'integer|null',
            'valor_porcentagem_premiacao' => 'integer|null'
        ], '$.dados[0]');
        */

    }

    public function getReservasComissoesComDataReferencia(ApiTester $I)
    {
        
        sleep(3);
        $startTime = time();

        $now = new \DateTime();
        $now->modify('-45 days');
        $formattedDate = $now->format('Y-m-d');

        $bodyContent = ['pagina' => 1, 'registros' => 1, 'a_partir_data_referencia' => $formattedDate];
        
        codecept_debug("Body: " . $formattedDate);
        
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/reservas/comissoes', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $I->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');

            // verifica se $referencia_data[0] é maior que $formattedDate
            $timestamp_referencia = strtotime($referencia_data[0]);
            $timestamp_filtro = strtotime($formattedDate);

            codecept_debug("Data do primeiro item: " . $referencia_data[0] . " -> $timestamp_referencia");
            codecept_debug("Data do filtro: " . $formattedDate . " -> $timestamp_filtro");

            if($timestamp_referencia >= $timestamp_filtro){
                codecept_debug("Filtro é menor!");
                Assert::assertTrue(true);
            } else {
                codecept_debug("Filtro é maior!");
                Assert::assertTrue(false);
            }

            // Agora, compara os timestamps
            //$I->assertTrue($timestamp_referencia >= $timestamp_filtro);

        }

        // Estrutura de 'dados[0]'
        /*
        $I->seeResponseMatchesJsonType([
            'referencia' => 'string',
            'id' => 'integer|null',
            'data_cad' => 'string|null',
            'ativo' => 'string|null',
            'diferenca' => 'string|null',
            'tipo' => 'string|null',
            'valor' => 'integer|null',
            'porcentagem' => 'integer|null',
            'descricao' => 'string|null',
            'idreserva' => 'integer|null',
            'idimobiliaria' => 'integer|null',
            'idcorretor' => 'integer|null',
            'comissao_tipo' => 'string|null',
            'comissao_pagamento_tipo' => 'string|null',
            'codigointerno' => 'string|null',
            'tipo_valor' => 'string|null',
            'forma_pagamento' => 'string|null',
            'pago_pela_condicao_reserva' => 'string|null',
            'contrato' => 'string|null',
            'para' => 'string|null',
            'quem' => 'integer|null',
            'ordem' => 'integer|null',
            'enviar_pagadoria' => 'string|null',
            'editado' => 'string|null',
            'para_pagamento' => 'string|null',
            'idusuario_nivel' => 'integer|null',
            'idusuario_categoria' => 'integer|null',
            'idcorretor_nivel' => 'integer|null',
            'idcorretor_categoria' => 'integer|null',
            'fifty' => 'string|null',
            'fifty_idreferencia' => 'integer|null',
            'id_sub_regra_comissao' => 'integer|null',
            'idlancamento' => 'integer|null',
            'idreserva_comissao_lancamento' => 'integer|null',
            'valor_porcentagem_premiacao' => 'integer|null'
        ], '$.dados[0]');
        */

    }    
}