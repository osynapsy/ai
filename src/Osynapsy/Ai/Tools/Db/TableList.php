<?php
namespace Osynapsy\Ai\Tools\Db;

use Osynapsy\Ai\Tools\ToolInterface;

class TableList implements ToolInterface
{
    public function execute(array $arguments, $databaseService = null): mixed
    {
        if (!method_exists($databaseService, 'getType')) {
            return ['error' => "Metodo getType non disponibile."];
        }
        $query = $this->getTables($databaseService->getType());
        if (empty($query)) {
            return ['error' => "Query di estrazione tabelle non disponibile"]; 
        }
        return $databaseService->findAssoc($query);
    }
    
    protected function getTables($dbType)
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
