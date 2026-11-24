<?php
namespace Osynapsy\Ai\Prompt;

class PromptImage
{
    protected ?string $filePath = null;
    protected ?string $base64Data = null;

    /**
     * @param string $filePathOrBase64 Percorso assoluto/relativo del file oppure stringa Base64
     * @param string|null $storageDir Cartella base in cui persistere i Base64 su disco (es. '/var/www/storage/ai_images')
     */
    public function __construct(string $filePathOrBase64, ?string $storageDir = null)
    {
        // Caso 1: È già un percorso file esistente
        if (strlen($filePathOrBase64) < 512 && is_file($filePathOrBase64)) {
            $this->filePath = $filePathOrBase64;
            return;
        }

        // Caso 2: È una stringa Base64 -> Se è configurata una storageDir, la salviamo subito su disco
        if ($storageDir !== null) {
            $this->filePath = $this->persistBase64ToDisk($filePathOrBase64, $storageDir);
            return;
        }

        // Fallback in memoria se non è stata passata una directory di storage
        $this->base64Data = $this->stripDataUriPrefix($filePathOrBase64);
    }

    public function getBase64(): string
    {
        if ($this->filePath !== null && is_file($this->filePath)) {
            return base64_encode(file_get_contents($this->filePath));
        }

        return $this->base64Data ?? '';
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getWebPath()
    {
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->filePath);
    }

    /**
     * Quando PHP serializza l'oggetto per salvarlo sul DB, salva SOLO $filePath (pochi byte!)
     */
    public function __serialize(): array
    {
        return [
            'filePath'   => $this->filePath,
            'base64Data' => $this->filePath ? null : $this->base64Data,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->filePath   = $data['filePath'] ?? null;
        $this->base64Data = $data['base64Data'] ?? null;
    }

    private function persistBase64ToDisk(string $rawBase64, string $storageDir): string
    {
        $cleanBase64 = $this->stripDataUriPrefix($rawBase64);
        $binary = base64_decode($cleanBase64, true);

        if ($binary === false) {
            throw new \InvalidArgumentException('Immagine Base64 non valida.');
        }

        // Rileva estensione dai magic bytes
        $ext = match (true) {
            str_starts_with($binary, "\x89PNG") => 'png',
            str_starts_with($binary, "\xFF\xD8\xFF") => 'jpg',
            str_starts_with($binary, "RIFF") => 'webp',
            default => 'bin'
        };

        $subDir = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date('Y/m');
        if (!is_dir($subDir)) {
            mkdir($subDir, 0755, true);
        }

        $fullPath = $subDir . DIRECTORY_SEPARATOR . uniqid('img_', true) . '.' . $ext;
        file_put_contents($fullPath, $binary, LOCK_EX);

        return $fullPath;
    }

    public static function getMimeType($filePath, $base64Image)
    {
        return ($filePath && is_file($filePath))
                ? (mime_content_type($filePath) ?: self::detectBase64MimeType($base64Image))
                : self::detectBase64MimeType($base64Image);
    }

    private function stripDataUriPrefix(string $base64): string
    {
        if (str_contains($base64, ';base64,')) {
            return explode(';base64,', $base64, 2)[1];
        }
        return $base64;
    }

    /**
    * Helper per determinare il MIME type dalla stringa base64
    */
    public static function detectBase64MimeType(string $base64): string
    {
        // Il tuo esempio inizia con "iVBORw0KGgo...", che è la firma PNG (\x89PNG)
        if (str_starts_with($base64, 'iVBORw0KGgo')) {
            return 'image/png';
        }
        if (str_starts_with($base64, '/9j/')) {
            return 'image/jpeg';
        }
        if (str_starts_with($base64, 'R0lGOD')) {
            return 'image/gif';
        }
        if (str_starts_with($base64, 'UklGR')) {
            return 'image/webp';
        }
        // Fallback tramite finfo se disponibile
        if (function_exists('finfo_open')) {
            $decoded = base64_decode(substr($base64, 0, 100), true);
            if ($decoded !== false) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $decoded);
                finfo_close($finfo);
                if ($mime) {
                    return $mime;
                }
            }
        }

        return 'image/jpeg'; // Default sicuro
    }
}
