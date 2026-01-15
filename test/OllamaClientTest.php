<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\AI\Ollama\Client as OllamaClient;
use Osynapsy\AI\Ollama\Model\Qwen_2_5 as OllamaModel;
use Osynapsy\AI\Ollama\Prompt\Prompt as OllamaPrompt;

/**
 * Description of OllamaClientTest
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
class OllamaClientTest extends TestCase
{
    protected function promptFactory($prompt)
    {
        $Prompt = new OllamaPrompt();
        $Prompt->add('user', $prompt);
        return $Prompt;
    }
    
    protected function testClient()
    {
        $Model = new OllamaModel('1.5b');
        $Prompt = $this->promptFactory('Restituisci un json con 2 campi, nome e cognome e scrivi dentro Pietro Celeste');
        $Client = new OllamaClient('http://llm01.qanda.cc', 'Clsptr1974', $Model);
        $result = $Client->send($Prompt);
        var_dump($result);
        $this->assertNotEmpty($result);
    }

}
