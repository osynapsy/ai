<?php
namespace Osynapsy\Ai\Tools\Db;

use Osynapsy\Ai\Tools\ToolInterface;

/**
 * Description of TableDescribe
 *
 * @author Pietro Celeste <p.celeste@@osynapsy.net>
 */
class TableList implements ToolInterface
{
    public function execute(array $arguments, $databaseService = null): mixed
    {
        if (!method_exists($databaseService, 'getType')) {
            return ['error' => "Il meotodo getType non disponibile sul db service impossibile procedere."];
        }
        $query = $this->getQuery($databaseService->getType());
        if (empty($query)) {
            return ['error' => "Query di estrazione tabelle non disponibile"]; 
        }
        return $databaseService->find($query);
    }
    
    protected function getQuery($dbType)
    {
        return match($dbType) {
            'mysql' => 'SHOW TABLES',
            'oracle' => 'SELECT table_name FROM user_tables ORDER BY table_name ',
            'postgres' => "SELECT table_name FROM information_schema.tables  WHERE table_schema = 'public' AND table_type = 'BASE TABLE'",
            'sqlite' => "SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",
            default => false
        };
    }
}
