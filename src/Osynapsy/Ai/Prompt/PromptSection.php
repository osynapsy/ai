<?php
namespace Osynapsy\Ai\Prompt;

class PromptSection
{
    protected string $id;
    protected ?string $label = null;
    protected $content = [];

    public function __construct(string $id, mixed $content = null, ?string $label = null)
    {
        $this->id = $id;
        $this->label = $label;
        if (!empty($content)) {
            $this->add($content);
        }
    }

    public function add(mixed $content)
    {
        $this->content = array_merge($this->content, !is_array($content) ? [$content] : $content);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getContent(): string
    {
        if (is_array($this->content)) {
            return implode("\n", array_map(
                fn($item) => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) $item,
                $this->content
            ));
        }

        return (string) $this->content;
    }

    public function getContentRaw()
    {
        return $this->content;
    }

    /**
     * Genera automaticamente la rappresentazione testuale della sezione
     */
    public function __toString(): string
    {
        $body = $this->getContent();

        // Se non c'è label o è una sezione generica/main, restituiamo solo il contenuto
        if (empty($this->label) || in_array(strtolower($this->label), ['main', 'default'])) {
            return $body;
        }

        // Se la label è specifica (es. DATASET, SCHEMA), aggiungiamo l'header Markdown
        return "### " . strtoupper($this->label) . "\n" . $body;
    }
}
