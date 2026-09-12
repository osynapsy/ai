<?php
namespace Osynapsy\AI\Prompt;

/**
 * Description of AbstractPromptIt
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
abstract class AbstractPromptIt extends AbstractPrompt
{
    protected array $labels = [
        'rules' => 'REGOLE',
        'dataset' => 'DATASET',
        'expectedResult' => 'RISULTATO ATTESO'
    ];
}
