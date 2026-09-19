<?php

namespace Osynapsy\Ai\Test;

use PHPUnit\Framework\TestCase;
use Osynapsy\Ai\Prompt\AbstractPrompt;
use Osynapsy\Ai\Prompt\PromptSection;
use Osynapsy\Ai\Prompt\Formatter\ChatFormatter;
use Osynapsy\Ai\Prompt\Formatter\CompletionFormatter;

class PromptFormatterTest extends TestCase
{
    private AbstractPrompt $prompt;

    protected function setUp(): void
    {

        // Prepariamo un prompt ricco con tutte le sezioni
        $this->prompt = new class extends AbstractPrompt {
            protected function configure(): void
            {
                // Inizializzazione vuota per la classe anonima
            }
        };
        $this->prompt
            ->addContext('Sei un estrattore dati specializzato in JSON.')
            ->addSection((new PromptSection('istructions', 'Estrai le informazioni dal dataset fornito.', 'ISTRUZIONI')))
            ->setDataset([
                ['id' => 101, 'nome' => 'Pietro Celeste', 'ruolo' => 'Developer']
            ])
            ->addSection((new PromptSection('OUTPUT_SCHEMA', '{"id": "int", "nome": "string"}', 'OUTPUT_SCHEMA')))
            ->addImage('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }

    public function testChatFormatterStructure(): void
    {
        $formatter = new ChatFormatter();
        $payload = $this->prompt->format($formatter);
        // Verifichiamo che l'output sia un array con la struttura di /api/chat
        $this->assertIsArray($payload);
        $this->assertCount(2, $payload); // 1 System message + 1 User message

        // 1. Verifica System Message
        $this->assertEquals('system', $payload[0]['role']);
        $this->assertEquals('Sei un estrattore dati specializzato in JSON.', $payload[0]['content']);

        // 2. Verifica User Message (Istruzione + Sezioni)
        $this->assertEquals('user', $payload[1]['role']);
        $this->assertStringContainsString("### ISTRUZIONI\nEstrai le informazioni dal dataset fornito.", $payload[1]['content']);
        $this->assertStringContainsString('### DATASET', $payload[1]['content']);
        $this->assertStringContainsString('Pietro Celeste', $payload[1]['content']);
        $this->assertStringContainsString('### OUTPUT_SCHEMA', $payload[1]['content']);

        // 3. Verifica Immagine allegata al messaggio user
        $this->assertArrayHasKey('images', $payload[1]);
        $this->assertCount(1, $payload[1]['images']);
    }

    public function testCompletionFormatterStructure(): void
    {
        $formatter = new CompletionFormatter();
        $payload = $this->prompt->format($formatter);
        // Verifichiamo che l'output sia una singola stringa concatenata per /api/generate
        $this->assertIsString($payload);
        $this->assertStringContainsString('### ISTRUZIONI', $payload);
        $this->assertStringContainsString('### DATASET', $payload);
        $this->assertStringContainsString('### OUTPUT_SCHEMA', $payload);
    }
}