<?php

// Ler o arquivo cvdw.json
$cvdw = file_get_contents(__DIR__ . '/cvdw/cvdw.json');
$cvdw = json_decode($cvdw, true);

$report = [];

// Ler arquivos .json de cvdw/objetos
$files = glob(__DIR__ . '/cvdw/objetos/*.json');
foreach ($files as $file) {
    $json = file_get_contents($file);
    $objeto = json_decode($json, true);

    //echo "Objeto: " . $objeto['nome'] . "\n";

    $path = $objeto['path'];
    $dadosCVDW = $cvdw['paths'][$path]['dados'];
    $dadosObjeto = $objeto['response']['dados'];

    /*
        Array
        (
            [referencia] => string
            [referencia_data] => string
            [ativo] => string
            [idvistoria] => integer
            [idvistoria_pai] => integer
            [idempreendimento] => integer
            [empreendimento] => string
            [codigointerno_empreendimento] => string
            [etapa] => string
            [bloco] => string
            [unidade] => string
            [idunidade] => integer
            [cliente] => string
            [idcliente] => integer
            [cep_cliente] => string
            [data_agendamento] => string
            [horario] => string
            [vistoriador] => string
            [tipo] => string
            [situacao] => string
            [desmarcado] => string
            [quitado] => string
            [chave_liberada] => string
            [chave_entregue] => string
            [data_entrega_de_chave] => string
            [idreserva] => integer
            [numero] => integer
        )
        Array
        (
            [referencia] => string
            [referencia_data] => datetime
            [ativo] => string
            [idvistoria] => integer
            [idvistoria_pai] => integer
            [idempreendimento] => integer
            [empreendimento] => string
            [codigointerno_empreendimento] => string
            [etapa] => string
            [bloco] => string
            [unidade] => string
            [idunidade] => string
            [cliente] => string
            [idcliente] => integer
            [cep_cliente] => string
            [data_agendamento] => string
            [horario] => string
            [vistoriador] => string
            [tipo] => string
            [situacao] => string
            [quitado] => string
            [chave_liberada] => string
            [chave_entregue] => string
            [idreserva] => integer
            [numero] => integer
            [data_modificacao] => datetime
        )
    */

    foreach ($dadosCVDW as $keyDW => $tipoDW) {

        // Se for um array (Subtabela)
        if (is_array($tipoDW)) {

            $subDadosCVDW = $tipoDW;
            foreach ($subDadosCVDW as $subKeyDW => $subTipoDW) {
                // Verifica se a chave do DW existe em objeto
                if (isset($dadosObjeto[$keyDW][$subKeyDW])) {
                    if ($dadosObjeto[$keyDW][$subKeyDW] != $subTipoDW) {
                        if ($dadosObjeto[$keyDW][$subKeyDW] == 'datetime' && $subTipoDW == 'string') {
                            $report['igual'][$path][$keyDW][$subKeyDW] = $subTipoDW." = ".$dadosObjeto[$keyDW][$subKeyDW];

                            continue;
                        }
                        if ($dadosObjeto[$keyDW][$subKeyDW] == 'text' && $tipoDW == 'string') {
                            $report['igual'][$path][$keyDW][$subKeyDW] = $subTipoDW." = ".$dadosObjeto[$keyDW][$subKeyDW];

                            continue;
                        }
                        $report['alterar'][$path][$keyDW][$subKeyDW] = $subTipoDW." para ".$dadosObjeto[$keyDW][$subKeyDW];
                    } else {
                        $report['igual'][$path][$keyDW][$subKeyDW] = $subTipoDW." = ".$dadosObjeto[$keyDW][$subKeyDW] ;
                    }
                } else {
                    $report['adicionar'][$path][$keyDW][$subKeyDW] = $subTipoDW;
                }
            }

            continue;
        }
        // Verifica se a chave do DW existe em objeto
        if (isset($dadosObjeto[$keyDW])) {
            if ($dadosObjeto[$keyDW] != $tipoDW) {
                if ($dadosObjeto[$keyDW] == 'datetime' && $tipoDW == 'string') {
                    $report['igual'][$path][$keyDW] = $tipoDW." = ".$dadosObjeto[$keyDW];

                    continue;
                }
                if ($dadosObjeto[$keyDW] == 'text' && $tipoDW == 'string') {
                    $report['igual'][$path][$keyDW] = $tipoDW." = ".$dadosObjeto[$keyDW];

                    continue;
                }
                if (! is_array($dadosObjeto[$keyDW])) {
                    $report['alterar'][$path][$keyDW] = $tipoDW." para ".$dadosObjeto[$keyDW];
                }

            } else {
                $report['igual'][$path][$keyDW] = $tipoDW." = ".$dadosObjeto[$keyDW];
            }
        } else {
            $report['adicionar'][$path][$keyDW] = $tipoDW;
        }

    }

    foreach ($dadosObjeto as $keyObjeto => $tipoObjeto) {
        if (! isset($dadosCVDW[$keyObjeto])) {
            $report['remover'][$path][$keyObjeto] = $tipoObjeto;
        }

        if (isset($dadosCVDW[$keyObjeto]) && is_array($dadosCVDW[$keyObjeto])) {
            foreach ($dadosCVDW[$keyObjeto] as $keyObjeto2 => $tipoObjeto2) {
                if (! isset($dadosObjeto[$keyObjeto][$keyObjeto2])) {
                    $report['remover'][$path][$keyObjeto][$keyObjeto2] = $tipoObjeto2;
                }
            }
        }
    }

}



//unset($report['igual']);
//print_r($report);


$filename = "tests/cvdw/analise.csv";
$output = fopen($filename, 'w');
fputcsv($output, ['Path', 'Situação', 'Campo', 'Tipo CVDW', 'Tipo Objeto']);

foreach ($report as $situacao => $paths) {
    foreach ($paths as $path => $campos) {
        foreach ($campos as $campo => $tipos) {
            if (is_array($tipos)) {
                foreach ($tipos as $campo2 => $tipo) {
                    fputcsv($output, [$path, $situacao, $campo."->".$campo2, $tipo]);
                }
            } else {
                fputcsv($output, [$path, $situacao, $campo, $tipos]);
            }
        }
    }
}

// Fecha o arquivo
fclose($output);

echo "CSV gerado com sucesso: $filename\n";
exit;
