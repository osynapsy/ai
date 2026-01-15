<?php
namespace Osynapsy\AI\Ollama\Model;

use Osynapsy\AI\Ollama\Prompt\PromptInterface;
use Osynapsy\AI\Ollama\Response\ChatResponse;
use Osynapsy\AI\Ollama\Response\ResponseInterface;

class Qwen_2_5 implements ModelInterface
{
    protected $tag;
    protected $format;
    
    public function __construct($tag = null)
    {
        $this->tag = $tag;
    }
    
    public function getId() : string
    {
        return 'qwen2.5';
    }
    
    public function getEndpoint() : string
    {
        return '/api/generate';
    }

    public function buildRequest(PromptInterface $prompt) : array
    {
        $model = implode(':', [$this->getId(), $this->tag ?? 'latest']);        
        $body = [
            'model' => $model,
            'prompt' => $prompt->get(),
            'stream' => false
        ];
        if (!empty($this->format)) {
            $body['format'] = $this->format;
        }
        if (!empty($maxTokens)) {
            //$body['max_tokens'] = $maxTokens;
        }
        return $body;
    }

    public function useJson(): bool
    {
        return true;
    }

    public function getResponse(array $rawresponse) : ResponseInterface
    {
        return new ChatResponse($rawresponse);
    }
    
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
