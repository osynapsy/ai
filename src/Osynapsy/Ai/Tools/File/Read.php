<?php
namespace Osynapsy\Ai\Tools\File;

use Osynapsy\Ai\Tools\ToolInterface;

class Read implements ToolInterface
{
    protected string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        // Imposta una directory sandbox di default (es. storage/workspace del progetto)
        $this->baseDir = rtrim($baseDir ?? $_SERVER['DOCUMENT_ROOT'] . '/../', DIRECTORY_SEPARATOR);
    }

    public function execute(array $arguments): mixed
    {
        // Allineato a "path" (con fallback su "file_path" per retrocompatibilità)
        $filePath = trim($arguments['path'] ?? $arguments['file_path'] ?? '');

        if ($filePath === '') {
            return [
                'error' => true,
                'message' => "Parametro 'path' mancante per il tool file.read."
            ];
        }

        try {
            // Se il percorso è relativo, lo ancoriamo alla directory consentita
            $targetPath = str_starts_with($filePath, DIRECTORY_SEPARATOR)
                ? $filePath
                : $this->baseDir . DIRECTORY_SEPARATOR . $filePath;

            $realPath = realpath($targetPath);
            $realBaseDir = realpath($this->baseDir);

            // 1. Verifica esistenza e che sia effettivamente un file (non una directory)
            if ($realPath === false || !is_file($realPath)) {
                return [
                    'error' => true,
                    'message' => "Il file specificato non esiste o non è un file valido: {$filePath}"
                ];
            }

            // 2. Sicurezza Sandbox: impedisce l'uscita dalla baseDir tramite "../" o path assoluti esterni
            if ($realBaseDir !== false && !str_starts_with($realPath, $realBaseDir . DIRECTORY_SEPARATOR)) {
                return [
                    'error' => true,
                    'message' => "Accesso negato: il percorso '{$filePath}' è fuori dalla directory consentita."
                ];
            }

            // 3. Lettura dal percorso canonico risolto ($realPath)
            $content = file_get_contents($realPath);

            if ($content === false) {
                return [
                    'error' => true,
                    'message' => "Impossibile leggere il contenuto del file: {$filePath}"
                ];
            }

            return [
                'output' => $content
            ];

        } catch (\Throwable $e) {
            return [
                'error' => true,
                'message' => "Errore durante la lettura del file: {$e->getMessage()}"
            ];
        }
    }
}