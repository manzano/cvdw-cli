<?php

namespace Manzano\CvdwCli\Services;
class Log
{

    protected $salvarLog = false;
    protected $arquivoLog = null;
    public function __construct($arquivoLog)
    {
        $this->arquivoLog = $arquivoLog;
        // Se $this->salvarLog for true, então executamos o método criarArquivoLog($this->arquivoLog)
        //if($this->salvarLog){
        //    $this->criarArquivoLog($this->arquivoLog);
        //}
    }

    public function criarArquivoLog()
    {
        // Verifica se o diretório /log existe, caso contrário, tenta criá-lo
        $diretorioLog = $this->retornarDiretorioLog();
        if (!file_exists($diretorioLog)) {
            mkdir($diretorioLog, 0777, true);
        }
        // Se o arquivo de log existir, remove
        if (file_exists($diretorioLog . "/" . $this->arquivoLog)) {
            unlink($diretorioLog . "/" . $this->arquivoLog);
        }

        fopen($diretorioLog . "/" . $this->arquivoLog, 'w');

        $primeiraMensagem = "[" . date('Y-m-d H:i:s') . "]";
        $this->escreverLog($primeiraMensagem);
    }

    public function escreverLog($mensagem) : void
    {
        // Verifica se o arquivo de log foi criado
        if($this->arquivoLog !== null){
            $diretorioLog = $this->retornarDiretorioLog();
            // Tenta abrir o arquivo de log para escrita, criando-o se não existir
            // 'a' abre o arquivo para escrita e posiciona o ponteiro no final do arquivo
            $arquivo = fopen($diretorioLog."/".$this->arquivoLog, 'a');
            if ($arquivo === false) {
                echo "Erro ao abrir o arquivo de log para escrita.";
                return;
            }
            // Escreve a mensagem no arquivo de log
            fwrite($arquivo, $mensagem . PHP_EOL);
            // Fecha o arquivo
            fclose($arquivo);
        }
    }

    public function retornarDiretorioLog() : string
    {
        return __DIR__ . '/../../../logs';
    }

}
