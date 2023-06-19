<?php

namespace Blinq\LLM\Contexts;

use Blinq\LLM\Entities\ChatMessage;

/**
 * Class EmbeddingContext
 *
 * This class represents the context of a chat conversation,
 * consisting of a message and an optional history of previous messages.
 *
 */
class EmbeddingContext
{
    public function __construct(
            public string $model,
            public string|array $input,
            public ?string $user = null,
            public bool $useCache = false
        )
    {
        
    }

    public function toArray() : array
    {
        $output = [
            'model' => $this->model,
            'input' => $this->input,
        ];

        if ($this->user) {
            $output['user'] = $this->user;
        }

        return $output;
    }
}
