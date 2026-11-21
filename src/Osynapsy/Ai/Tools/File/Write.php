<?php

namespace Osynapsy\Ai\Tools\File;

use Osynapsy\Ai\Tools\ToolInterface;

class Write implements ToolInterface
{
    protected string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        // Imposta la stessa directory sandbox di default della classe Read
        $this->baseDir = rtrim($baseDir ?? sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    }

    public function execute(array $arguments): mixed
    {
        $filePath = trim($arguments['path'] ?? $arguments['file_path'] ?? '');
        $content  = $arguments['content'] ?? null;
        $append   = (bool) ($arguments['append'] ?? false);

        if ($filePath === '') {
            return [
                'error' => true,
                'message' => "Parametro 'path' mancante per il tool file.write."
            ];
        }

        if ($content === null) {
            return [
                'error' => true,
                'message' => "Parametro 'content' mancante per il tool file.write."
            ];
        }

        try {
            // 1. Ancoriamo i percorsi relativi alla baseDir
            $targetPath = str_starts_with($filePath, DIRECTORY_SEPARATOR)
                ? $filePath
                : $this->baseDir . DIRECTORY_SEPARATOR . $filePath;

            $realBaseDir = realpath($this->baseDir);
            if ($realBaseDir === false) {
                return [
                    'error' => true,
                    'message' => "Directory sandbox non valida o inesistente: {$this->baseDir}"
                ];
            }

            // 2. Sicurezza Sandbox per file che potrebbero NON esistere ancora:
            // Verifichiamo il percorso della directory padre + nome del file
            $parentDir = dirname($targetPath);
            $fileName  = basename($targetPath);

            // Impedisce nomi file invalidi o tentativi di attraversamento sul nome finale
            if ($fileName === '' || $fileName === '.' || $fileName === '..') {
                return [
                    'error' => true,
                    'message' => "Nome file non valido: {$filePath}"
                ];
            }

            // Se le sottocartelle intermedie non esistono ancora, le creiamo in sicurezza
            // solo dopo aver verificato che non contengano ".."
            if (str_contains($filePath, '..')) {
                return [
                    'error' => true,
                    'message' => "Accesso negato: il percorso '{$filePath}' contiene sequenze non consentite ('..')."
                ];
            }

            if (!is_dir($parentDir) && !mkdir($parentDir, 0755, true) && !is_dir($parentDir)) {
                return [
                    'error' => true,
                    'message' => "Impossibile creare la directory di destinazione: {$parentDir}"
                ];
            }

            $realParentDir = realpath($parentDir);

            // Controllo finale che la directory padre risolva dentro la sandbox
            if ($realParentDir === false || (
                $realParentDir !== $realBaseDir &&
                !str_starts_with($realParentDir, $realBaseDir . DIRECTORY_SEPARATOR)
            )) {
                return [
                    'error' => true,
                    'message' => "Accesso negato: il percorso '{$filePath}' è fuori dalla directory consentita."
                ];
            }

            $safeFinalPath = $realParentDir . DIRECTORY_SEPARATOR . $fileName;

            // Impedisce di sovrascrivere una directory esistente con lo stesso nome
            if (is_dir($safeFinalPath)) {
                return [
                    'error' => true,
                    'message' => "Impossibile scrivere: '{$filePath}' è una directory."
                ];
            }

            // 3. Scrittura su disco (con lock esclusivo e supporto append)
            $flags = LOCK_EX | ($append ? FILE_APPEND : 0);
            $bytes = file_put_contents($safeFinalPath, (string) $content, $flags);

            if ($bytes === false) {
                return [
                    'error' => true,
                    'message' => "Errore durante la scrittura del file: {$filePath}"
                ];
            }

            return [
                'output' => sprintf(
                    "File '%s' %s correttamente (%d byte).",
                    $filePath,
                    $append ? 'aggiornato in coda' : 'scritto',
                    $bytes
                )
            ];

        } catch (\Throwable $e) {
            return [
                'error' => true,
                'message' => "Errore durante la scrittura del file: {$e->getMessage()}"
            ];
        }
    }
}
