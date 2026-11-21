<?php

namespace Osynapsy\Ai\Tools\Ui;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Controller\ControllerInterface;

class Notify implements ToolInterface
{
    public function execute(array $arguments, ?ControllerInterface $controller = null): mixed
    {
        $message = trim($arguments['message'] ?? '');
        $type    = $arguments['type'] ?? 'info';

        if ($message === '') {
            return ['error' => true, 'message' => 'Messaggio di notifica vuoto.'];
        }

        if ($controller !== null) {
            $controller->remoteCall('myAiChat.notify', $message, $type);
        }

        return ['output' => "Notifica ({$type}) mostrata all'utente."];
    }
}