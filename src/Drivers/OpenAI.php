<?php

namespace Blinq\LLM\Drivers;

use Blinq\LLM\ApiClient;
use Blinq\LLM\Config\ApiConfig;
use Blinq\LLM\Contexts\ChatContext;
use Blinq\LLM\Contexts\CompletionContext;
use Blinq\LLM\Contexts\EmbeddingContext;
use Blinq\LLM\Entities\ApiResponse;
use Blinq\LLM\Entities\ChatResponse;
use Blinq\LLM\Entities\ChatStream;
use Blinq\LLM\Exceptions\ApiException;
use Blinq\LLM\OpenAI\OpenAIChatResponse;
use Blinq\LLM\OpenAI\OpenAIChatStream;
use Exception;

/**
 * This class handles the OpenAI API.
 */
class OpenAI extends ApiClient
{
    private string $apiKey;

    /**
     * Constructs a new OpenAI object.
     * 
     * @param ApiConfig $config The API configuration.
     * 
     * @throws Exception if the API key is not provided.
     */
    public function __construct(ApiConfig $config) {
        if (!$config->apiKey) {
            throw new Exception("API key is required for OpenAI");
        }
        $this->apiKey = $config->apiKey;
    }

    public function getHeaders(array $headers = []) : array
    {
        return array_merge([
            "Content-Type: application/json",
            "Authorization: Bearer $this->apiKey",
        ], $headers);
    }

    /**
     * Sends a chat message using the OpenAI API.
     * 
     * @param ChatContext $context The chat context.
     * 
     * @throws ApiException if an API error occurs.
     */
    public function chat(ChatContext $context) : ?OpenAIChatResponse {
        $messages = $context->getHistoryAndMessage();
        
        $options = array_merge([
            'model' => "gpt-3.5-turbo",
            'messages' => $messages,
            'stream' => count($this->streamHandlers) > 0,
        ], $context->options);

        
        $response = $this->sendJsonRequest("POST", "https://api.openai.com/v1/chat/completions", $options, $this->getHeaders());

        if ($response['error'] ?? null) {
            throw new ApiException($response['error']['message'] ?? 'Unknown error', $response['error']['type'] ?? null);
        }

        return is_array($response) ? $this->createChatResponse($response) : null;
    }

    /**
     * Gets the available models.
     *
     * @return array The available models.
     */
    public function models() : ApiResponse
    {
        $response = $this->sendJsonRequest("GET", "https://api.openai.com/v1/models", [], $this->getHeaders());

        return $this->createApiResponse($response);
    }

    /**
     * Get the embeddings for a text.
     *
     * @param EmbeddingContext $context
     * @return ApiResponse
     */
    public function embeddings(EmbeddingContext $context) : ApiResponse
    {
        $response = $this->sendJsonRequest("POST", "https://api.openai.com/v1/embeddings", $context->toArray(), $this->getHeaders());

        return $this->createApiResponse($response);
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

        return new OpenAIChatStream($curl, $data);
    }

    /**
     * Creates result data.
     * 
     * @param $data The parsed response body.
     * 
     * @return ChatResponse The created result data.
     */
    public function createChatResponse($data) : OpenAIChatResponse
    {
        return new OpenAIChatResponse($data);
    }
}
