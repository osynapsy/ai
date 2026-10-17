<?php
namespace Osynapsy\Ai\Tools\Db;

use Osynapsy\Ai\Tools\ToolInterface;

/**
 * Description of DescribeAll
 *
 * @author Pietro Celeste <p.celeste@@osynapsy.net>
 */
class DescribeAll implements ToolInterface
{
    public function execute(array $arguments, $databaseService = null): mixed
    {
        if (!method_exists($databaseService, 'getType')) {
            return ['error' => "Il meotodo getType non disponibile sul db service impossibile procedere."];
        }
        $dbType = $databaseService->getType();
        $query = $this->getAllTablesQuery($dbType);
        if (empty($query)) {
            return ['error' => "Query di estrazione tabelle non disponibile"];
        }
        $tables =  $databaseService->find($query);
        $query2 = $this->getDescribeTableQuery($dbType);
        if (empty($query2)) {
            return ['error' => "Query di estrazione descrizione tabella non disponibile"];
        }
        $fullSchema = [];
        foreach($tables as $row) {
            $table = $row['0'];
            $fullSchema[$table] = $databaseService->findAssoc(sprintf($query2, $table));
        }
        return $fullSchema;
    }

    protected function getAllTablesQuery($dbType)
    {
        return match($dbType) {
            'mysql' => 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'',
            'oracle' => 'SELECT table_name FROM user_tables ORDER BY table_name ',
            'postgres' => "SELECT table_name FROM information_schema.tables  WHERE table_schema = 'public' AND table_type = 'BASE TABLE'",
            'sqlite' => "SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",
            default => false
        };
    }

    protected function getDescribeTableQuery($dbType)
    {
        return match($dbType) {
            'mysql' => 'DESC %s',
            'oracle' => "SELECT column_name, data_type, data_length, nullable FROM user_tab_columns WHERE table_name = '%s'",
            'postgres' => "SELECT table_name FROM information_schema.tables  WHERE table_schema = 'public' AND table_type = 'BASE TABLE'",
            'sqlite' => "SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",
            default => false
        };
    }
}
