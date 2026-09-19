<?php
namespace Osynapsy\Ai\Prompt\Formatter;

use Osynapsy\Ai\Prompt\PromptInterface;
use Osynapsy\Ai\Prompt\PromptSection;

class ChatFormatter implements PromptFormatterInterface
{
    public function format(PromptInterface $prompt): array
    {
        $messages = [];

        // 1. SYSTEM ROLE: Il contesto generale
        if ($prompt->sectionExists('context')) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->stringifyContent($prompt->getContext())
            ];
        }

        // 2. STORICO: Eventuali turni di chat precedenti (User / Assistant)
        foreach ($prompt->getHistory() as $historicalMsg) {
            $messages[] = $historicalMsg;
        }

        // 3. TURNO CORRENTE: Assemblaggio delle Sezioni (escludendo 'context' se già estratto)
        $currentContentParts = [];
        foreach ($prompt->getSections() as $id => $section) {
            if ($id !== 'context') {
                $currentContentParts[] = strval($section);
            }
        }
        // Se ci sono sezioni o testo, creiamo il messaggio 'user' finale
        if (!empty($currentContentParts)) {
            $userMessage = [
                'role' => 'user',
                'content' => implode("\n\n", $currentContentParts)
            ];

            // Le immagini appartengono a questo turno di richiesta
            if ($images = $prompt->getImages()) {
                $userMessage['images'] = $images;
            }

            $messages[] = $userMessage;
        }
        return $messages;
    }

    /**
     * Helper per convertire in stringa pulita sia oggetti PromptSection, che array, che stringhe semplici.
     */
    protected function stringifyContent(mixed $content): string
    {
        if ($content instanceof PromptSection) {
            $content = $content->getContent();
        }

        if (is_array($content)) {
            return implode("\n", array_map(fn($item) => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string)$item, $content));
        }

        return (string) $content;
    }
}