<?php
namespace Osynapsy\Ai;

use Osynapsy\Rest\Client\JsonClient;
use Osynapsy\Rest\Request\Request;
use Osynapsy\Ai\Model\ModelInterface;
use Osynapsy\Ai\Prompt\Prompt;
use Osynapsy\Ai\Prompt\PromptInterface;
use Osynapsy\Ai\Tools\ToolExecutor;

class Client
{
    protected $version;
    protected $host;
    protected $model;
    protected $key;
    protected $cache = False;
    protected ?ToolExecutor $toolExecutor = null;
    protected int $maxToolIterations = 10;

    public function __construct(string $host, string $key, ModelInterface $model)
    {
        $this->host = $host;
        $this->key = $key;
        $this->model = $model;
    }

    public function getModel() : ModelInterface
    {
        return $this->model;
    }

    public function setToolExecutor(ToolExecutor $toolExecutor): self
    {
        $this->toolExecutor = $toolExecutor;
        return $this;
    }

    public function send(PromptInterface $prompt, $maxTokens = 1024)
    {
        $iteration = 0;
        while ($iteration < $this->maxToolIterations) {
            $iteration++;

            // 1. Costruisce ed esegue la chiamata HTTP tramite il formatter
            $body = $this->getModel()->buildRequest($prompt, $maxTokens);           
            $Request = $this->restClientRequestFactory($this->host . $this->getModel()->getEndpoint(), $body, $this->key);
            $Response = $this->restClientFactory($Request);

            /** @var ChatResponseInterface $chatResponse */
            $chatResponse = $this->getModel()->getResponse($Response->getBody());
            // 2. Se non ci sono richieste di tool o non c'è l'executor, restituisce la risposta
            if ($chatResponse->hasError() || !$this->toolExecutor || !$chatResponse->hasToolRequest()) {
                return $chatResponse;
            }
            //$prompt->clearContentSections();
            // 3. L'LLM ha richiesto un tool
            //Recupero la tool call request
            $toolCall = $chatResponse->getToolRequest();
             // 1. Registriamo prima il messaggio dell'Assistant che ha richiesto il tool
            // NOTA: passa il content (spesso stringa vuota) E la struttura nativa delle tool_calls
            // 1. L'assistant richiede un tool: passiamo direttamente l'oggetto ToolCall
            $prompt->addAssistantToolCall($chatResponse->getContent(), $toolCall);

            // 2. Eseguiamo il tool
            $toolResult = $this->toolExecutor->execute($toolCall);

            // 3. Aggiungiamo il risultato passando l'oggetto ToolCall e il risultato
            $prompt->addToolResult($toolCall, $toolResult);            
        }

        throw new \RuntimeException("Superato il limite massimo di iterazioni ({$this->maxToolIterations}) per i tool.");
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
