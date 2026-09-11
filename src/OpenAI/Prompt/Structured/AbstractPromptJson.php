<?php
namespace Osynapsy\AI\OpenAI\Prompt\Structured;

/**
 * Description of AbstractPromptJson
 *
 * @author peter
 */
abstract class AbstractPromptJson extends AbstractStructuredPrompt
{
    protected function rules(): array
    {
        return array_merge(
            $this->customRules(),
            [
                'Rispondi esclusivamente con JSON valido',
                'Non includere testo fuori dal JSON',
                'Se non puoi rispondere, restituisci un JSON di errore'
            ]
        );
    }

    abstract protected function customRules(): array;
    abstract protected function outputSchema(): array;
}

