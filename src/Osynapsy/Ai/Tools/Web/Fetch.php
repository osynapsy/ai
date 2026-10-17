<?php
namespace Osynapsy\Ai\Tools\Web;

use Osynapsy\Ai\Tools\ToolInterface;

class Fetch implements ToolInterface
{
    public function execute(array $arguments, $dbs = null): mixed
    {
        $url = trim($arguments['url'] ?? '');

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return json_encode(['error' => true, 'message' => 'URL non valido o vuoto.']);
        }

        // Controllo di sicurezza di base (evita SSRF verso localhost o reti interne)
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'])) {
            return json_encode(['error' => true, 'message' => 'Accesso a localhost non consentito per motivi di sicurezza.']);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: it-IT,it;q=0.9,en;q=0.5',
            ],
        ]);

        $htmlResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || empty($htmlResponse)) {
            return json_encode([
                'error' => true,
                'message' => "Impossibile scaricare la pagina (HTTP Code: {$httpCode}). Dettaglio: {$curlError}"
            ]);
        }

        // Puliamo l'HTML per estrarre solo il testo utile all'LLM
        return $this->extractCleanText($htmlResponse, $url);
    }

    private function extractCleanText(string $html, string $url): string
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        // Carica l'HTML gestendo correttamente l'encoding UTF-8
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        // Rimuoviamo tag rumorosi che sprecano token (script, stili, header, footer, nav)
        $xpath = new \DOMXPath($dom);
        $nodesToRemove = $xpath->query('//script | //style | //nav | //footer | //header | //aside');
        foreach ($nodesToRemove as $node) {
            $node->parentNode->removeChild($node);
        }

        // Estraiamo il titolo della pagina
        $titleNodes = $dom->getElementsByTagName('title');
        $pageTitle = $titleNodes->length > 0 ? trim($titleNodes->item(0)->textContent) : '';

        // Estraiamo tutto il testo corporeo rimanente
        $bodyNode = $dom->getElementsByTagName('body')->item(0);
        $bodyText = $bodyNode ? $bodyNode->textContent : $dom->textContent;

        // Pulizia degli spazi bianchi multipli e delle righe vuote
        $cleanText = preg_replace('/\s+/', ' ', $bodyText);
        $cleanText = trim($cleanText);

        // Limitiamo la lunghezza del testo per non esplodere la finestra di contesto del modello (es. max 10.000 caratteri)
        $maxChars = 10000;
        if (mb_strlen($cleanText) > $maxChars) {
            $cleanText = mb_substr($cleanText, 0, $maxChars) . '... [Testo troncato per limiti di lunghezza]';
        }

        return json_encode([
            'url' => $url,
            'title' => $pageTitle,
            'content' => $cleanText
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
