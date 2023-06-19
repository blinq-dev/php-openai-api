<?php

namespace Blinq\LLM\Exceptions;

use Exception;
use Throwable;

class ApiException extends Exception
{
    public ?string $type = null;

    public function __construct(string $message, string $type, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->type = $type;
    }
}