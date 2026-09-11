<?php
namespace Osynapsy\AI\OpenAI;

use Osynapsy\Rest\Client\JsonClient;
use Osynapsy\Rest\Request\Request;
use Osynapsy\AI\OpenAI\Model\ModelInterface;
use Osynapsy\AI\OpenAI\Prompt\Prompt;
use Osynapsy\AI\OpenAI\Prompt\PromptInterface;

class ChatGpt
{
    protected $version;
    protected $model;
    protected $Prompt;
    protected $dataset;
    protected $key;
    protected $cache = False;

    public function __construct(string $key, ?ModelInterface $model = null)
    {
        $this->key = $key;
        $this->model = $model ?? new Model\Gpt_4o_mini();
    }

    public function getModel() : ModelInterface
    {
        return $this->model;
    }

    public function promptFactory() : promptInterface
    {
        return new Prompt;
    }

    protected function setVersion(string $ver)
    {
        $this->version = $ver;
        return $this;
    }

    public function enableCache()
    {
        $this->cache = False;
        return $this;
    }

    public function prompt(string $message, string $role = 'user')
    {
        if (empty($this->Prompt)) {
            $this->Prompt = new Prompt;
        }
        $this->Prompt->add($role, $message);
        return $this;
    }

    public function getRespose($maxTokens = 1024)
    {
        $body = $this->getModel()->buildRequest($this->prompt, $maxTokens);
        if (!empty($this->dataset)) {
            $body .= PHP_EOL. '----DATASET-----'.PHP_EOL;
            $body .= json_encode($this->dataset);
        }
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

    protected function setDataset($dataset)
    {
        $this->dataset = $dataset;
        return $this;
    }
}
