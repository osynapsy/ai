<?php
namespace Osynapsy\AI\OpenAI\Prompt;

class Prompt implements PromptInterface
{
    protected array $messages = [];

    public function add(string $role, string $content): self
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content
        ];

        return $this;
    }

    public function get(): array
    {
        return $this->messages;
    }

    public function getJson(): string
    {
        return json_encode($this->messages, JSON_UNESCAPED_UNICODE);
    }
}
