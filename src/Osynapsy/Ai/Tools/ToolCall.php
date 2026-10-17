<?php
namespace Osynapsy\Ai\Tools;

/**
 * Description of ToolCall
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
class ToolCall
{
    public function __construct(
        protected string $id,
        protected string $tool,
        protected array $arguments
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTool(): string
    {
        return $this->tool;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
