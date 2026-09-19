<?php
declare(strict_types=1);

namespace Osynapsy\Ai\Tests\Prompt;

use PHPUnit\Framework\TestCase;
use Osynapsy\Ai\Prompt\AbstractPrompt;
use Osynapsy\Ai\Prompt\PromptSection;
use Osynapsy\Ai\Prompt\Formatter\ChatFormatter;

class AbstractPromptTest extends TestCase
{
    private AbstractPrompt $prompt;

    protected function setUp(): void
    {
        $this->prompt = new class extends AbstractPrompt {
            protected function configure(): void
            {
                // Inizializzazione vuota per la classe anonima
            }
        };
    }

    public function testAddAndGetSection(): void
    {
        $section = new PromptSection('custom', null, 'CUSTOM TITLE');

        $this->assertFalse($this->prompt->sectionExists('custom'));

        $result = $this->prompt->addSection($section);

        $this->assertSame($this->prompt, $result);
        $this->assertTrue($this->prompt->sectionExists('custom'));
        $this->assertSame($section, $this->prompt->section('custom'));
    }

    public function testGetNonExistingSectionThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Section 'non_existing' not found");

        $this->prompt->section('non_existing');
    }

    public function testAddAndGetContext(): void
    {
        $this->assertFalse($this->prompt->sectionExists('context'));

        $this->prompt->addContext('Primo contesto');

        $this->assertTrue($this->prompt->sectionExists('context'));
        $this->assertInstanceOf(PromptSection::class, $this->prompt->getContext());
    }

    public function testSetAndGetDataset(): void
    {
        $dataset = ['name' => 'Pietro', 'role' => 'Developer'];

        $this->prompt->setDataset($dataset);

        $this->assertTrue($this->prompt->sectionExists('dataset'));
        $this->assertEquals($dataset, $this->prompt->getDataset());
    }

    public function testBuildOutputFormatting(): void
    {
        // 1. Impostiamo il dataset
        $dataset = ['id' => 10, 'status' => 'active'];
        $this->prompt->setDataset($dataset);
        // 2. Uso di un'istanza reale di PromptSection anziché un Mock
        $this->prompt->addSection(new PromptSection('instructions', 'Rispondi in italiano.', 'INSTRUCTIONS'));
        $output = $this->prompt->format(new ChatFormatter);       
        $expectedJson = json_encode($dataset, JSON_PRETTY_PRINT);
        // Verifica la formattazione di build()
        $this->assertStringContainsString("### DATASET\n" . $expectedJson, $output[0]['content']);
        $this->assertStringContainsString("### INSTRUCTIONS\nRispondi in italiano.", $output[0]['content']);
    }

    public function testToStringCallsBuild(): void
    {
        $this->prompt->addContext('Test di conversione a stringa');
        $stringOutput = strval($this->prompt);
        $this->assertEquals(json_encode($this->prompt->format(new ChatFormatter), JSON_PRETTY_PRINT), $stringOutput);
    }
}