<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class UsuariosAdministrativosCest extends Common
{
    public function getUsuariosAdministrativos(ApiTester $I)
    {
        $I->sendGet('/usuarios_administrativos', ['pagina' => 1, 'registros' => 500]);
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
            'idusuario' => 'integer|null',
            'ativo' => 'string|null',
            'ativo_painel' => 'string|null',
            'tipo_login' => 'string|null',
            'usuario' => 'string|null',
            'motivo_bloqueiologin' => 'string|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}