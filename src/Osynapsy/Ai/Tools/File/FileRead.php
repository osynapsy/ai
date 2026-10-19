<?php

namespace Osynapsy\Ai\Tools\File;

use Osynapsy\Ai\Tools\ToolInterface;

class FileRead implements ToolInterface
{
    public function execute(array $arguments, $dummy = null): mixed
    {
        // Recupero e pulizia del percorso
        $filePath = trim($arguments['file_path'] ?? '');

        if (empty($filePath)) {
            return [
                'error' => "Parametro 'file_path' mancante per il tool file.read."
            ];
        }

        try {
            // 1. Controllo se il file esiste fisicamente
            if (!file_exists($filePath)) {
                return [
                    'error' => true,
                    'message' => "Il file specificato non esiste: {$filePath}"
                ];
            }

            // 2. Sicurezza: Impedire la lettura di file tramite ".." o percorsi assoluti indesiderati
            // Normalizziamo il percorso per evitare manipolazioni come ../../../etc/passwd
            $realPath = realpath($filePath);
            
            if ($realPath === false) {
                return [
                    'error' => true,
                    'message' => "Impossibile risolvere il percorso del file: {$filePath}"
                ];
            }

            // Opzionale: Se hai una directory base di riferimento (es. /var/www/data), 
            // potresti voler verificare che $realPath sia un sotto-percorso di quella directory.
            // Per ora, la sicurezza di base è bloccare i percorsi nulli o non validi.

            // 3. Lettura del contenuto
            $content = file_get_contents($filePath);

            if ($content === false) {
                return [
                    'error' => true,
                    'message' => "Impossibile leggere il contenuto del file: {$filePath} (Permesso negato o formato non leggibile)."
                ];
            }

            // 4. Restituzione del risultato
            return [
                'output' => $content
            ];

        } catch (\Throwable $e) {
            // Gestione degli errori di runtime (es. permessi in scrittura se il file è un symlink, ecc.)
            return [
                'error' => true,
                'message' => "Errore durante la lettura del file: {$e->getMessage()}"
            ];
        }
    }
}
