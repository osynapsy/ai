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
            $controller->remoteCall('myAiChat.renderUiCommand', $cardData);
        }

        return [
          'output' => "FATTO: Il pulsante di conferma è già visibile sullo schermo dell'utente. Non richiamare più ui.confirm. Scrivi ora all'utente una breve frase in italiano invitandolo a cliccare su Conferma."
        ];
    }
}
