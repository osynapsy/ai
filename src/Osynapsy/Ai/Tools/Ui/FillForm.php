<?php

namespace Osynapsy\Ai\Tools\Ui;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Controller\ControllerInterface;

class FillForm implements ToolInterface
{
    public function execute(array $arguments, ?ControllerInterface $controller = null): mixed
    {
        $fields = $arguments['fields'] ?? [];

        if (!is_array($fields) || empty($fields)) {
            return ['error' => true, 'message' => 'Nessun campo specificato in fields.'];
        }

        if ($controller !== null) {
            $controller->remoteCall('window.myAiChat.fillForm', $fields);
        }

        $count = count($fields);
        return ['output' => "Compilati {$count} campi nella maschera corrente."];
    }
}
