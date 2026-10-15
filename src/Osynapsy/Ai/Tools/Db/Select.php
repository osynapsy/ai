<?php
namespace Osynapsy\Ai\Tools\Db;

/**
 * Description of Select
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
class Select extends Query
{
    public function execute(array $arguments, $databaseService = null): mixed
    {
        // Se l'LLM ha passato argomenti strutturati (es. table e columns/fields)
        if (isset($arguments['table'])) {
            $table = $arguments['table'];

            // Estrae le colonne o usa '*' di default
            $columns = $arguments['columns'] ?? $arguments['fields'] ?? ['*'];
            $columnsStr = is_array($columns) ? implode(', ', $columns) : $columns;

            $where = isset($arguments['where']) ? implode(' AND ', $arguments['where']) : '1 = 1';
            
            // Gestisce un eventuale limite se specificato dall'LLM
            $limit = isset($arguments['limit']) ? " LIMIT " . intval($arguments['limit']) : " LIMIT 50"; // Limit di sicurezza opzionale


            // Costruisce la query SQL finale
            $arguments['sql'] = "SELECT {$columnsStr} FROM {$table} WHERE {$where} {$limit}";
        }

        // Passa il controllo a Query, che verificherà la sicurezza della SELECT e interrogherà il DB
        return parent::execute($arguments, $databaseService);
    }
}
