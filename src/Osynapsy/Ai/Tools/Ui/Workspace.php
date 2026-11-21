<?php

namespace Osynapsy\Ai\Tools\Ui;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Controller\ControllerInterface;

class Workspace implements ToolInterface
{
    public function execute(array $arguments, ?ControllerInterface $controller = null): mixed
    {
        $mode  = $arguments['mode'] ?? 'open';
        $title = trim($arguments['title'] ?? 'Dettaglio Operativo');
        $html  = trim($arguments['html'] ?? '');

        if ($controller !== null) {
            if ($mode === 'close') {
                $controller->remoteCall('myAiChat.closeWorkspace');
            } else {
                $controller->remoteCall('myAiChat.openWorkspace', $title, $html);
            }
        }

        return [
            'output' => $mode === 'close'
                ? "Pannello laterale chiuso."
                : "Pannello laterale '{$title}' aperto e aggiornato a schermo."
        ];
    }
}