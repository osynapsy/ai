<?php
namespace Osynapsy\Ai\Prompt;

use Osynapsy\Ai\Prompt\PromptSection;

interface PromptInterface
{
    public function addSection(PromptSection $section) : static;

    public function getText() : string;

    public function getImages() : array;
}
