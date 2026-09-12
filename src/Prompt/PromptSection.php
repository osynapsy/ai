<?php
namespace Osynapsy\AI\Prompt;

/**
 * Description of PromptSection
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class PromptSection
{
    public function __construct(
        public readonly string $id,
        protected ?string $label = null,
        protected array $content = []
    ) {}

    public function add(string $text): static
    {
        $this->content[] = $text;
        return $this;
    }

    public function set(array $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function get(): array
    {
        return $this->content;
    }
    
    public function isEmpty(): bool
    {
        return empty($this->content);
    }

    public function render(): string
    {
        $body = implode("\n", $this->content);

        if ($this->label === null) {
            return $body;
        }

        return $this->label . ":\n" . $body;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

