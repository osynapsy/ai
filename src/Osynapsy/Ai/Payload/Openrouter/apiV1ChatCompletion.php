<?php
namespace Osynapsy\Ai\Payload\Openrouter;

use Osynapsy\Ai\Payload\OpenAi\v1ChatCompletion;

/**
 * Description of apiV1ChatCompletion
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class apiV1ChatCompletion extends v1ChatCompletion
{
    public function __construct(string $model, \Osynapsy\Ai\Message\MessageCollection $history)
    {
        parent::__construct($model, $history);
        $this->setThink('low');
        $this->setParameter('stream', true);
    }

    public function getEndpoint() : string
    {
        return '/api/v1/chat/completions';
    }

    public function setThink(string $level)
    {
        $this->setParameter('reasoning', (object) ['effort' => $level]);
        return $this;
    }
    
    public function isStreaming(): bool 
    {
        return !empty($this->getParameter('stream'));
    }
}
