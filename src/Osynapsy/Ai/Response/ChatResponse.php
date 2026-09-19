<?php
namespace Osynapsy\Ai\Response;

class ChatResponse implements ChatResponseInterface
{
    protected array $raw;
    protected ?array $error;

    public function __construct(array $response)
    {
        $this->raw = $response;
        $this->error = $response['error'] ?? null;
    }

    /**
     * Verifica se la response contiene un errore OpenAI
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Messaggio di errore (se presente)
     */
    public function getErrorMessage(): string
    {
        return $this->error['message'] ?? '';
    }

    /**
     * Codice errore OpenAI (se presente)
     */
    public function getErrorCode(): string
    {
        return $this->error['code'] ?? '';
    }

    /**
     * Contenuto testuale principale della risposta
     */
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

        // Responses API
        if (isset($this->raw['output_text'])) {
            return $this->raw['output_text'];
        }

        return '';
    }

    /**
     * Ritorna la risposta grezza (utile per debug / log)
     */
    public function getRaw(): array
    {
        return $this->raw;
    }
}
