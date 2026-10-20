<?php
namespace Osynapsy\Ai\Payload\OpenAi;

use Osynapsy\Ai\Payload\AbstractPayload;
use Osynapsy\Ai\Response\ChatResponse;

class v1ChatCompletion extends AbstractPayload
{
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
        $messages = [];
        foreach($rawmessages as $rawmessage) {
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

    public function responseFactory(array $rawresponse): \Osynapsy\Ai\Response\ResponseInterface
    {
        return new ChatResponse($rawresponse);
    }

    public function setTemperature($value)
    {
        return $this->setParameter('temperature', $value);
    }
}
