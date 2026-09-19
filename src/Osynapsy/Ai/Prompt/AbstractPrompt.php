<?php
namespace Osynapsy\Ai\Prompt;

use Osynapsy\Ai\Prompt\Formatter\PromptFormatterInterface;
use Osynapsy\Ai\Prompt\Formatter\ChatFormatter;
use Osynapsy\Ai\Prompt\Section\Dataset;

/**
 * Description of AbstractPrompt
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
abstract class AbstractPrompt implements PromptInterface
{
    /** @var array<string,PromptSection> */
    protected array $sections = [];
    protected array $messages = [];
    protected array $images = [];

    public function addContext(string $context)
    {
        if (!$this->sectionExists('context')) {
            $this->addSection(new PromptSection('context', null, 'SYSTEM'));
        }
        $this->getContext()->add($context);
        return $this;
    }

    public function getContext()
    {
        return $this->section('context');
    }

    public function addSection(PromptSection $section): static
    {
        $this->sections[$section->getId()] = $section;
        return $this;
    }

    final public function setDataset(array $dataset): static
    {
        if (!$this->sectionExists('dataset')) {
            $this->addSection(new Dataset('dataset', null, 'DATASET'));
        }
        $this->section('dataset')->add($dataset);
        return $this;
    }

    final public function getDataset()
    {
        return $this->section('dataset')->getDataset();
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

    /**
     * Il Prompt si adatta al formato richiesto dal Formatter
     */
    public function format(PromptFormatterInterface $formatter): mixed
    {
        return $formatter->format($this);
    }

    public function addImage(string $filePathOrBase64): static
    {
        $this->images[] = new PromptImage($filePathOrBase64);
        return $this;
    }

    public function getHistory() : array
    {
        return $this->messages;
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

    public function hasImages(): bool
    {
        return !empty($this->images);
    }

    abstract protected function configure(): void;

    public function __construct()
    {
        $this->configure();
    }

    public function __toString(): string
    {
        return json_encode($this->format(new Formatter\ChatFormatter), JSON_PRETTY_PRINT);
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
