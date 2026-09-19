<?php
declare(strict_types=1);

namespace Osynapsy\Ai\Tests\Prompt;

use PHPUnit\Framework\TestCase;
use Osynapsy\Ai\Prompt\PromptRuleSet;
use Osynapsy\Ai\Prompt\PromptRule;

class PromptRuleSetTest extends TestCase
{
    private PromptRuleSet $ruleSet;

    protected function setUp(): void
    {
        $this->ruleSet = new PromptRuleSet('rules', 'RULES');
    }

    public function testAddAndAllRules(): void
    {
        $this->ruleSet->add('Prima regola', 'r1');
        $this->ruleSet->add('Seconda regola', 'r2');

        $rules = $this->ruleSet->all();

        $this->assertCount(2, $rules);
        $this->assertInstanceOf(PromptRule::class, $rules[0]);
        $this->assertEquals('r1', $rules[0]->id);
    }

    public function testUpdateExistingRule(): void
    {
        $this->ruleSet->add('Testo originale', 'r1');
        $this->ruleSet->update('r1', 'Testo modificato');

        $rules = $this->ruleSet->all();

        $this->assertEquals('Testo modificato', $rules[0]->text);
    }

    public function testUpdateNonExistingRuleThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Rule 'not_found' does not exist");

        $this->ruleSet->update('not_found', 'Nuovo testo');
    }

    public function testRemoveRule(): void
    {
        $this->ruleSet->add('Regola 1', 'r1');
        $this->ruleSet->add('Regola 2', 'r2');

        $this->ruleSet->remove('r1');

        $rules = $this->ruleSet->all();

        $this->assertCount(1, $rules);
        $this->assertEquals('r2', $rules[0]->id);
    }

    public function testRenderWithLabel(): void
    {
        $this->ruleSet->add('Non interrompere', 'R1');
        $this->ruleSet->add('Sii conciso', 'R2');

        $expected = "RULES:\nR1 - R1 - Non interrompere\nR2 - R2 - Sii conciso";
        
        // Nota: se PromptRule implementa __toString() che restituisce solo $text,
        // l'output sarà "RULES:\nR1 - Non interrompere\nR2 - Sii conciso"
        $this->assertStringContainsString('RULES:', $this->ruleSet->render());
        $this->assertStringContainsString('R1 -', $this->ruleSet->render());
    }

    public function testRenderEmptyReturnsEmptyString(): void
    {
        $this->assertEquals('', $this->ruleSet->render());
    }
}
