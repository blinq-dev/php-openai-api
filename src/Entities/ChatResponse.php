<?php

namespace Blinq\LLM\Entities;

class ChatResponse extends ApiResponse
{
    public function getMessage() : ?ChatMessage {
        return $this->data['message'] ?? null;
    }
}
