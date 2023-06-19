<?php

namespace Blinq\LLM\Config;

class ApiConfig
{
    public function __construct(
        public string $apiName, 
        public ?string $apiKey = null
    )
    {
        
    }
}
