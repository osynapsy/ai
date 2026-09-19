<?php
namespace Osynapsy\Ai\Prompt;

/**
 * Description of PromptRule
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class PromptRule
{
    public function __construct(
        public string $id,
        public string $text
    ) {}
    
    public function __toString(): string 
    {
        return sprintf('%s - %s', $this->id, $this->text);
    }
}

