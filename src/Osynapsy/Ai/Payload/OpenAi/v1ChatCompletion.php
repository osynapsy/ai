<?php
namespace Osynapsy\Ai\Payload\OpenAi;

use Osynapsy\Ai\Payload\AbstractPayload;
use Osynapsy\Ai\Response\ChatResponse;

class v1ChatCompletion extends AbstractPayload
{
    private $decoder = null;

    public function __construct(string $model, \Osynapsy\Ai\Message\MessageCollection $history)
    {
        parent::__construct($model, $history);
        $this->decoder = new ResponseStreamDecoder;
    }

    public function getEndpoint() : string
    {
        return '/v1/chat/completions';
    }

    protected function toolsAdapter(array $tools)
    {
        $formattedTools = [];
        foreach ($tools as $tool) {
            $parameters = $tool['parameters'] ?? $tool['function']['parameters'] ?? [];

            // Se properties è vuoto o un array vuoto, forziamo il cast a (object)[]
            // così json_encode lo serializza come {} e non []
            if (isset($parameters['properties']) && empty($parameters['properties'])) {
                $parameters['properties'] = (object) [];
            }

            $formattedTools[] = [
                'type' => 'function',
                'function' => [
                    'name' =>  str_replace('.', '_', $tool['name'] ?? $tool['function']['name']),
                    'description' => $tool['description'] ?? $tool['function']['description'] ?? '',
                    'parameters' => $parameters
                ]
            ];
        }
        return $formattedTools;
    }

    public function messageHistorySerialize($rawmessages): mixed
    {
        $cleanmessages = parent::messageHistorySerialize($rawmessages);
        $messages = [];
        foreach($cleanmessages as $rawmessage) {
            $message = $rawmessage;
            if ($message['role'] === 'assistant' && !empty($message['tool_calls']) && is_object($message['tool_calls'])) {
                $toolCall = $message['tool_calls'];
                $arguments = $toolCall->getArguments();
                $message['tool_calls'] =  [[
                    'id' => $toolCall->getId(),
                    'type' => 'function',
                    'function' => [
                        'name' => $toolCall->getTool(),
                        'arguments' => is_string($arguments)
                        ? $arguments
                        : json_encode(
                            $arguments,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                        )
                    ]
                ]];
            }
            $messages[] = $message;
        }
        return $messages;
    }

    public function responseFactory(string $rawresponse): \Osynapsy\Ai\Response\ResponseInterface
    {
        $response = !$this->isStreaming() ? json_decode($rawresponse, true) : $this->decoder->merge($this->decoder->decodeBody($rawresponse));
        return new ChatResponse($response);
    }

    public function extractChunk($rawLine)
    {
        $row = $this->decoder->decodeLine($rawLine);   // ResponseStreamDecoder: NDJSON o SSE
        if ($row === null) {
            return null;
        }
        // OpenAI-compatibile (delta) oppure Ollama nativo (message)
        $part = $row['choices'][0]['delta'] ?? $row['message'] ?? [];

        if (($part['content'] ?? '') !== '') {
            return ['type' => 'content', 'text' => $part['content']];
        }
        $reasoning = $part['reasoning'] ?? $part['thinking'] ?? '';
        if ($reasoning !== '') {
            return ['type' => 'reasoning', 'text' => $reasoning];
        }
        return null;
    }

    public function setTemperature($value)
    {
        $this->setParameter('temperature', $value);
        return $this;
    }

    public function setStream($value)
    {
        $this->setParameter('stream', $value);
        return $this;
    }

    public function setThink(string $effort)
    {
        $this->setParameter('reasoning_effort', $effort);
        return $this;
    }

    public function isStreaming(): bool
    {
        return !empty($this->getParameter('stream'));
    }
}
