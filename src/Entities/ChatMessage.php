<?php

namespace Blinq\LLM\Entities;

/**
 * Class ChatMessage
 *
 * Represents a single message in a chat conversation
 *
 * @package Blinq\LLM\Entities
 */
class ChatMessage
{
    /**
     * ChatMessage constructor.
     *
     * @param string|null $role The role of the message sender (e.g., "system", "user", "assistant")
     * @param string|null $content The content of the message
     * @param string|null $name The name of the user (optional)
     * @param array|null $function_call The function call to be made (optional)
     */
    public function __construct(
        public ?string $role, 
        public ?string $content, 
        public ?string $name = null,
        public ?array $function_call = null
    )
    {
    }

    /**
     * Convert the message object to an array
     *
     * @return array An array representation of the message
     */
    public function toArray()
    {
        $data = [
            'role' => $this->role,
            'content' => $this->content
        ];

        if ($this->name) {
            $data['name'] = $this->name;
        }

        if ($this->function_call) {
            $data['function_call'] = $this->function_call;
        }

        return $data;
    }

    /**
     * Convert an array to a message object
     *
     * @param array $message The array message to use for creating the message
     * @return ChatMessage
     */
    public static function fromArray(array $message) : ChatMessage
    {
        return new ChatMessage($message['role'] ?? null, $message['content'] ?? null, $message['name'] ?? null, $message['function_call'] ?? null);
    }    

    /**
     * Convert an array list to a message objects
     *
     * @param array $history The list of array messages to use for creating the message
     * @return array
     */
    public static function fromHistoryArray(array $history) : array
    {
        $messages = [];

        foreach($history as $item) {
            $messages[] = static::fromArray($item);
        }

        return $messages;
    }
}