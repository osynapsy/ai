<?php
namespace Osynapsy\Ai\Prompt;


class File implements PromptInterface
{
    protected $file;

    public function __construct($filename)
    {
        $this->file = new \CURLFile($filename);
    }

    public function format(?Formatter\PromptFormatterInterface $formatter = null) : mixed
    {
        return ['file' => $this->file];
    }
}
