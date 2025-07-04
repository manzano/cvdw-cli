<?php

namespace Manzano\CvdwCli\Services\Console;

use Manzano\CvdwCli\Services\Log;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CvdwSymfonyStyle extends SymfonyStyle
{
    protected $logObjeto = false;
    public InputInterface $input;
    public OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output, $logObjeto = false)
    {
        parent::__construct($input, $output);
        $this->logObjeto = $logObjeto;
        $this->input = $input;
        $this->output = $output;
    }

    public function text($message)
    {
        parent::text($message);
        if ($this->logObjeto) {
            // Se $message for um array, então fazemos um foreach para escrever cada mensagem no arquivo de log
            if (is_array($message)) {
                foreach ($message as $msg) {
                    $this->logObjeto->escreverLog($msg);
                }
            } else {
                $this->logObjeto->escreverLog($message);
            }
        }
    }

    public function error($message)
    {
        parent::error($message);

        if ($this->logObjeto) {
            // Se $message for um array, então fazemos um foreach para escrever cada mensagem no arquivo de log
            if (is_array($message)) {
                foreach ($message as $msg) {
                    $this->logObjeto->escreverLog('[ERRO] '. $msg);
                }
            } else {
                $this->logObjeto->escreverLog('[ERRO] ' . $message);
            }
        }
    }

    public function info($message)
    {
        parent::info($message);
        if ($this->logObjeto) {
            // Se $message for um array, então fazemos um foreach para escrever cada mensagem no arquivo de log
            if (is_array($message)) {
                foreach ($message as $msg) {
                    $this->logObjeto->escreverLog('[INFO] '. $msg);
                }
            } else {
                $this->logObjeto->escreverLog('[INFO] ' . $message);
            }
        }
    }

    public function section($message)
    {
        parent::section($message);
        if ($this->logObjeto) {
            // Se $message for um array, então fazemos um foreach para escrever cada mensagem no arquivo de log
            if (is_array($message)) {
                foreach ($message as $msg) {
                    $this->logObjeto->escreverLog($msg);
                }
            } else {
                $this->logObjeto->escreverLog($message);
            }
        }
    }

    public function question(string $question): string
    {
        return $this->ask(sprintf(' ✍️  %s', $question));
    }

}
