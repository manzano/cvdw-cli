<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Inc\CvdwException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testCvdwExceptionCanBeInstantiated()
    {
        $exception = new CvdwException();
        $this->assertInstanceOf(CvdwException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testCvdwExceptionWithMessage()
    {
        $message = "Erro customizado do CVDW";
        $exception = new CvdwException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testCvdwExceptionWithMessageAndCode()
    {
        $message = "Erro com código";
        $code = 500;
        $exception = new CvdwException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testCvdwExceptionWithPreviousException()
    {
        $previousException = new \Exception("Erro anterior");
        $message = "Erro com exceção anterior";
        $code = 404;

        $exception = new CvdwException($message, $code, $previousException);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testCvdwExceptionCanBeThrown()
    {
        $this->expectException(CvdwException::class);
        $this->expectExceptionMessage("Exceção lançada");

        throw new CvdwException("Exceção lançada");
    }

    public function testCvdwExceptionCanBeCaught()
    {
        try {
            throw new CvdwException("Exceção capturada");
            $this->fail("A exceção deveria ter sido lançada");
        } catch (CvdwException $e) {
            $this->assertEquals("Exceção capturada", $e->getMessage());
            $this->assertEquals(0, $e->getCode());
        }
    }
}
