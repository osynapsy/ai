<?php

namespace Osynapsy\Ai\Tools\Mail;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Ai\Tools\Ui\Confirm;
use Osynapsy\Controller\ControllerInterface;

class Draft implements ToolInterface
{
    public function execute(array $arguments, ?ControllerInterface $controller = null): mixed
    {
        $to      = trim($arguments['to'] ?? '');
        $subject = trim($arguments['subject'] ?? '');
        $body    = trim($arguments['body'] ?? '');
        $cc      = trim($arguments['cc'] ?? '');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return [
                'error'   => true,
                'message' => "Indirizzo destinatario non valido ('{$to}'). Verifica l'anagrafica con db.query o chiedi all'utente."
            ];
        }

        if ($cc !== '' && !filter_var($cc, FILTER_VALIDATE_EMAIL)) {
            return [
                'error'   => true,
                'message' => "Indirizzo CC non valido ('{$cc}')."
            ];
        }

        if ($subject === '' || $body === '') {
            return [
                'error'   => true,
                'message' => "Oggetto e corpo dell'email sono obbligatori."
            ];
        }

        // In Osynapsy\Ai\Tools\Mail\Draft::execute()
        $confirmTool = new Confirm();
        return $confirmTool->execute([
            'title'         => 'Conferma invio email',
            'summary'       => "Stai per inviare un messaggio di posta elettronica.",
            'action'        => 'mail.send',
            'confirm_label' => 'Invia Email',
            'payload'       => [
                'to'      => $to,
                'cc'      => $cc,
                'subject' => $subject,
                'body'    => $body,
            ]
        ], $controller);
    }
}
