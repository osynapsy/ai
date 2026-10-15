<?php
namespace Osynapsy\Ai\Tools\Db;

use Osynapsy\Ai\Tools\ToolInterface;

class Query implements ToolInterface
{
    public function execute(array $arguments, $databaseService): mixed
    {
        $sql = trim($arguments['sql'] ?? '');

        if (empty($sql)) {
            return ['error' => "Parametro 'sql' mancante per il tool db.query."];
        }

        // Regola di sicurezza tassativa: Solo SELECT
        if (!preg_match('/^SELECT\b/i', $sql)) {
            return [
                'error' => "Violazione di sicurezza: è permessa esclusivamente l'istruzione SELECT. Query rifiutata."
            ];
        }

        if (method_exists($databaseService, 'executeSafeSelect')) {
            return $databaseService->findAssoc($sql , $arguments);
        }

        return ['error' => "Metodo executeSafeSelect non disponibile nel servizio database."];
    }
}
