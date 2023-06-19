# PHP OpenAI API
(Written by ChatGPT)

This is a library for interacting with Language Learning Models (LLMs), built with a focus on AI-driven chat functionalities. 
The library currently supports OpenAI's GPT-3.5-turbo, but it can be extended to support other LLMs as well.

## Features
- **Cross-API Compatibility**: The library is designed to work with different language learning models, including OpenAI's GPT-3.5-turbo.

- **Ease of Use**: With simple configuration and intuitive API, this library enables developers to easily interact with complex language learning models.

- **Streaming Support**: The library offers streaming capabilities that allow real-time interactions with language learning models.

- **Chat History**: The library maintains a history of the chat session which can be retrieved and analyzed anytime.

- You can add support for other AI providers by extending the `ApiClient` class and implementing the required methods.

- **Error Handling**: The library provides robust error handling to deal with potential issues during interactions with language learning models.

- **Documentation**: Comprehensive documentation is available to guide developers in using the library.

- **Community Support**: Issues and feature requests can be raised in the GitHub repository, and the active community is open to discussions and improvements.

- **License**: The library is open source and is licensed under the MIT License.

- **Support for Future AI Models**: The architecture of the library is designed to support any future language learning models seamlessly.

- It is clean, well-structured, and includes comments for clarity.

## Getting Started

Firstly, you need to install the library in your PHP project. 

```
composer require blinq/openai
```

## Usage

### Chat with OpenAI's GPT-3.5-turbo

You need to initialize a client object using `ApiClient` configuration. The following example demonstrates a chat session with OpenAI's GPT-3.5-turbo.

```php
<?php

use Blinq\LLM\Config\ApiConfig;
use Blinq\LLM\Entities\ChatMessage;
use Blinq\LLM\Client;

$config = new ApiConfig('openai', 'your-api-key');
$client = new Client($config);

// Set the system message. This is optional.
$client->setSystemMessage("You are a nice chatbot");

// User message
$client->chat("Hello, how are you?");

// Get the last message
$message = $client->getLastMessage();

echo $message->content; // Prints "I am fine, thank you. How are you?"

// Get the chat history
$history = $client->getHistory();

foreach ($history as $message) {
    echo $message->content;
}
```

The client by default holds a history of the chat session. You can retrieve the history using the `getHistory()` method. The history is an array of `ChatMessage` objects, which have the following properties:

- `content`: The content of the message
- `role`: The role of the message (user or system)

You can reset the chat history using the `resetHistory()` method.

Or use the `getLastMessageAndClearHistory()` method to get the last message and clear the history.

### Chat with Stream

If the streaming option is used, the following example shows how to add a handler to deal with the stream of chat messages.

```php
<?php

use Blinq\LLM\Config\ApiConfig;
use Blinq\LLM\Entities\ChatStream;
use Blinq\LLM\Client;

$config = new ApiConfig('openai', 'your-api-key');
$client = new Client($config);

$client->addStreamHandler(function (ChatStream $stream) {
    // Handle the stream data
    echo $stream->getMessage()?->content; // Prints the partial message content
    
    // The $stream object has a 'done' property to check if the stream is done
    if ($stream->done) {
        // Do something when the stream is done
    }
});

$client->chat("Hello, how are you?", "user", ['stream' => true]);
```

## Customization

The `ApiClient` class is abstract and can be extended to create custom drivers for different APIs. 

The current implementation is for OpenAI, but if you'd like to use a different AI provider, you can create a new class that extends `ApiClient`, and implement the required methods (`chat()`, `completion()`, etc.).

For any issues or additional features, feel free to open an issue in the GitHub repository.

## License
This project is licensed under the MIT License. See the LICENSE file for details.
