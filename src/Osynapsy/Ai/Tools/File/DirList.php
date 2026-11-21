<?php
namespace Osynapsy\Ai\Tools\File;

use Osynapsy\Ai\Tools\ToolInterface;

class DirList implements ToolInterface
{
    protected string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = rtrim($baseDir ?? $_SERVER['DOCUMENT_ROOT'] . '/../', DIRECTORY_SEPARATOR);
    }

    public function handle(array $arguments): mixed
    {
        return $this->execute($arguments);
    }

    public function execute(array $arguments, $dummy = null): mixed
    {
        $subPath = trim($arguments['path'] ?? '');

        try {
            $realBaseDir = realpath($this->baseDir);
            if ($realBaseDir === false) {
                return [
                    'error' => true,
                    'message' => "Directory sandbox non valida: {$this->baseDir}"
                ];
            }

            // Se path è vuoto o '.', esplora la root della sandbox
            $targetPath = ($subPath === '' || $subPath === '.')
                ? $realBaseDir
                : (str_starts_with($subPath, DIRECTORY_SEPARATOR)
                    ? $subPath
                    : $realBaseDir . DIRECTORY_SEPARATOR . $subPath);

            $realTarget = realpath($targetPath);

            if ($realTarget === false || !is_dir($realTarget)) {
                return [
                    'error' => true,
                    'message' => "La directory specificata non esiste: {$subPath}"
                ];
            }

            // Controllo Sandbox: può essere uguale alla baseDir oppure una sua sottocartella
            if (
                $realTarget !== $realBaseDir &&
                !str_starts_with($realTarget, $realBaseDir . DIRECTORY_SEPARATOR)
            ) {
                return [
                    'error' => true,
                    'message' => "Accesso negato: il percorso '{$subPath}' è fuori dalla directory consentita."
                ];
            }

            $entries = scandir($realTarget);
            if ($entries === false) {
                return [
                    'error' => true,
                    'message' => "Impossibile leggere il contenuto della directory: {$subPath}"
                ];
            }

            $items = [];
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $fullItemPath = $realTarget . DIRECTORY_SEPARATOR . $entry;
                $isDir = is_dir($fullItemPath);

                $items[] = [
                    'name'        => $entry,
                    'type'        => $isDir ? 'directory' : 'file',
                    'size_bytes'  => $isDir ? null : filesize($fullItemPath),
                    'modified_at' => date('Y-m-d H:i:s', filemtime($fullItemPath))
                ];
            }

            return [
                'output' => [
                    'directory' => $subPath === '' ? '.' : $subPath,
                    'count'     => count($items),
                    'items'     => $items
                ]
            ];

        } catch (\Throwable $e) {
            return [
                'error' => true,
                'message' => "Errore durante l'elenco della directory: {$e->getMessage()}"
            ];
        }
    }
}
