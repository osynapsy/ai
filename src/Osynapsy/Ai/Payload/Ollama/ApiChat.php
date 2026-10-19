<?php
namespace Osynapsy\Ai\Payload\Ollama;

use Osynapsy\Ai\Payload\AbstractPayload;
use Osynapsy\Ai\Response\ChatResponse;
use Osynapsy\Ai\Message\MessageCollection;

/**
 * Description of ApiChat
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class ApiChat extends AbstractPayload
{
    public function __construct(string $model, MessageCollection $history)
    {
        parent::__construct($model, $history);
        $this->setParameter('stream', false);
    }

    public function getEndpoint() : string
    {
        return '/api/chat';
    }

    protected function toolsAdapter(array $tools)
    {
        $formattedTools = [];
        foreach ($tools as $tool) {
            $parameters = $tool['parameters'] ?? $tool['function']['parameters'] ?? [];

            // TRUCCO CRUCIALE: Se properties è vuoto (o un array vuoto),
            // lo castiamo a (object)[] così json_encode lo serializza come "{}" anziché "[]"
            if (isset($parameters['properties']) && empty($parameters['properties'])) {
                $parameters['properties'] = (object) [];
            }

            $formattedTools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool['name'] ?? $tool['function']['name'] ?? '',
                    'description' => $tool['description'] ?? $tool['function']['description'] ?? '',
                    'parameters' => $parameters
                ]
            ];
        }
        return $formattedTools;
    }

    public function setFormat(?string $format): self
    {
        return $this->setParameter('format', $format);        
    }

    public function setTemperature(float $temperature): self
    {
        return $this->setOption('temperature', $temperature);        
    }

    public function setMaxContext(?int $maxContext): self
    {
        return $this->setOption('num_ctx', $maxContext);        
    }

    public function setMaxTokens(?int $maxTokens): self
    {
        return $this->setOption('num_predict', $maxTokens);        
    }

    public function setKeepAlive($value)
    {
        return $this->setParameter('keep_alive', $value);        
    }

    public function setRepeatPenality(float $val)
    {
        return $this->setOption('repeat_penality', $val);        
    }

    public function responseFactory(array $rawresponse): \Osynapsy\Ai\Response\ResponseInterface
    {
        return new ChatResponse($rawresponse);
    }
}
