<?php
namespace Osynapsy\Ai\Tools;

class ToolExecutor
{
    protected array $definitions = [];
    protected array $handlers = [];
    protected $dbo;

    public function __construct($dbo, $dir = __DIR__)
    {
        $this->dbo = $dbo;
        $this->discover($dir);
    }

    /**
     * Scansiona automaticamente una directory base (es. Osynapsy/Ai/Tools)
     * alla ricerca di cataloghi JSON in ogni sottocartella.
     */
    public function discover(string $toolsBaseDir): self
    {
        if (!is_dir($toolsBaseDir)) {
            return $this;
        }

        // Cerca tutte le sottocartelle (es. Db, File, ecc.)
        $subDirs = glob($toolsBaseDir . '/*', GLOB_ONLYDIR);

        foreach ($subDirs as $dir) {
            // Cerca file JSON all'interno della sottocartella (es. db.json, file.json)
            $jsonFiles = glob($dir . '/*.json');
            foreach ($jsonFiles as $jsonFile) {
                $this->loadCatalog($jsonFile);
            }
        }

        return $this;
    }

    /**
     * Carica un singolo file JSON di catalogo e registra tool e handler.
     */
    public function loadCatalog(string $jsonFilePath): void
    {
        $content = file_get_contents($jsonFilePath);
        $catalog = json_decode($content, true);

        if (!$catalog || !isset($catalog['tools']) || !is_array($catalog['tools'])) {
            return;
        }

        foreach ($catalog['tools'] as $tool) {
            $name = $tool['name'] ?? null;
            $className = $tool['class'] ?? null;

            if (!$name || !$className) {
                continue;
            }

            // 1. Registra la definizione pulita pronta per essere passata nel Payload dell'LLM
            $this->definitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => $name,
                    'description' => $tool['description'] ?? '',
                    'parameters' => $tool['parameters'] ?? []
                ]
            ];

            // 2. Mappa il nome del tool alla classe PHP che lo esegue
            $this->handlers[$name] = $className;
        }
    }

    /**
     * Restituisce l'array dei tool formattato per il Payload del Client.
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getToolsContext()
    {
        return ToolContext::getToolsContext();
    }

    public function getMaxToolIterations()
    {
        return 10;
    }

    /**
     * Esegue la classe associata al ToolCall ricevuto dall'LLM.
     */
    public function execute(ToolCall $toolCall): mixed
    {
        $name = str_replace('_', '.', $toolCall->getTool());

        if (!isset($this->handlers[$name])) {
            throw new \Exception("Tool non registrato: {$name}");
        }

        $className = $this->handlers[$name];

        if (!class_exists($className)) {
            throw new \Exception("Classe handler non trovata per il tool {$name}: {$className}");
        }

        $handler = new $className();

        if (!method_exists($handler, 'execute')) {
            throw new \Exception("La classe handler {$className} deve implementare un metodo handle().");
        }

        // Esegue il tool passando gli argomenti decodificati dall'LLM
        return $handler->execute($toolCall->getArguments(), $this->dbo);
    }
}