<?php
namespace Osynapsy\Ai;

use Osynapsy\Rest\Client\JsonClient;
use Osynapsy\Rest\Request\Request;
use Osynapsy\Ai\Model\ModelInterface;
use Osynapsy\Ai\Prompt\Prompt;
use Osynapsy\Ai\Prompt\PromptInterface;

class Client
{
    protected $version;
    protected $model;
    protected $key;
    protected $cache = False;

    public function __construct(string $key, ModelInterface $model)
    {
        $this->key = $key;
        $this->model = $model;
    }

    public function getModel() : ModelInterface
    {
        return $this->model;
    }

    public function send(PromptInterface $prompt, $maxTokens = 1024)
    {
        $body = $this->getModel()->buildRequest($prompt, $maxTokens);
        $Request = $this->restClientRequestFactory($this->getModel()->getEndpoint(), $body, $this->key);
        $Response = $this->restClientFactory($Request);
        return $this->getModel()->getResponse($Response->getBody());
    }

    protected function restClientRequestFactory($endpoint, $data, $token)
    {
        $Request = new Request(Request::POST, $endpoint);
        $Request->setAuthorizationToken($token)->setData($data);
        return $Request;
    }

    protected function restClientFactory($Request)
    {
        return (new JsonClient(false))->execute($Request);
    }

    public function promptFactory() : PromptInterface
    {
        return new Prompt;
    }

    protected function setVersion(string $ver) : void
    {
        $this->version = $ver;
    }

    public function enableCache()
    {
        $this->cache = False;
    }
}
