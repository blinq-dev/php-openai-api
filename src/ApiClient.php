<?php

namespace Blinq\LLM;

use Blinq\LLM\Contexts\ChatContext;
use Blinq\LLM\Contexts\CompletionContext;
use Blinq\LLM\Contexts\EmbeddingContext;
use Blinq\LLM\Entities\ApiResponse;
use Blinq\LLM\Entities\ChatResponse;
use Blinq\LLM\Entities\ChatStream;
use Blinq\LLM\Traits\WithCurlRequests;

/**
 * Base class for API clients.
 * Handles sending JSON requests, streaming, and stream data creation.
 */
abstract class ApiClient
{
    use WithCurlRequests;

    public array $streamHandlers = [];
    public array $resultHandlers = [];

    /**
     * Adds a new stream handler to the client.
     *
     * @param callable $handler The handler function to add.
     */
    public function addStreamHandler(callable $handler)
    {
        $this->streamHandlers[] = $handler;
    }

    /**
     * Adds a new result handler to the client.
     *
     * @param callable $handler The handler function to add.
     */
    public function addResultHandler(callable $handler)
    {
        $this->resultHandlers[] = $handler;
    }
    
    /**
     * Handles incoming streaming data.
     *
     * @param resource $curl The cURL resource.
     * @param string $streamData The incoming stream data.
     * @return int The length of the handled data.
     */
    protected function handleStream($curl, $streamData) {
        foreach(explode("data: ", $streamData) as $bodyParsed) {
            if (!$bodyParsed) continue;

            $bodyParsed = (string) trim($bodyParsed);
            $data = $this->createChatStream($curl, $bodyParsed);

            if ($bodyParsed === "[DONE]") {
                $data->done = true;
            }

            foreach($this->streamHandlers as $handler) {
                $handler($data);
            }
        }

        return strlen($streamData);
    }

    /**
     * Send a chat message via the API.
     *
     * @param ChatContext $context The context for the chat message.
     * @return ?ChatResponse The response from the API.
     */
    public function chat(ChatContext $context) : ?ChatResponse {
        throw new \Exception("Not implemented");
    }

    /**
     * Request a completion from the API.
     *
     * @param CompletionContext $context The context for the completion request.
     */
    public function completion(CompletionContext $context) : ApiResponse {
        throw new \Exception("Not implemented");
    }

    public function embeddings(EmbeddingContext $context) : ApiResponse {
        throw new \Exception("Not implemented");
    }

    /**
     * Creates stream data.
     * 
     * @param $curl The cURL handle.
     * @param $data The parsed response body.
     * 
     * @return ChatStream The created stream data.
     */
    public function createChatStream($curl, $data) : ChatStream 
    {
        try {
            $data = json_decode($data, true);
        } catch (\Throwable $th) {
            $data = null;
        }

        return new ChatStream($curl, $data);
    }

    /**
     * Creates result data.
     * 
     * @param $data The parsed response body.
     * 
     * @return ChatResponse The created result data.
     */
    public function createChatResponse($data) : ChatResponse
    {
        return new ChatResponse($data);
    }

    public function createApiResponse($data) : ApiResponse
    {
        return new ApiResponse($data);
    }
}
