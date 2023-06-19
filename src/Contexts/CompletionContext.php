<?php

namespace Blinq\LLM\Contexts;

/**
 * This class handles completion contexts.
 */
class CompletionContext
{
    /**
     * Constructs a new CompletionContext object.
     * 
     * @param string $prompt The completion prompt.
     * @param array $options Optional settings.
     */
    public function __construct(public string $prompt, public array $options = [])
    {        
    }
}
