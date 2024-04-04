<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasCest extends Common
{
    public function getReservas(ApiTester $I)
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

        $I->sendGet('/reservas', $bodyContent);
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
            'idreserva' => 'integer|null',
            'aprovada' => 'string|null',
            'data_cad' => 'string|null',
            'data_venda' => 'string|null',
            'situacao' => 'string|null',
            'idsituacao' => 'integer|null',
            'situacao_comercial' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'data_entrega_chaves_contrato_cliente' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'regiao' => 'string|null',
            'venda' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'email' => 'string|null',
            'cidade' => 'string|null',
            'cep_cliente' => 'string|null',
            'renda' => 'integer|null',
            'sexo' => 'string|null',
            'idade' => 'integer|null',
            'estado_civil' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'valor_contrato' => 'integer|null',
            'vencimento' => 'string|null',
            'campanha' => 'string|null',
            'cessao' => 'string|null',
            'motivo_cancelamento' => 'string|null',
            'espacos_complementares' => 'text|null',
            'idlead' => 'text|null',
            'data_ultima_alteracao_situacao' => 'string|null',
            'empresa_correspondente' => 'string|null',
            'valor_fgts' => 'integer|null',
            'valor_financiamento' => 'integer|null',
            'valor_subsidio' => 'integer|null',
            'nome_usuario' => 'string|null',
            'idunidade' => 'integer|null',
            'idprecadastro' => 'integer|null',
            'idmidia' => 'integer|null',
            'midia' => 'string|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'idsituacao_anterior' => 'integer|null',
            'situacao_anterior' => 'string|null',
            'idtabela' => 'integer|null',
            'nometabela' => 'string|null',
            'codigointernotabela' => 'string|null',
            'data_contrato' => 'string|null',
            'valor_proposta' => 'integer|null',
            'vpl_tabela' => 'integer|null',
            'vpl_reserva' => 'integer|null',
            'usuario_aprovacao' => 'string|null',
            'data_aprovacao' => 'string|null',
            'juros_condicao_aprovada' => 'integer|null',
            'juros_apos_entrega_condicao_aprovada' => 'integer|null',
            'idtabela_condicao_aprovada' => 'integer|null',
            'data_primeira_aprovacao' => 'string|null',
            'aprovacao_absoluto' => 'integer|null',
            'aprovacao_vpl_valor' => 'integer|null',
            'idtipovenda' => 'integer|null',
            'tipovenda' => 'string|null',
            'idgrupo' => 'integer|null',
            'grupo' => 'string|null',
            'data_modificacao' => 'string|null',
            'campos_adicionais' => 'array|null'
        ], '$.dados[0]');
        */

    }
}