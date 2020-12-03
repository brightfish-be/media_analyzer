<?php


namespace Brightfish\SpxMediaAnalyzer;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Sabre\Cache\Memory;

class Ffmpeg
{
    private string $binary;
    private CacheInterface $cache;
    private LoggerInterface $logger;
    private int $cache_expiration;

    public function __construct(string $binary = "")
    {
        // this assumes that ffmpeg executable is in the path somewhere
        $this->binary = "ffmpeg";
        if ($binary) {
            $this->useBinary($binary);
        }
        $this->cache_expiration = 3600;
        $this->logger = new NullLogger();
        $this->cache = new Memory();
    }

    public function useBinary(string $path): self
    {
        // to set the ffmpeg path explicitly
        if (! file_exists($path)) {
            throw new Exception("Binary [$path] not found");
        }
        $this->binary = $path;

        return $this;
    }

    public function useLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function useCache(CacheInterface $cache, int $expiration = 3600):self
    {
        $this->cache = $cache;
        $this->cache_expiration = $expiration;

        return $this;
    }

    public function run(string $inputFile, string $outputFile = "", array $parameters = [], bool $useCache = false): array
    {
        $commandParts = [];
        if ($this->binary === "ffmpeg") {
            $commandParts[] = $this->binary;
        } else {
            $commandParts[] = $this->addQuotes($this->binary, true);
        }
        $inputIsMoreRecent=false;
        if($inputFile && is_file($inputFile) && $outputFile && $outputFile <> "-"){
            if(filemtime($inputFile) > filemtime($outputFile)){
                $inputIsMoreRecent=true;
            }
        }
        if ($inputFile) {
            $commandParts[] = "-i " . $this->addQuotes($inputFile);
        }
        $commandParts = array_merge($commandParts, $parameters);
        if ($outputFile) {
            $commandParts[] = "-y " . $this->addQuotes($outputFile);
        }
        $commandParts[] = "2>&1";
        $command = implode(" ", $commandParts);
        $key = $command;
        if (! $inputIsMoreRecent && $useCache && $this->cache->has($key)) {
            $data = $this->cache->get($key);
            $data["from_cache"] = date("c");

            return $data;
        }
        $data = [];
        $data["program"] = $this->binary;
        $data["command"] = $command;
        if (file_exists($inputFile)) {
            $data["input"] = [
                "filename" => $inputFile,
                "filesize" => filesize($inputFile), // TODO: check if it works for > 4GB files
            ];
        }
        $data["started_at"] = date("c");
        $t0 = microtime(true);
        exec($command, $output, $return);
        $t1 = microtime(true);
        $data["finished_at"] = date("c");
        $duration = round($t1 - $t0, 3);
        $data["duration"] = $duration;
        $data["return"] = $return;
        $this->logger->info("Executed [$command] in $duration seconds");
        if (file_exists($outputFile)) {
            $data["output"] = [
                "filename" => $outputFile,
                "filesize" => filesize($outputFile),
            ];
        }
        $data["output"] = $output;
        if ($useCache) {
            $this->cache->set($key, $data, $this->cache_expiration);
        }

        return $data;
    }

    private function addQuotes(string $path, bool $dont_expand = false): string
    {
        if (! $dont_expand) {
            if (file_exists($path)) {
                $path = realpath($path);
            } elseif (file_exists(dirname($path))) {
                $path = realpath(dirname($path)) . DIRECTORY_SEPARATOR  . basename($path);
            }
        }

        return '"' . $path . '"';
    }
}
