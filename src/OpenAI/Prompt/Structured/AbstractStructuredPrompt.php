<?php
namespace Osynapsy\AI\OpenAI\Prompt\Structured;

/**
 * Description of AbstractStructuredPrompt
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
abstract class AbstractStructuredPrompt
{
    protected PromptRuleSet $rules;

    public function __construct()
    {
        $this->rules = new PromptRuleSet();
        $this->configureRules();
    }

    abstract protected function configureRules(): void;

    protected function renderRules(): string
    {
        return "RULES:\n" . implode("\n", array_map(
            fn(PromptRule $r) => "- {$r->text}",
            $this->rules->all()
        ));
    }
}


