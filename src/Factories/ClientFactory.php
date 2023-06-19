<?php

namespace Blinq\LLM\Factories;

use Blinq\LLM\Config\ApiConfig;
use Blinq\LLM\Drivers\OpenAI;
use Exception;

class ClientFactory
{
    public static function create(ApiConfig $config) {
        switch ($config->apiName) {
            case 'openai':
                return new OpenAI($config);
            default:
                throw new Exception("Invalid API name: $config->apiName");
        }
    }
}
