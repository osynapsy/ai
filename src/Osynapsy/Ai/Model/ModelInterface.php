<?php
namespace Osynapsy\Ai\Model;

use Osynapsy\Ai\Prompt\PromptInterface;
use Osynapsy\Ai\Response\ChatResponseInterface;

/**
 * Description of ModelInterface
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
interface ModelInterface
{
    public function getId() : string;
    
    public function getEndpoint() : string;
    
    public function getResponse(array $rawresponse) : ChatResponseInterface;
    
    public function buildRequest(PromptInterface $prompt) : array;
}
