<?php
namespace Osynapsy\AI\Prompt;

/**
 * Description of AbstractPromptIt
 *
 * @author peter
 */
abstract class AbstractPromptIt extends AbstractPrompt
{
    protected array $labels = [
        'rules' => 'REGOLE',
        'dataset' => 'DATASET',
        'expectedResult' => 'RISULTATO ATTESO'
    ];
}
