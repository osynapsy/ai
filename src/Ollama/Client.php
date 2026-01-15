<?php
namespace Osynapsy\AI\Ollama;

use Osynapsy\Rest\Client\JsonClient;
use Osynapsy\Rest\Request\Request;
use Osynapsy\AI\Ollama\Model\ModelInterface;
use Osynapsy\AI\Ollama\Prompt\Prompt;
use Osynapsy\AI\Ollama\Prompt\PromptInterface;

class Client
{
    protected $version;
    protected $model;
    protected $key;
    protected $host;
    protected $cache = False;
    protected $endpoint;

    public function __construct(string $host, string $key, ?ModelInterface $model)
    {
        $this->host = $host;
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
        $Request = $this->restClientRequestFactory($this->host . $this->getModel()->getEndpoint(), $body, $this->key);        
        $RestResponse = $this->restClientFactory($Request);        
        return $this->getModel()->getResponse($RestResponse->getBody());
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

    public function promptFactory() : promptInterface
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
