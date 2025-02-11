<?php
namespace Manzano\CvdwCli\Inc;

use RuntimeException;

class CvdwException extends RuntimeException
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
