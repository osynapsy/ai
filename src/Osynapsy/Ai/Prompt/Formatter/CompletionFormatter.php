<?php
namespace Osynapsy\Ai\Prompt\Formatter;

use Osynapsy\Ai\Prompt\PromptInterface;

class CompletionFormatter implements PromptFormatterInterface
{
    public function format(PromptInterface $prompt): string
    {
        $buffer = [];

        // Sezioni Dati (Dataset, Schemi)
        foreach ($prompt->getSections() as $section) {
            $buffer[] = strval($section);
        }

        return implode("\n\n", $buffer);
    }
}
