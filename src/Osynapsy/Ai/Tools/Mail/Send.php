<?php

namespace Osynapsy\Ai\Tools\Mail;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Mailer\Mailer;
use Osynapsy\Mailer\Email\TransitionalMessage;
use Osynapsy\Mailer\Email\Attachment;
use Osynapsy\Mailer\Client\SmtpClient;

class Send implements ToolInterface
{
    public function execute(array $arguments, ?Mailer $mailer = null): mixed
    {
        $to          = trim($arguments['to'] ?? '');
        $subject     = trim($arguments['subject'] ?? '');
        $body        = trim($arguments['body'] ?? '');
        $attachments = $arguments['attachments'] ?? [];

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['error' => true, 'message' => "Destinatario non valido: '{$to}'."];
        }

        if ($subject === '' || $body === '') {
            return ['error' => true, 'message' => "Oggetto e corpo del messaggio sono obbligatori."];
        }

        if ($mailer === null) {
            $mailer = new Mailer(new SmtpClient(
                request('env.instance.smtp.host'),
                request('env.instance.smtp.port'),
                request('env.instance.smtp.username'),
                request('env.instance.smtp.password'),
                request('env.instance.smtp.secure')
            ));
            //return ['error' => true, 'message' => "Istanza Osynapsy\\Mailer\\Mailer non registrata in AutoWire."];
        }

        // Mittente configurato da env o sessione dell'utente loggato
        $from = request('env.instance.smtp.from') ?: request('env.instance.smtp.username');

        $message = new TransitionalMessage($from, $to, $subject);
        $message->setHtmlBody(nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')));

        // Eventuali allegati (es. file generati nella sandbox o export)
        foreach ((array) $attachments as $filePath) {
            if (is_string($filePath) && is_file($filePath)) {
                $message->addAttachment(new Attachment($filePath));
            }
        }

        $errors = $mailer->send($message)->getErrors();

        if (!empty($errors)) {
            return [
                'error'   => true,
                'message' => "Errore invio SMTP: " . implode(' | ', $errors)
            ];
        }

        return [
            'output' => "Email inviata correttamente a {$to} (Oggetto: '{$subject}')."
        ];
    }
}
