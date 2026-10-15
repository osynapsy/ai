<?php
namespace Osynapsy\Ai\Tools;

class ToolContext
{
    protected static array $resourceDirs = [__DIR__ . '/resources'];

    /**
     * Permette di aggiungere una directory esterna di risorse Markdown
     */
    public static function addResourceDir(string $dir): void
    {
        if (is_dir($dir)) {
            self::$resourceDirs[] = rtrim($dir, DIRECTORY_SEPARATOR);
        }
    }
    
    /**
     * Carica e concatena tutti i file Markdown (.md) presenti nella cartella resources.
     */
    public static function get(): string
    {
        // Directory nativa del package
        $dirs = self::$resourceDirs;        

        $contents = [];

        foreach ($dirs as $resourcesDir) {
            if (!is_dir($resourcesDir)) {
                continue;
            }

            $files = glob($resourcesDir . '/*.md');
            if (empty($files)) {
                continue;
            }

            sort($files);

            foreach ($files as $filePath) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    $contents[] = trim($content);
                }
            }
        }

        return implode("\n\n---\n\n", $contents);
    }
    
}

