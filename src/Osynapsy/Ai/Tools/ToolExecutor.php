<?php
namespace Osynapsy\Ai\Tools;

use Osynapsy\Ai\Tools\ToolCall;

/**
 * Description of ToolExecutor
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class ToolExecutor
{
    protected string $baseNamespace = 'Osynapsy\\Ai\\Tools\\';

    public function __construct(protected $databaseService) {}

    public function execute(ToolCall $toolCall): mixed
    {
        $toolName = $toolCall->getTool(); // es. "db.table_list"
        $arguments = $toolCall->getArguments();

        $className = $this->resolveClassName($toolName);

        if (!class_exists($className) || !is_subclass_of($className, ToolInterface::class)) {
            return ['error' => "Tool sconosciuto o non valido: {$toolName}"];
        }

        /** @var ToolInterface $tool */
        $tool = new $className();

        return $tool->execute($arguments, $this->databaseService);
    }

    /**
     * Converte "db.table_list" in "Osynapsy\Ai\Tools\Db\TableList"
     */
    protected function resolveClassName(string $toolName): string
    {
        $parts = explode('.', $toolName);

        if (count($parts) !== 2) {
            return '';
        }

        $category = ucfirst(strtolower($parts[0])); // db -> Db

        // table_list -> TableList
        $action = str_replace(' ', '', ucwords(str_replace('_', ' ', $parts[1])));

        return "{$this->baseNamespace}{$category}\\{$action}";
    }
}