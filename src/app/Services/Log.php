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
        if($this->salvarLog){
            $this->criarArquivoLog($this->arquivoLog);
        }
    }

    protected function criarArquivoLog($arquivoLog)
    {
        // Verifica se o diretório /log existe, caso contrário, tenta criá-lo
        $diretorioLog = __DIR__ . '/../../../logs';
        if (!file_exists($diretorioLog)) {
            mkdir($diretorioLog, 0755, true);
        }
        $primeiraMensagem = "[" . date('Y-m-d H:i:s') . "]";
        $this->escreverLog($primeiraMensagem);
    }

    public function escreverLog($mensagem) : void
    {
        // Verifica se o arquivo de log foi criado
        if($this->arquivoLog !== null){
            // Tenta abrir o arquivo de log para escrita, criando-o se não existir
            // 'a' abre o arquivo para escrita e posiciona o ponteiro no final do arquivo
            $arquivo = fopen($this->arquivoLog, 'a');
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

}
