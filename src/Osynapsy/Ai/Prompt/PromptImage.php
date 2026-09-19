<?php
namespace Osynapsy\Ai\Prompt;

class PromptImage
{
    protected string $base64Data;

    public function __construct(string $filePathOrBase64)
    {
        if (file_exists($filePathOrBase64)) {
            // Converte il file locale direttamente in base64 pulito (senza data:image/png;base64,)
            $this->base64Data = base64_encode(file_get_contents($filePathOrBase64));
        } else {
            // Assumiamo sia già una stringa base64
            $this->base64Data = $filePathOrBase64;
        }
    }

    public function getBase64(): string
    {
        return $this->base64Data;
    }
}
