<?php
namespace Osynapsy\AI\OpenAI\Prompt;

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
}

