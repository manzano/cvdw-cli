<?php

namespace Manzano\CvdwCli\Inc;

class Helper
{
    public const ANO_MINIMO = 1900;
    public const ANO_MAXIMO = 2100;
    public const FORMATO_DATA_HORA = 'Y-m-d\TH:i:s';
    public const FORMATO_DATA = 'Y-m-d';

    public static function validarData($dataInput): bool
    {
        if (empty($dataInput)) {
            return false;
        }

        // Validar formato com hora
        $data = \DateTime::createFromFormat(self::FORMATO_DATA_HORA, $dataInput);
        if ($data && $data->format(self::FORMATO_DATA_HORA) === $dataInput) {
            // Validar ano razoável
            $ano = (int)$data->format('Y');
            if ($ano >= self::ANO_MINIMO && $ano <= self::ANO_MAXIMO) {
                return true;
            }
        }

        // Validar formato sem hora
        $data = \DateTime::createFromFormat(self::FORMATO_DATA, $dataInput);
        if ($data && $data->format(self::FORMATO_DATA) === $dataInput) {
            // Validar ano razoável
            $ano = (int)$data->format('Y');
            if ($ano >= self::ANO_MINIMO && $ano <= self::ANO_MAXIMO) {
                return true;
            }
        }

        return false;
    }

    public static function substituirPorAsteriscos($texto): ?string
    {
        if (is_null($texto)) {
            return null;
        }

        $texto = trim($texto);

        $tamanho = strlen($texto);
        $metade = (int)floor($tamanho / 2); // Encontra o índice da metade da string

        // Verifica se o tamanho da string é ímpar para determinar o número de asteriscos
        if ($tamanho % 2 !== 0) {
            $asteriscos = str_repeat('*', $metade + 1);
        } else {
            $asteriscos = str_repeat('*', $metade);
        }

        // Substitui a metade do meio da string pelos asteriscos
        $texto = substr_replace($texto, $asteriscos, (int)($metade / 2), $metade);

        return $texto;
    }

    public static function substituirPorHash($texto, int $caracteres = 32): ?string
    {
        if (is_null($texto)) {
            return null;
        }

        $hash = hash('sha256', $texto);

        return substr($hash, 0, $caracteres);
    }
}

// Manter compatibilidade com código existente
if (! function_exists('validarData')) {
    function validarData($dataInput): bool
    {
        return \Manzano\CvdwCli\Inc\Helper::validarData($dataInput);
    }
}

if (! function_exists('substituirPorAsteriscos')) {
    function substituirPorAsteriscos($texto): ?string
    {
        return \Manzano\CvdwCli\Inc\Helper::substituirPorAsteriscos($texto);
    }
}

if (! function_exists('substituirPorHash')) {
    function substituirPorHash($texto, int $caracteres = 32): ?string
    {
        return \Manzano\CvdwCli\Inc\Helper::substituirPorHash($texto, $caracteres);
    }
}
