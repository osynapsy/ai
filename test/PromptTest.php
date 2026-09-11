<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\AI\Prompt\AbstractPrompt;
use Osynapsy\AI\Prompt\PromptSection;

class PromptTest extends TestCase
{
    private function promptFactory(): AbstractPrompt
    {
        return new class extends AbstractPrompt {
            protected function configure() : void
            {
                // configurazione vuota per il test
            }
        };
    }

    /**
     * La classe può essere istanziata correttamente.
     */
    public function testConstructor(): void
    {
        $prompt = $this->promptFactory();

        $this->assertInstanceOf(AbstractPrompt::class, $prompt);
    }

    /**
     * addContext crea correttamente la sezione "context".
     */
    public function testAddContext(): void
    {
        $prompt  = $this->promptFactory();
        $context = 'Questo è un contesto di esempio';

        $prompt->addContext($context);

        $section = $prompt->getContext();

        $this->assertInstanceOf(PromptSection::class, $section);
        $this->assertSame("CONTEXT:\n$context", strval($section));
    }

    /**
     * setDataset imposta correttamente il dataset.
     */
    public function testSetDataset(): void
    {
        $prompt  = $this->promptFactory();
        $dataset = ['key' => 'value', 'another_key' => 123];

        $prompt->setDataset($dataset);        

        //$this->assertInstanceOf(PromptSection::class, $section);
        $this->assertSame($dataset, $prompt->getDataset());
    }

    /**
     * build restituisce le parti del prompt nel formato atteso.
     */
    public function testBuild(): void
    {
        $prompt = $this->promptFactory();
        $prompt->addContext('Hello World!');
        $prompt->addSection((new PromptSection('other', 'OTHER'))->add('other'));
        $prompt->setDataset(['key' => 'value']);
        $strPrompt = strval($prompt);
        $this->assertIsString($strPrompt);
        $this->assertStringContainsString('"key": "value"', $strPrompt);
        $this->assertStringContainsString('CONTEXT', $strPrompt);
        $this->assertStringContainsString('OTHER', $strPrompt);
    }

    /**
     * Richiedere una sezione inesistente lancia un'eccezione.
     */
    public function testSectionDoesNotExist(): void
    {
        $prompt = $this->promptFactory();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/section.*not found/i');

        $prompt->section('non_existent_section');
    }
}
