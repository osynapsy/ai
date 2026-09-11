<?php
namespace Osynapsy\AI\Prompt;

abstract class AbstractPrompt
{
    /** @var array<string,PromptSection> */
    protected array $sections = [];

    public function addContext(string $context)
    {
        if (!$this->sectionExists('context')) {
            $this->addSection(new PromptSection('context', 'CONTEXT'));
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
        $this->sections[$section->id] = $section;
        return $this;
    }

    final public function setDataset(array $dataset): static
    {
        if (!$this->sectionExists('dataset')) {
            $this->addSection(new PromptSection('dataset', 'DATASET'));
        }
        $this->section('dataset')->set($dataset);
        return $this;
    }

    final public function getDataset()
    {
        return $this->section('dataset')->get();
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

    public function build(): string|array
    {
        $parts = [];
        foreach ($this->sections as $id => $section) {
            if ($id === 'dataset') {
                $parts[] = "DATASET:\n" . json_encode($section->get(), JSON_PRETTY_PRINT);
                continue;
            }
            if (!$section->isEmpty()) {
                $parts[] = $section->render();
            }
        }
        return implode("\n\n", $parts);        
    }

    abstract protected function configure(): void;

    public function __construct()
    {
        $this->configure();
    }
    
    public function __toString(): string 
    {
        return $this->build();
    }
}
