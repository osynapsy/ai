<?php
namespace Osynapsy\Ai;

use Osynapsy\Rest\Client\JsonClient;
use Osynapsy\Rest\Request\Request;
use Osynapsy\Ai\Payload\PayloadInterface;

class Client
{
    protected $host;
    protected $key;

    public function __construct(string $host, string $key)
    {
        $this->host = $host;
        $this->key = $key;
    }

    public function send(PayloadInterface $payload)
    {
        $iteration = 0;
        $maxIterations = $payload->hasTool() ? $payload->getToolExecutor()->getMaxToolIterations() : 1;
        while ($iteration < $maxIterations) {
            $iteration++;
            // 1. Costruisce ed esegue la chiamata HTTP tramite il formatter
            $Request = $this->restClientRequestFactory($this->host . $payload->getEndpoint(), $payload->jsonSerialize(), $this->key);

                // Debug immediato del JSON esatto che sta partendo verso Ollama:
            file_put_contents(sprintf('/tmp/ollama_payload_%s.json',date('Y-m-d_H:i:s')), json_encode($payload->jsonSerialize(), JSON_PRETTY_PRINT));
            file_put_contents('/tmp/ollama_payload.json', json_encode($payload->jsonSerialize(), JSON_PRETTY_PRINT));
            $Response = $this->restClientFactory($Request);

            /** @var ChatResponseInterface $chatResponse */
            $chatResponse = $payload->responseFactory($Response->getBody());
            file_put_contents(sprintf('/tmp/aichatresponse.txt'), print_r($chatResponse, true));
            // 2. Se non ci sono richieste di tool o non c'è l'executor, restituisce la risposta
            if ($chatResponse->hasError() || !$payload->hasTool() || !$chatResponse->hasToolRequest()) {
                $payload->getHistory()->addAssistant($chatResponse->getContent());
                return $chatResponse;
            }
            // 3. L'LLM ha richiesto un tool
            //Recupero la tool call request
            $toolCall = $chatResponse->getToolRequest();
            // 3.1. L'assistant richiede un tool: passiamo direttamente l'oggetto ToolCall
            $payload->getHistory()->addAssistant($chatResponse->getContent(), $toolCall);
            // 3.2. Eseguiamo il tool
            $toolResult = $payload->getToolExecutor()->execute($toolCall);
            // 3.3. Aggiungiamo il risultato passando l'oggetto ToolCall e il risultato
            $payload->getHistory()->addToolResult($toolCall, $toolResult);
        }

        throw new \RuntimeException("Superato il limite massimo di iterazioni ({$maxIterations()}) per i tool.");
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
}
