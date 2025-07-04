<?php

use Manzano\CvdwCli\Inc\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testValidarDataComFormatoHora()
    {
        // Teste com formato de data e hora válido
        $this->assertTrue(Helper::validarData('2023-12-25T14:30:00'));
        $this->assertTrue(Helper::validarData('2000-01-01T00:00:00'));
        $this->assertTrue(Helper::validarData('2099-12-31T23:59:59'));

        // Teste com formato de data e hora inválido
        $this->assertFalse(Helper::validarData('2023-13-25T14:30:00')); // Mês inválido
        $this->assertFalse(Helper::validarData('2023-12-32T14:30:00')); // Dia inválido
        $this->assertFalse(Helper::validarData('2023-12-25T25:30:00')); // Hora inválida
        $this->assertFalse(Helper::validarData('2023-12-25T14:60:00')); // Minuto inválido
        $this->assertFalse(Helper::validarData('2023-12-25T14:30:60')); // Segundo inválido
    }

    public function testValidarDataComFormatoData()
    {
        // Teste com formato de data válido
        $this->assertTrue(Helper::validarData('2023-12-25'));
        $this->assertTrue(Helper::validarData('2000-01-01'));
        $this->assertTrue(Helper::validarData('2099-12-31'));

        // Teste com formato de data inválido
        $this->assertFalse(Helper::validarData('2023-13-25')); // Mês inválido
        $this->assertFalse(Helper::validarData('2023-12-32')); // Dia inválido
        $this->assertFalse(Helper::validarData('2023/12/25')); // Formato inválido
    }

    public function testValidarDataComAnoLimite()
    {
        // Teste com anos nos limites
        $this->assertTrue(Helper::validarData('1900-01-01'));
        $this->assertTrue(Helper::validarData('2100-12-31'));

        // Teste com anos fora dos limites
        $this->assertFalse(Helper::validarData('1899-12-31'));
        $this->assertFalse(Helper::validarData('2101-01-01'));
    }

    public function testValidarDataComValoresVazios()
    {
        $this->assertFalse(Helper::validarData(''));
        $this->assertFalse(Helper::validarData(null));
        $this->assertFalse(Helper::validarData('   '));
    }

    public function testValidarDataCaminhosExtras()
    {
        // Data vazia
        $this->assertFalse(Helper::validarData(''));
        $this->assertFalse(Helper::validarData(null));
        $this->assertFalse(Helper::validarData('   '));

        // Data com formato correto mas ano fora do limite
        $this->assertFalse(Helper::validarData('1800-01-01'));
        $this->assertFalse(Helper::validarData('2200-01-01'));

        // Data com formato incorreto
        $this->assertFalse(Helper::validarData('2023-02-30'));
        $this->assertFalse(Helper::validarData('2023-02-28T99:99:99'));
        $this->assertFalse(Helper::validarData('2023/12/25'));
    }

    public function testSubstituirPorAsteriscos()
    {
        // Teste com string par
        $this->assertEquals('a***ef', Helper::substituirPorAsteriscos('abcdef'));
        $this->assertEquals('a**d', Helper::substituirPorAsteriscos('abcd'));

        // Teste com string ímpar
        $this->assertEquals('a****efg', Helper::substituirPorAsteriscos('abcdefg'));
        $this->assertEquals('a***de', Helper::substituirPorAsteriscos('abcde'));

        // Teste com string pequena
        $this->assertEquals('*b', Helper::substituirPorAsteriscos('ab'));
        $this->assertEquals('**bc', Helper::substituirPorAsteriscos('abc'));

        // Teste com string de um caractere
        $this->assertEquals('*a', Helper::substituirPorAsteriscos('a'));

        // Teste com valores nulos
        $this->assertNull(Helper::substituirPorAsteriscos(null));

        // Teste com espaços
        $this->assertEquals('a***ef', Helper::substituirPorAsteriscos('  abcdef  '));
    }

    public function testSubstituirPorAsteriscosCaminhosExtras()
    {
        // String vazia
        $this->assertEquals('', Helper::substituirPorAsteriscos(''));
        // String com espaços
        $this->assertEquals('', Helper::substituirPorAsteriscos('   '));
        // String com caracteres especiais
        $this->assertEquals('**@#', Helper::substituirPorAsteriscos('!@#'));
        // String longa par
        $this->assertEquals('abc******jkl', Helper::substituirPorAsteriscos('abcdefghijkl'));
        // String longa ímpar
        $this->assertEquals('a***de', Helper::substituirPorAsteriscos('abcde'));
    }

    public function testSubstituirPorHash()
    {
        // Teste com texto normal
        $hash1 = Helper::substituirPorHash('teste');
        $this->assertEquals(32, strlen($hash1));
        $this->assertIsString($hash1);

        // Teste com caracteres personalizados
        $hash2 = Helper::substituirPorHash('teste', 16);
        $this->assertEquals(16, strlen($hash2));

        $hash3 = Helper::substituirPorHash('teste', 8);
        $this->assertEquals(8, strlen($hash3));

        // Teste com valores nulos
        $this->assertNull(Helper::substituirPorHash(null));

        // Teste que o hash é consistente
        $hash4 = Helper::substituirPorHash('mesmo_texto');
        $hash5 = Helper::substituirPorHash('mesmo_texto');
        $this->assertEquals($hash4, $hash5);

        // Teste que hashes diferentes para textos diferentes
        $hash6 = Helper::substituirPorHash('texto1');
        $hash7 = Helper::substituirPorHash('texto2');
        $this->assertNotEquals($hash6, $hash7);
    }

    public function testSubstituirPorHashCaminhosExtras()
    {
        // String vazia
        $hash = Helper::substituirPorHash('');
        $this->assertEquals(32, strlen($hash));
        // String longa
        $long = str_repeat('a', 100);
        $hashLong = Helper::substituirPorHash($long);
        $this->assertEquals(32, strlen($hashLong));
        // Tamanho customizado
        $this->assertEquals(10, strlen(Helper::substituirPorHash('teste', 10)));
        $this->assertEquals(64, strlen(Helper::substituirPorHash('teste', 64)));
    }

    public function testConstantes()
    {
        $this->assertEquals(1900, Helper::ANO_MINIMO);
        $this->assertEquals(2100, Helper::ANO_MAXIMO);
        $this->assertEquals('Y-m-d\TH:i:s', Helper::FORMATO_DATA_HORA);
        $this->assertEquals('Y-m-d', Helper::FORMATO_DATA);
    }
}
