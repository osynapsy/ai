<?php
namespace Osynapsy\Ai\Payload\OpenAi;

/**
 * Description of ResponseStreamDecoder
 *
 * @author Pietro Celeste <p.cleste@qanda.cc>
 */

class ResponseStreamDecoder
{
    /** Decodifica una riga di stream (NDJSON o SSE); null se la riga non porta dati. */
    public function decodeLine(string $rawLine): ?array
    {
        $line = trim($rawLine);
        if ($line === '' || $line[0] === ':') {
            return null;                        // riga vuota o commento/keep-alive SSE
        }
        if (str_starts_with($line, 'data:')) {
            $line = ltrim(substr($line, 5));
            if ($line === '[DONE]') {
                return null;
            }
        } elseif ($line[0] !== '{') {
            return null;                        // event:, id:, retry: o testo non JSON
        }
        $row = json_decode($line, true);
        return is_array($row) ? $row : null;
    }

    /** Decodifica un body di stream completo in un array di righe. */
    public function decodeBody(string $body): array
    {
        $rows = [];
        foreach (preg_split('/\R/', $body) as $line) {
            $row = $this->decodeLine($line);
            if ($row !== null) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    
    /**
    * Fonde le righe NDJSON di uno stream Ollama in un unico JSON,
    * identico nella forma a una risposta con stream=false.
    */
   /**
    * Fonde le righe di uno stream (Ollama nativo o OpenAI-compatibile)
    * in un'unica risposta, nella forma di una risposta non-stream.
    */
    public function merge(array $rows): array
    {
        $content = '';
        $reasoning = '';
        $toolCalls = [];
        $finish = null;
        $meta = [];

        foreach ($rows as $row) {
            if (isset($row['error'])) {
                $error = $row['error'];
                return ['error' => is_array($error) ? $error : ['message' => (string) $error]];
            }
            // metadati (model, id, usage, statistiche...): le righe successive hanno la precedenza
            $meta = array_diff_key($row, array_flip(['choices', 'message'])) + $meta;
            $finish = $row['choices'][0]['finish_reason'] ?? $row['done_reason'] ?? $finish;

            $part = $row['choices'][0]['delta'] ?? $row['message'] ?? [];
            $content   .= $part['content'] ?? '';
            $reasoning .= $part['reasoning'] ?? $part['thinking'] ?? '';

            foreach ($part['tool_calls'] ?? [] as $tc) {
                // OpenAI manda frammenti con "index"; Ollama nativo chiamate complete senza index
                $i = $tc['index'] ?? count($toolCalls);
                $toolCalls[$i] ??= ['type' => 'function', 'function' => ['name' => '', 'arguments' => '']];
                if (!empty($tc['id'])) {
                    $toolCalls[$i]['id'] = $tc['id'];
                }
                if (!empty($tc['function']['name'])) {
                    $toolCalls[$i]['function']['name'] = $tc['function']['name'];
                }
                $args = $tc['function']['arguments'] ?? '';
                if (is_array($args)) {
                    $toolCalls[$i]['function']['arguments'] = $args;
                } else {
                    $toolCalls[$i]['function']['arguments'] .= $args;
                }
            }
        }

        $message = ['role' => 'assistant', 'content' => $content];
        if ($reasoning !== '') {
            $message['reasoning'] = $reasoning;
        }
        if ($toolCalls) {
            ksort($toolCalls);
            $message['tool_calls'] = array_values($toolCalls);
        }

        return array_merge($meta, [
            'object'  => 'chat.completion',
            'choices' => [['index' => 0, 'message' => $message, 'finish_reason' => $finish]],
        ]);
    }
}
