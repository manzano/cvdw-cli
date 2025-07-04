<?php

use PHPUnit\Framework\TestCase;
use Manzano\CvdwCli\Inc\CvdwException;

class CvdwExceptionTest extends TestCase
{
    public function testCvdwExceptionConstructor()
    {
        $message = "Erro de teste";
        $code = 500;
        $previous = new Exception("Erro anterior");
        
        $exception = new CvdwException($message, $code, $previous);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
    
    public function testCvdwExceptionWithDefaultValues()
    {
        $exception = new CvdwException();
        
        $this->assertEquals("", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
    
    public function testCvdwExceptionWithMessageOnly()
    {
        $message = "Apenas mensagem";
        $exception = new CvdwException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
    
    public function testCvdwExceptionInheritance()
    {
        $exception = new CvdwException("Teste");
        
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(Throwable::class, $exception);
    }
} 