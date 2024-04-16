<?php
namespace Manzano\CvdwCli;

use RuntimeException;

class CvdwException extends RuntimeException
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
