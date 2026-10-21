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
        $this->setThink(false);
        $this->setParameter('stream', false);
    }

    public function getEndpoint() : string
    {
        return '/api/v1/chat/completions';
    }

    public function setThink(bool $v)
    {
        $this->setParameter('reasoning', (object) ['enabled' => $v]);
    }
}
