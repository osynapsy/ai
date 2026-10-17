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
    public static function getToolsContext(): string
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


    public static function getTools(): array
    {
        // Directory nativa del package
        $dirs = self::$resourceDirs;

        $tools = [];

        foreach ($dirs as $resourcesDir) {
            if (!is_dir($resourcesDir)) {
                continue;
            }

            $files = glob($resourcesDir . '/*.json');
            if (empty($files)) {
                continue;
            }

            sort($files);

            foreach ($files as $filePath) {
                $toolNamespace = json_decode(file_get_contents($filePath));
                if ($toolNamespace !== false) {
                    $tools[] = $toolNamespace;
                }
            }
        }
        return $tools;
    }

    public static function ensureDatabaseSchemaCache($databaseService, string $cacheFilePath = null): void
    {
        if (!$cacheFilePath) {
            $cacheFilePath = __DIR__ . '/resources/db_schema_cache.txt';
        }

        // Se il file esiste non viene rigenerato: puoi quindi aggiungerci a mano
        // le note di business senza che vengano sovrascritte.
        // Per rigenerarlo basta cancellare il file (le note a mano andrebbero perse).
        if (file_exists($cacheFilePath)) {
            return;
        }

        try {
            $db_desc = new Db\DescribeAll;
            // 1. Recupera l'elenco delle tabelle/viste
            $tables = $db_desc->execute([], dbo());

            if (empty($tables)) {
                return;
            }

            // 2. Costruisce il contenuto compatto dello schema
            $out  = "# DB schema (compatto). Formato: tabella(colonna:tipo,...)\n";
            $out .= "# Tipi: i=int, iu=int unsigned, vN=varchar(N), chN=char(N), t=text, mt=mediumtext, lt=longtext, "
                  . "d=date, dt=datetime, ts=timestamp, tm=time, dec(p,s)=decimal (u=unsigned), db=double, f=float, "
                  . "b=tinyint(1) booleano, bu=tinyint(1) unsigned, ti=tinyint, si=smallint, mi=mediumint, bi=bigint, "
                  . "bl=blob, mb=mediumblob, lb=longblob\n\n";
            $out .= "## TABELLE\n";

            foreach ($tables as $tableName => $columns) {
                $cols = [];
                if (!empty($columns) && is_array($columns)) {
                    foreach ($columns as $col) {
                        $colName = $col['Field'] ?? $col['column_name'] ?? reset($col);
                        $colType = $col['Type'] ?? $col['data_type'] ?? '';
                        $cols[]  = $colName . ':' . self::compactColumnType((string)$colType);
                    }
                }
                $out .= $tableName . '(' . implode(',', $cols) . ")\n";
            }

            // 3. Salva il file fisico nella cartella resources del package
            file_put_contents($cacheFilePath, $out);

        } catch (\Exception $e) {
            // Gestione silenziosa o log dell'errore
        }
    }

    /**
     * Abbrevia il tipo di colonna MySQL (es. "int(11) unsigned" => "iu", "varchar(255)" => "v255").
     */
    private static function compactColumnType(string $type): string
    {
        $t = strtolower(trim($type));
        $unsigned = strpos($t, 'unsigned') !== false;

        // enum/set: si mantengono per esteso (i valori sono informativi)
        if (preg_match('/^(enum|set)\b/', $t)) {
            return preg_replace('/\s+/', '', $type);
        }

        if (!preg_match('/^([a-z]+)\s*(?:\(([^)]*)\))?/', $t, $m)) {
            return $t;
        }
        $base = $m[1];
        $arg  = isset($m[2]) ? str_replace(' ', '', $m[2]) : '';

        switch ($base) {
            case 'int':
            case 'integer':   return $unsigned ? 'iu' : 'i';
            case 'tinyint':
                if ($arg === '1') {
                    return $unsigned ? 'bu' : 'b';
                }
                return $unsigned ? 'tiu' : 'ti';
            case 'smallint':  return $unsigned ? 'siu' : 'si';
            case 'mediumint': return $unsigned ? 'miu' : 'mi';
            case 'bigint':    return $unsigned ? 'biu' : 'bi';
            case 'varchar':   return 'v' . $arg;
            case 'char':      return 'ch' . $arg;
            case 'text':      return 't';
            case 'tinytext':  return 'tt';
            case 'mediumtext':return 'mt';
            case 'longtext':  return 'lt';
            case 'date':      return 'd';
            case 'datetime':  return 'dt';
            case 'timestamp': return 'ts';
            case 'time':      return 'tm';
            case 'year':      return 'y';
            case 'decimal':
            case 'numeric':   return 'dec' . ($arg !== '' ? '(' . $arg . ')' : '') . ($unsigned ? 'u' : '');
            case 'double':    return 'db';
            case 'float':     return 'f';
            case 'blob':      return 'bl';
            case 'tinyblob':  return 'tb';
            case 'mediumblob':return 'mb';
            case 'longblob':  return 'lb';
            default:          return $t;
        }
    }

}

