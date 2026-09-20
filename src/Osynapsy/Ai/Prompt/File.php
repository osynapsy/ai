<?php
namespace Osynapsy\Ai\Prompt;


class File implements PromptInterface
{
    protected $file;

    public function __construct($filename)
    {
        $this->file = new \CURLFile($filename);
    }

    public function format(?Formatter\PromptFormatterInterface $formatter = null) : mixed
    {
        return ['file' => $this->file];
    }
    
    protected function convertAudioToWav($rawfileName)
    {
        $fileName = $_SERVER['DOCUMENT_ROOT'] . $rawfileName;
        // Controlla che il file di input esista
        if (!file_exists($fileName)) {
            throw new \Exception(sprintf('The file %s do not exists', $fileName));
        }
        // Imposta il path del file di output: stessa cartella, stesso nome ma con estensione .wav
        $outputPath = preg_replace('/\.(ogg|opus)$/i', '.wav', $fileName);
        // Comando ffmpeg per convertire il file
        // -y sovrascrive senza chiedere, -loglevel error limita output solo a errori
        $cmd = "ffmpeg -y -i " . escapeshellarg($fileName) . " -ar 16000 -ac 1 -loglevel error " . escapeshellarg($outputPath) . " 2>&1";
        // Esegue il comando e cattura l'output e il codice di uscita
        exec($cmd, $output, $return_var);
        // Se il comando è andato a buon fine e il file di output è stato creato
        if ($return_var === 0 && file_exists($outputPath)) {
            return $outputPath;
        }        
        throw new \Exception('Conversione ogg => wav non riuscita. ffmpeg output: ' . implode("\n", $output));        
    }
}
