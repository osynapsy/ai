<?php
namespace Osynapsy\Ai\Response;

use Osynapsy\Ai\Tools\ToolCall;

class ChatResponse implements \Osynapsy\Ai\Response\ResponseInterface
{
    protected array $raw;
    protected ?array $error;

    public function __construct(array $response)
    {
        $this->raw = $response;
        $this->error = $response['error'] ?? null;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getErrorMessage(): string
    {
        return $this->error['message'] ?? '';
    }

    public function getErrorCode(): string
    {
        return $this->error['code'] ?? '';
    }

    public function getContent(): string
    {
        // Chat completion classico
        if (isset($this->raw['choices'][0]['message']['content'])) {
            $content = $this->raw['choices'][0]['message']['content'];

            if (is_string($content)) {
                return $content;
            }

            if (is_array($content)) {
                return implode(
                    '',
                    array_column(
                        array_filter(
                            $content,
                            fn ($c) => ($c['type'] ?? '') === 'output_text'
                        ),
                        'text'
                    )
                );
            }
        }

        // 2. Formato Ollama chat (message[content])
        if (isset($this->raw['message']['content'])) {
            $content = $this->raw['message']['content'];
            if (is_string($content)) {
                return $content;
            }
        }

        // 3. Formato Ollama generate (response)
        if (isset($this->raw['response'])) {
            return (string) $this->raw['response'];
        }

        // 4. Responses API / generiche
        if (isset($this->raw['output_text'])) {
            return (string) $this->raw['output_text'];
        }

        // Responses API
        if (isset($this->raw['output_text'])) {
            $this->raw['output_text'];
        }
        return '';
    }

    public function getReasoning()
    {
        if (isset($this->raw['choices'][0]['message']['reasoning'])) {
            $reasoning = $this->raw['choices'][0]['message']['reasoning'];

            if (is_string($reasoning)) {
                return $reasoning;
            }
        }
        return null;
    }

    /**
     * Verifica se la risposta nativa dell'LLM contiene richieste di tool.
     */
    public function hasToolRequest(): bool
    {
        // Controlla sia nel formato standard OpenAI/Ollama che in eventuali variazioni radice
        //$toolCalls = $this->raw['message']['tool_calls'] ?? $this->raw['tool_calls'] ?? null;
        //return !empty($toolCalls) && is_array($toolCalls);
        $toolCalls = $this->raw['choices'][0]['message']['tool_calls']
              ?? $this->raw['message']['tool_calls']
              ?? $this->raw['tool_calls']
              ?? null;

        return !empty($toolCalls) && is_array($toolCalls);
    }

    /**
     * Estrae la richiesta del tool dalla struttura nativa tool_calls.
     */
    public function getToolRequest(): ?ToolCall
    {
        $toolCalls = $this->raw['choices'][0]['message']['tool_calls']
              ?? $this->raw['message']['tool_calls']
              ?? $this->raw['tool_calls']
              ?? null;

        if (empty($toolCalls) || !is_array($toolCalls)) {
            return null;
        }

        // Prendiamo la prima chiamata tool richiesta dal modello
        $call = $toolCalls[0] ?? null;
        if (!$call || !isset($call['function'])) {
            return null;
        }

        $id = $call['id'] ?? null;
        $function = $call['function'];
        $toolName = trim($function['name'] ?? '');
        $arguments = $function['arguments'] ?? [];

        // Se gli argomenti sono una stringa JSON, decodifichiamoli subito
        if (is_string($arguments)) {
            $arguments = json_decode($arguments, true) ?? [];
        }

        // FALLBACK DIFENSIVO: Se il modello ha lasciato 'name' vuoto ma ha inserito
        // il nome del tool dentro l'array degli argomenti (es. $arguments['tool'])
        if (empty($toolName) && isset($arguments['tool'])) {
            $toolName = $arguments['tool'];
            // Rimuoviamo la chiave 'tool' dagli argomenti se non serve,
            // oppure teniamo la sotto-chiave 'arguments' se presente
            $arguments = $arguments['arguments'] ?? $arguments;
            // Se 'arguments' era un sotto-array, puliamo l'eventuale chiave 'tool' residua
            if (is_array($arguments) && isset($arguments['tool'])) {
                unset($arguments['tool']);
            }
        }

        if (empty($toolName)) {
            return null;
        }

        // Istanziamo il ToolCall
        $toolCall = new ToolCall($id, $toolName, is_string($arguments) ? json_decode($arguments, true) : $arguments);
        return $toolCall;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }
}
