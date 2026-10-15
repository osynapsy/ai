<?php
namespace Osynapsy\Ai\Response;

use Osynapsy\Ai\Tools\ToolCall;

class ChatResponse implements ChatResponseInterface
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

    /**
     * Verifica se il contenuto della risposta richiede l'esecuzione di un tool
     * cercando un blocco JSON con la chiave "tool".
     */
    public function hasToolRequest(): bool
    {
        return $this->getToolRequest() !== null;
    }

    /**
     * Estrae e decodifica la richiesta del tool dal contenuto testuale.
     * Restituisce un array con ['tool' => '...', 'arguments' => [...]] oppure null.
     */
    public function getToolRequest(): ?ToolCall
    {
        $content = $this->getContent();

        if (empty($content)) {
            return null;
        }

        // Cerca un blocco di codice JSON (es. ```json ... ```) oppure un JSON grezzo
        if (preg_match('/```(?:json)?\s*({.*?})\s*```/s', $content, $matches)) {
            $jsonString = $matches[1];
        } else {
            // Tentativo di decodificare l'intero contenuto se è un JSON puro
            $jsonString = trim($content);
        }

        $decoded = json_decode($jsonString, true);

        // Verifica che sia un array valido e che contenga la chiave 'tool'
        if (is_array($decoded) && isset($decoded['tool'])) {
            return new ToolCall($decoded['tool'], $decoded['arguments'] ?? []);
        }

        return null;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }
}