<?php

namespace Blinq\LLM\OpenAI;

use Blinq\LLM\Entities\ChatMessage;
use Blinq\LLM\Entities\ChatStream;
use Blinq\LLM\Exceptions\ApiException;

/**
 * Class OpenAIChatStream
 *
 * Represents a chat response stream returned by OpenAI.
 *
 * @package Blinq\LLM\OpenAI
 */
class OpenAIChatStream extends ChatStream
{
    /**
     * Get the message related to the chat response stream.
     *
     * @return ChatMessage|null The message related to the chat response stream.
     */
    public function getMessage() : ?ChatMessage
    {
        $choice = $this->data['choices'][0] ?? [];

        if ($this->data['error'] ?? null) {
            throw new ApiException($this->data['error']['message'] ?? 'Unknown error', $this->data['error']['type'] ?? null);
        }

        $message = $choice['message'] ?? $choice['delta'] ?? null;

        if ($message) {
            return ChatMessage::fromArray($message);
        }

        return null;
    }
}
