<?php
namespace Osynapsy\Ai\Response;

interface ChatResponseInterface
{
    public function hasError(): bool;

    public function getErrorMessage(): string;

    public function getContent(): string;
}
