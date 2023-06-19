<?php

namespace Blinq\LLM;

use Blinq\LLM\Config\ApiConfig;
use Blinq\LLM\Contexts\ChatContext;
use Blinq\LLM\Contexts\CompletionContext;
use Blinq\LLM\Contexts\EmbeddingContext;
use Blinq\LLM\Entities\ApiResponse;
use Blinq\LLM\Entities\ChatMessage;
use Blinq\LLM\Entities\ChatStream;
use Blinq\LLM\Entities\ChatResponse;
use Blinq\LLM\Factories\ClientFactory;
use Blinq\LLM\Traits\WithDiskCaching;

/**
 * Class Client
 *
 * The main client class for interacting with language learning models.
 *
 * @package Blinq\LLM
 */
class Client
{
    use WithDiskCaching;
    
    protected ApiClient $driver;
    protected array $history = [];
    protected array $streamMessages = [];
    protected array $cache = [];

    /**
     * Client constructor.
     *
     * @param ApiConfig $config
     * The configuration object containing necessary information such as apiName, apiKey, etc.
     */
    public function __construct(ApiConfig $config) {
        $this->driver = ClientFactory::create($config);

        $this->addResultHandler(function (ChatResponse $data) {
            if ($message = $data->getMessage()) {
                $this->history[] = $message;
            }
        });

        // Add a stream handler to handle the response stream
        $this->addStreamHandler(function (ChatStream $data) {
            if ($message = $data->getMessage()) {
                $this->streamMessages[] = $message;
            }

            if ($data->done) {
                $message = $this->combineStreamMessageToResultMessage();
                
                // Clear tmp stream messages
                $this->streamMessages = [];

                // Trigger the result handlers when the stream is done
                foreach($this->driver->resultHandlers as $handler) {
                    $resultData = new ChatResponse([
                        "message" => $message,
                    ]);

                    $handler($resultData);
                }
            }
        });
    }

    public function combineStreamMessageToResultMessage() : ChatMessage
    {
        $message = new ChatMessage("user", "");

        /**
         * @var ChatMessage $streamMessage
         */
        foreach($this->streamMessages as $streamMessage) {
            if ($streamMessage->name ?? null) {
                $message->name = $streamMessage->name;
            }

            if ($streamMessage->role ?? null) {
                $message->role = $streamMessage->role;
            }

            if ($streamMessage->content ?? null) {
                $message->content .= $streamMessage->content;
            }

            if ($streamMessage->function_call ?? []) {
                if (! $message->function_call) {
                    $message->function_call = [];
                }

                foreach($streamMessage->function_call as $key => $value) {
                    if (!isset($message->function_call[$key])) {
                        $message->function_call[$key] = $value;
                    } else {
                        $message->function_call[$key] .= $value;
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Sets the system message in the chat history.
     *
     * @param string $message
     * The system message to be set in the chat history.
     *
     * @return self
     */
    public function setSystemMessage(string $message) : self
    {
        // Find if there is a system message in the history
        foreach($this->history as $key => $item) {
            if ($item->role === "system") {
                $this->history[$key] = new ChatMessage("system", $message);
                return $this;
            }
        }

        $this->history[] = new ChatMessage("system", $message);

        return $this;
    }

    /**
     * Sets the chat history.
     *
     * @param array $history
     * The chat history to be set.
     *
     * @return self
     */
    public function setHistory(array $history) : self
    {
        $this->history = $history;

        return $this;
    }

    public function getHistory()
    {
        return $this->history;
    }

    public function addHistory(ChatMessage $message)
    {
        $this->history[] = $message;

        return $this;
    }

    public function clearHistory(bool $exceptSystemMessage = true)
    {
        if ($exceptSystemMessage) {
            $this->history = array_filter($this->history, function ($item) {
                return $item->role === "system";
            });
        } else {
            $this->history = [];
        }

        return $this;
    }

    public function getLastMessage() : ?ChatMessage
    {
        return end($this->history) ?: null;
    }

    public function getLastMessageAndClearHistory() : ?ChatMessage
    {
        $message = $this->getLastMessage();

        $this->clearHistory();

        return $message;
    }

    /**
     * Sends a chat message.
     *
     * @param string $message
     * The message to be sent.
     *
     * @param string $role
     * The role of the message sender. Default is "user".
     *
     * @param array $options
     * The options for the chat. Default is an empty array.
     *
     * @return self
     */
    public function chat(string $message, $role = "user", array $options = []) : self {
        $message = new ChatMessage($role, $message);
        $chatContext = new ChatContext($message, $this->history, $options);

        $this->history[] = $message;
        
        $response = $this->driver->chat($chatContext);

        if ($response) {
            foreach($this->driver->resultHandlers as $handler) {
                $handler($response);
            }
        }

        return $this;
    }

    /**
     * Sends a completion request.
     *
     * @param CompletionContext $context
     * The context of the completion request.
     */
    public function completion(CompletionContext $context) : ApiResponse {
        return $this->driver->completion($context);
    }

    public function embeddings(EmbeddingContext $context) : ApiResponse
    {
        if ($context->useCache) {
            $result = $this->getCache($context->toArray());
            
            if ($result) {
                return $this->driver->createApiResponse($result);
            }
        }
        
        $result = $this->driver->embeddings($context);
        
        if ($context->useCache) {
            $this->setCache($context->toArray(), $result->raw());
        }

        return $result;
    }

    /**
     * Adds a stream handler.
     *
     * @param callable $handler
     * The handler for the stream.
     *
     * @return self
     */
    public function addStreamHandler(callable $handler) : self
    {
        $this->driver->addStreamHandler($handler);

        return $this;
    }

    /**
     * Adds a result handler.
     *
     * @param callable $handler
     * The handler for the result.
     *
     * @return self
     */
    public function addResultHandler(callable $handler) : self
    {
        $this->driver->addResultHandler($handler);

        return $this;
    }
}
