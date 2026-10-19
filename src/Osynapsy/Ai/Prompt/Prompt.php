<?php
namespace Osynapsy\Ai\Prompt;

/**
 * Description of Prompt
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Prompt implements PromptInterface
{
    /** @var array<string,PromptSection> */
    protected array $sections = [];
    protected array $images = [];

    public function addSection(PromptSection $section): static
    {
        $this->sections[$section->getId()] = $section;
        return $this;
    }

    public function hasSections(): bool
    {
        return !empty($this->section);
    }

    public function sectionExists(string $id)
    {
        return isset($this->sections[$id]);
    }

    public function section(string $id): PromptSection
    {
        if (!$this->sectionExists($id)) {
            throw new \Exception("Section '{$id}' not found");
        }
        return $this->sections[$id];
    }

    public function addImage(string $filePathOrBase64): static
    {
        $this->images[] = new PromptImage($filePathOrBase64);
        return $this;
    }

    public function hasImages(): bool
    {
        return !empty($this->images);
    }

    public function getImages(): array
    {
        $imagesBase64 = [];
        foreach ($this->images as $image) {
            $imagesBase64[] = $image->getBase64();
        }
        return $imagesBase64;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function getText() : string
    {
        return implode("\n", $this->sections);
    }

    public function __toString(): string
    {
        return $this->getText();
    }

    public function __call(string $name, array $arguments): static
    {
       if (substr($name, 0, 3) === 'add' && strlen($name) >= 3) {
            if (empty($arguments)) {
                throw new \InvalidArgumentException("Il metodo $name() richiede almeno il contenuto della sezione.");
            }
           $section = substr($name, 3) ?: 'default';
           $this->addSection(new PromptSection(strtolower($section), $arguments[0], $arguments[1] ?? null));
           return $this;
       }
       throw new \BadMethodCallException("No methos {$name}() exists in " . static::class);
    }
}
