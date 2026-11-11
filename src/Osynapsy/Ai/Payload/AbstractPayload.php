<?php
namespace Osynapsy\Ai\Payload;

use Osynapsy\Ai\Tools\ToolExecutor;
use Osynapsy\Ai\Message\MessageCollection;

/**
 * Description of AbstractPayload
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
abstract class AbstractPayload implements \JsonSerializable, PayloadInterface
{
    protected MessageCollection $history;
    protected string $model;
    protected array $tools = [];
    protected array $options = [];
    protected array $parameters = [];
    protected bool $toolsEnabled = true;
    protected int $maxToolIterations = 1;
    protected $toolExecutor;

    public function __construct(string $model, MessageCollection $history)
    {
        $this->setParameter('model', $model);
        $this->history = $history;
    }

    public function hasTool()
    {
        return !empty($this->toolExecutor);
    }
    /**
     * Abilita o disabilita l'invio dei tool nel payload.
     */
    public function setToolExecutor(ToolExecutor $toolExecutor): static
    {
        // Riutilizza il tuo metodo addContext esistente per metterlo nel ruolo 'system'
        $this->setParameter('tools', $this->toolsAdapter($toolExecutor->getDefinitions()));
        $this->history->addSystem($toolExecutor->getToolsContext());
        $this->toolExecutor = $toolExecutor;
        return $this;
    }

    public function getHistory() : MessageCollection
    {
        return $this->history;
    }

    public function getToolExecutor() : ToolExecutor
    {
        return $this->toolExecutor;
    }

    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    public function getOptions(string $key)
    {
        return $this->options[$key] ?? null;
    }

    public function setParameter(string $parameter, mixed $value): static
    {
        $this->parameters[$parameter] = $value;
        return $this;
    }

    public function setOption(string $option, mixed $value): static
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Calcola dinamicamente un num_ctx sicuro basato sulla dimensione del payload.
     */
    public function calculateDynamicContext($dataForCounting, $div): int
    {
        $jsonString = json_encode($dataForCounting);
        $charCount = mb_strlen($jsonString);

        // 2. Stimiamo i token (fattore cautelativo: ~3.5 caratteri per token)
        // Aggiungiamo un buffer di sicurezza di 2000 token per la risposta dell'LLM
        $estimatedTokens = (int) ceil($charCount / $div) + 2000;
        // 3. Mappiamo il risultato nei "tagli" standard di context window supportati da Ollama
        $thresholds = [4096, 8192, 16384, 32768, 65536];

        foreach ($thresholds as $limit) {
            if ($estimatedTokens <= $limit) {
                return $limit;
            }
        }

        // Se è un payload mastodontico, restituiamo il taglio massimo
        return 131072;
    }

    public function jsonSerialize(): mixed
    {
        $data = $this->parameters;
        if (!empty($this->options)) {
            $data['options'] = $this->optionsAdapter($this->options);
        }
        $data['messages'] = $this->messageHistorySerialize($this->history->all());
        return $data;
    }

    protected function messageHistorySerialize($messages)
    {
        return array_map(
            function($msg) {
                if (array_key_exists('__id__', $msg)) {
                    unset($msg['__id__']);
                }
                return $msg;
            },
            $messages
        );
    }

    protected function toolsAdapter(array $tools)
    {
        return $tools;
    }

    protected function optionsAdapter(array $options)
    {
        return $options;
    }
}
