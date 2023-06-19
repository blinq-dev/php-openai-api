<?php

namespace Blinq\LLM\Contexts;

use Blinq\LLM\Entities\ChatMessage;

/**
 * Class ChatContext
 *
 * This class represents the context of a chat conversation,
 * consisting of a message and an optional history of previous messages.
 *
 */
class ChatContext
{
     /**
     * Constructs a new ChatContext object.
     * 
     * @param ChatMessage $message The current message.
     * @param ChatMessage[] $history The history of messages.
     * @param array $options Optional settings.
     * 
     * @throws \InvalidArgumentException if any item in $history is not of type Message
     */
    public function __construct(
            public ChatMessage $message,
            /**
            * @var Message[] $history
            */
            public array $history = [],
            public array $options = []
        )
    {
        foreach ($history as $item) {
            if (!$item instanceof ChatMessage) {
                throw new \InvalidArgumentException('All items in $history must be of type Message');
            }
        }
    }

    /**
     * This function merges the history and the current message.
     *
     * @return array - An array that contains all the messages including the history and the current message
     */
    public function getHistoryAndMessage(): array
    {
        return array_merge(array_map(fn($item) => $item->toArray(), $this->history), [$this->message->toArray()]);
    }
}
