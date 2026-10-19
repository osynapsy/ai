<?php
namespace Osynapsy\Ai\Response;

interface ResponseInterface
{
    public function hasError(): bool;

    public function getErrorMessage(): string;

    public function getContent(): string;
}
