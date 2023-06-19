<?php

namespace Blinq\LLM\Entities;


class ChatStream extends ApiResponse
{
    public function __construct(
        public $curl,
        public ?array $data,
        public bool $done = false,
    )
    {
        parent::__construct($data);
    }

    public function getMessage() : ?ChatMessage {
        return $this->data['message'] ?? null;
    }
}
