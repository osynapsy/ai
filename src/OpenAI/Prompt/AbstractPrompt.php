<?php
namespace Osynapsy\AI\OpenAI\Prompt;

/**
 * Description of AbstractPrompt
 *
 * @author peter
 */
abstract class AbstractPrompt
{
    protected array $context = [];
    protected array $rules = [];
    protected ?array $dataset = null;
    protected array $expectedResult = [];

    final protected function add(string $content): void
    {
        $this->messages[] = [
            'role' => 'system',
            'content' => $content
        ];
    }

    // ---- CONTEXT -------------------------------------------------

    public function addContext(string $text): static
    {
        $this->context[] = $text;
        return $this;
    }

    public function appendContext(string $text): static
    {
        return $this->addContext($text);
    }

    // ---- RULES ---------------------------------------------------

    public function addRule(string $id, string $text): static
    {
        $this->rules[$id] = $text;
        return $this;
    }

    public function updateRule(string $id, string $text): static
    {
        if (!isset($this->rules[$id])) {
            throw new \LogicException("Rule {$id} does not exist");
        }
        $this->rules[$id] = $text;
        return $this;
    }

    public function removeRule(string $id): static
    {
        unset($this->rules[$id]);
        return $this;
    }

    // ---- DATASET -------------------------------------------------

    public function setDataset(array $dataset): static
    {
        $this->dataset = $dataset;
        return $this;
    }

    // ---- RESULT --------------------------------------------------

    public function setExpectedResult(string $text): static
    {
        $this->expectedResult[] = $text;
        return $this;
    }

    // ---- BUILD ---------------------------------------------------

    final public function build(): array
    {
        $content = [];

        if ($this->context) {
            $content[] = "CONTESTO:\n" . implode("\n", $this->context);
        }

        if ($this->rules) {
            $rules = [];
            foreach ($this->rules as $id => $rule) {
                $rules[] = "{$id} - {$rule}";
            }
            $content[] = "REGOLE:\n" . implode("\n", $rules);
        }

        if ($this->dataset) {
            $content[] = "DATASET:\n" . json_encode($this->dataset, JSON_PRETTY_PRINT);
        }

        if ($this->expectedResult) {
            $content[] = "RISULTATO ATTESO:\n" . implode("\n", $this->expectedResult);
        }

        return [[
            'role' => 'system',
            'content' => implode("\n\n", $content)
        ]];
    }
}
