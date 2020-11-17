<?php


namespace Brightfish\SpxMediaAnalyzer;

use Exception;

class Ffmpeg
{
    private string $program = "";
    private $cache = null;
    private string $logFolder = "";

    public function __construct()
    {
        // this assumes that ffmpeg executable is in the path somewhere
        $this->program = "ffmpeg";
    }

    public function use_ffmpeg(string $path)
    {
        // to set the ffmpeg path explicitly
        if (! file_exists($path)) {
            throw new Exception("Program [$path] not found");
        }
        $this->program = $path;

        return $this;
    }

    public function log_to_folder(string $path)
    {
        if (! file_exists($path)) {
            throw new Exception("Program [$path] not found");
        }
        $this->logFolder = $path;

        return $this;
    }

    public function cache_to_folder(string $path)
    {
        if (! is_dir($path)) {
            throw new Exception("Log folder [$path] not found");
        }
        $this->cache = new Cache($path, 36000, "ffmpeg");

        return $this;
    }

    public function run_ffmpeg(string $inputFile, string $outputFile = "", array $parameters = [], bool $cache_results = false)
    {
        $data = [];
        $commandParts = [];
        if ($this->program === "ffmpeg") {
            $commandParts[] = $this->program;
        } else {
            $commandParts[] = $this->addQuotes($this->program, true);
        }
        $commandParts[] = "-i " . $this->addQuotes($inputFile);
        $commandParts = array_merge($commandParts, $parameters);
        if ($outputFile) {
            $commandParts[] = "-y " . $this->addQuotes($outputFile);
        }
        $commandParts[] = "2>&1";
        $command = implode(" ", $commandParts);
        $data["command"] = $command;
        $uniq = substr(sha1($command), 0, 10);
        $key = $command;
        $data["key"] = $key;
        if (file_exists($inputFile)) {
            $data["input"] = [
                "filename" => $inputFile,
                "filesize" => filesize($inputFile),
            ];
        }
        $data["started_at"] = date("c");
        if ($cache_results && $this->cache && $this->cache->exists($key)) {
            return $this->cache->get($key);
        }
        $t0 = microtime(true);
        exec($command, $output, $return);
        $t1 = microtime(true);
        $data["finished_at"] = date("c");
        $data["duration"] = round($t1 - $t0, 3);
        $data["return"] = $return;
        if (file_exists($outputFile)) {
            $data["output"] = [
                "filename" => $outputFile,
                "filesize" => filesize($outputFile),
            ];
        }
        $data["output"] = $output;
        if ($cache_results && $this->cache) {
            $this->cache->set($key, $data);
        }

        return $data;
    }

    public function addQuotes(string $path, bool $dont_expand = false)
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
