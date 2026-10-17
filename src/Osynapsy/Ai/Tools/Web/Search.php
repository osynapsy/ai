<?php
namespace Osynapsy\Ai\Tools\Web;

use Osynapsy\Ai\Tools\ToolInterface;

class Search implements ToolInterface
{
    private const MAX_RESULTS = 5;

    public function execute(array $arguments, $dbs = null): mixed
    {
        $searchTerm = trim($arguments['query'] ?? '');

        if ($searchTerm === '') {
            return json_encode(['error' => true, 'message' => 'Query di ricerca vuota.']);
        }

        $url = 'https://html.duckduckgo.com/html/?q=' . urlencode($searchTerm);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT      => 'w3m/0.5.3',
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html, text/*;q=0.5, image/*',
                'Accept-Language: it-IT,it;q=0.9,en;q=0.5',
            ],
        ]);

        $htmlResponse = curl_exec($ch);
        $httpCode     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 202) {
            return json_encode([
                'error'   => true,
                'message' => 'DuckDuckGo ha limitato la richiesta (HTTP 202, bot detection).'
            ]);
        }

        if ($httpCode !== 200 || empty($htmlResponse)) {
            return json_encode([
                'error'   => true,
                'message' => "Errore di connessione a DuckDuckGo (HTTP Code: {$httpCode}). Dettaglio: {$curlError}"
            ]);
        }

        return $this->parseDuckDuckGoHtml($htmlResponse);
    }

    private function parseDuckDuckGoHtml(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath   = new \DOMXPath($dom);
        $results = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' result ')]");
        $extractedData = [];

        foreach ($results as $result) {
            if (count($extractedData) >= self::MAX_RESULTS) {
                break;
            }

            $titleNode   = $xpath->query(".//a[contains(@class, 'result__a')]", $result)->item(0);
            $snippetNode = $xpath->query(".//*[contains(@class, 'result__snippet')]", $result)->item(0);

            if (!$titleNode) {
                continue;
            }

            // L'href reale è un redirect DuckDuckGo: l'URL vero è nel parametro uddg
            $href = $titleNode->getAttribute('href');
            if (strpos($href, 'uddg=') !== false) {
                $query = parse_url($href, PHP_URL_QUERY) ?? '';
                parse_str($query, $params);
                $href = $params['uddg'] ?? $href;
            }

            $extractedData[] = [
                'title'   => trim($titleNode->textContent),
                'snippet' => $snippetNode ? trim($snippetNode->textContent) : '',
                'url'     => $href,
            ];
        }

        if (empty($extractedData)) {
            return json_encode(['error' => false, 'message' => 'Ricerca completata ma nessun risultato estratto dall\'HTML.']);
        }

        return json_encode($extractedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}