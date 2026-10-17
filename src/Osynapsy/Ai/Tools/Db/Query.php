<?php
namespace Osynapsy\Ai\Tools\Db;

use Osynapsy\Ai\Tools\ToolInterface;

class Query implements ToolInterface
{
    public function execute(array $arguments, $databaseService = null): mixed
    {
        $narguments = array_values($arguments);
        $sql = trim($narguments[0] ?? '');

        if (empty($sql)) {
            return ['error' => "Parametro 'sql' mancante per il tool db.query."];
        }

        // Regola di sicurezza tassativa: Solo SELECT
        if (!preg_match('/^SELECT\b/i', $sql)) {
            return [
                'error' => "Violazione di sicurezza: è permessa esclusivamente l'istruzione SELECT. Query rifiutata."
            ];
        }

        if (!method_exists($databaseService, 'findAssoc')) {
            return ['error' => "Metodo findAssoc non disponibile nel servizio database."];
        }

        try {
            // ... logica di esecuzione del tool ...
            return $databaseService->findAssoc($sql);
        } catch (\Throwable $e) {
            // RESTITUIAMO L'ERRORE ALL'LLM INVECE DI INTERROMPERE L'APPLICAZIONE
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
