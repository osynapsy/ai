<?php
namespace Osynapsy\Ai\Tools\Ui;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Controller\ControllerInterface;

class Confirm implements ToolInterface
{
    public function execute(array $arguments, ?ControllerInterface $controller = null): mixed
    {
        $action = trim($arguments['action'] ?? 'ui.confirm_action');

        $cardData = [
            'type'          => 'confirm_card',
            'id'            => uniqid('ui_act_'),
            'title'         => trim($arguments['title'] ?? 'Conferma operazione'),
            'summary'       => trim($arguments['summary'] ?? ''),
            'action'        => $action,
            'payload'       => $arguments['payload'] ?? [],
            'confirm_label' => trim($arguments['confirm_label'] ?? 'Conferma'),
        ];

        // Chiama il metodo reale già presente nella tua ChatUI
        if ($controller !== null) {
            $controller->remoteCall('myAiChat.renderConfirm', $cardData);
        }

        return [
            'output' => "Conferma mostrata. Rispondi all'utente solo con: 'Verifica i dati nel riquadro e clicca su Conferma per procedere.'"
        ];
    }
}
