<?php
namespace Osynapsy\Ai\Prompt;

use Osynapsy\Ai\Prompt\Formatter\PromptFormatterInterface;

interface PromptInterface
{    
    public function format(?PromptFormatterInterface $formatter = null) : mixed;
}
