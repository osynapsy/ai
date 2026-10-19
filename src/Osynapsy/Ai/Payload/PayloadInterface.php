<?php
namespace Osynapsy\Ai\Payload;

use Osynapsy\Ai\Message\MessageCollection;
use Osynapsy\Ai\Tools\ToolExecutor;
use Osynapsy\Ai\Response\ResponseInterface;

/**
 * Description of PayloadInterface
 *
 * @author Pietro Celeste
 */
interface PayloadInterface
{
    public function __construct(string $model, MessageCollection $history);

    public function getEndpoint() : string;

    public function getHistory() : MessageCollection;

    public function getToolExecutor() : ToolExecutor;

    public function setToolExecutor(ToolExecutor $toolExecutor) : self;

    public function responseFactory(array $rawresponse) : ResponseInterface;

    public function jsonSerialize(): mixed;
}
