<?php

namespace Blinq\LLM\Entities;

class ApiResponse
{
    public function __construct(
        protected ?array $data,
    )
    {
        
    }

    public function raw() : ?array
    {
        return $this->data;
    }
}
