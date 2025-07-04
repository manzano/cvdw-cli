<?php

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\Log;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CvdwSymfonyStyleTest extends TestCase
{
    private $input;
    private $output;
    private $logMock;

    protected function setUp(): void
    {
        $this->input = new ArrayInput([]);
        $this->output = new BufferedOutput();
        $this->logMock = $this->createMock(Log::class);
    }

    public function testConstructor()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output);

        $this->assertInstanceOf(CvdwSymfonyStyle::class, $style);
        $this->assertSame($this->input, $style->input);
        $this->assertSame($this->output, $style->output);
    }

    public function testConstructorWithLog()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);

        $this->assertInstanceOf(CvdwSymfonyStyle::class, $style);
        $this->assertSame($this->input, $style->input);
        $this->assertSame($this->output, $style->output);
    }

    public function testTextWithoutLog()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output);
        $message = "Mensagem de teste";

        $style->text($message);

        $this->assertStringContainsString($message, $this->output->fetch());
    }

    public function testTextWithLog()
    {
        $this->logMock->expects($this->once())
            ->method('escreverLog')
            ->with('Mensagem de teste');

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $message = "Mensagem de teste";

        $style->text($message);
    }

    public function testTextWithArray()
    {
        $calls = [];
        $this->logMock->expects($this->exactly(2))
            ->method('escreverLog')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
            });

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $messages = ["Mensagem 1", "Mensagem 2"];

        $style->text($messages);

        $this->assertEquals(['Mensagem 1', 'Mensagem 2'], $calls);
    }

    public function testErrorWithoutLog()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output);
        $message = "Erro de teste";

        $style->error($message);

        $output = $this->output->fetch();
        $this->assertStringContainsString($message, $output);
    }

    public function testErrorWithLog()
    {
        $this->logMock->expects($this->once())
            ->method('escreverLog')
            ->with('[ERRO] Erro de teste');

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $message = "Erro de teste";

        $style->error($message);
    }

    public function testErrorWithArray()
    {
        $calls = [];
        $this->logMock->expects($this->exactly(2))
            ->method('escreverLog')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
            });

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $messages = ["Erro 1", "Erro 2"];

        $style->error($messages);

        $this->assertEquals(['[ERRO] Erro 1', '[ERRO] Erro 2'], $calls);
    }

    public function testInfoWithoutLog()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output);
        $message = "Info de teste";

        $style->info($message);

        $output = $this->output->fetch();
        $this->assertStringContainsString($message, $output);
    }

    public function testInfoWithLog()
    {
        $this->logMock->expects($this->once())
            ->method('escreverLog')
            ->with('[INFO] Info de teste');

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $message = "Info de teste";

        $style->info($message);
    }

    public function testInfoWithArray()
    {
        $calls = [];
        $this->logMock->expects($this->exactly(2))
            ->method('escreverLog')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
            });

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $messages = ["Info 1", "Info 2"];

        $style->info($messages);

        $this->assertEquals(['[INFO] Info 1', '[INFO] Info 2'], $calls);
    }

    public function testSectionWithoutLog()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output);
        $message = "Seção de teste";

        $style->section($message);

        $output = $this->output->fetch();
        $this->assertStringContainsString($message, $output);
    }

    public function testSectionWithLog()
    {
        $this->logMock->expects($this->once())
            ->method('escreverLog')
            ->with('Seção de teste');

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $message = "Seção de teste";

        $style->section($message);
    }

    public function testSectionWithArray()
    {
        $calls = [];
        $this->logMock->expects($this->exactly(2))
            ->method('escreverLog')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
            });

        $style = new CvdwSymfonyStyle($this->input, $this->output, $this->logMock);
        $messages = ["Seção 1", "Seção 2"];

        $style->section($messages);

        $this->assertEquals(['Seção 1', 'Seção 2'], $calls);
    }

    public function testQuestion()
    {
        $style = new CvdwSymfonyStyle($this->input, $this->output);
        $question = "Qual é sua resposta?";

        // Como question() chama ask(), que requer interação, vamos apenas testar se o método existe
        $this->assertTrue(method_exists($style, 'question'));
    }
}
