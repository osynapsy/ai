<?php
namespace Osynapsy\Ai\Prompt\Formatter;

use Osynapsy\Ai\Prompt\PromptInterface;

interface PromptFormatterInterface
{
    /**
     * Trasforma il prompt nel formato richiesto dal target (array, stringa, ecc.)
     */
    public function format(PromptInterface $prompt): mixed;
}