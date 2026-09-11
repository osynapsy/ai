<?php
namespace Osynapsy\AI\Prompt;

/**
 * Description of PromptRuleSet
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class PromptRuleSet extends PromptSection
{
    /** @var array<string, PromptRule> */

    public function add(string $id, string $text): self
    {
        $this->content[$id] = new PromptRule($id, $text);
        return $this;
    }

    public function update(string $id, string $text): self
    {
        if (!isset($this->rules[$id])) {
            throw new \RuntimeException("Rule '{$id}' does not exist");
        }

        $this->content[$id]->text = $text;
        return $this;
    }

    public function remove(string $id): self
    {
        unset($this->content[$id]);
        return $this;
    }

    public function all(): array
    {
        return array_values($this->content);
    }

    public function render(): string
    {
        if (empty($this->content)) {
            return null;
        }
        $rules = [];
        foreach ($this->rules as $id => $rule) {
            $rules[] = "{$id} - {$rule}";
        }
        return $this->label . ":\n" .  implode("\n", $rules);
    }
}

