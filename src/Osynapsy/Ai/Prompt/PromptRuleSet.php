<?php
namespace Osynapsy\Ai\Prompt;

/**
 * Description of PromptRuleSet
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class PromptRuleSet extends PromptSection
{
    public function add(string $text, ?string $id = null): static
    {
        $ruleId = $id ?? 'rule_' . (count($this->content) + 1);        
        $this->content[$ruleId] = new PromptRule($ruleId, $text);        
        return $this;
    }

    public function update(string $id, string $text): static
    {
        if (!isset($this->content[$id])) {
            throw new \RuntimeException("Rule '{$id}' does not exist");
        }

        $this->content[$id]->text = $text;
        return $this;
    }

    public function remove(string $id): static
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
            return '';
        }

        $rules = [];
        foreach ($this->content as $id => $rule) {
            $rules[] = strval($rule);
        }

        if ($this->label === null) {
            return implode("\n", $rules);
        }

        return $this->label . ":\n" . implode("\n", $rules);
    }
}
