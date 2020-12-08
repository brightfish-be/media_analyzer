<?php


namespace Brightfish\SpxMediaAnalyzer;

use Exception;

class Ffprobe
{
    private string $binary;
    private array $version;
    private array $image_formats=["bmp","dpx","gif","jpeg","jpg","mjpeg","png","tif","tiff"];

    public function __construct(string $binary = "")
    {
        // this assumes that ffprobe executable is in the path somewhere
        $this->binary = "ffprobe";
        if ($binary) {
            if (! file_exists($binary)) {
                throw new Exception("Program [$binary] not found");
            }
            $this->$binary = $binary;
        }
        $commandParts = [];
        $commandParts[] = escapeshellarg($this->binary);
        $commandParts[] = "-version";
        $command = implode(" ", $commandParts);
        $output = [];
        $return = 0;
        exec($command, $output, $return);
        $this->version = $this->parse_ffprobe_version(implode("\n", $output));
        $this->version["file"] = basename($this->binary);
        $this->version["path"] = $this->binary;
        $this->version["command"] = $command;
        if (! file_exists($this->binary) && pathinfo($this->binary, PATHINFO_DIRNAME) == ".") {
            // $this->binary = ffprobe
            $path = "";
            if (stripos(PHP_OS, 'WIN') === 0) {
                // running on windows
                exec("where $this->binary", $path);
            } else {
                // running on Linux/MacOS
                exec("which $this->binary", $path);
            }
            if (isset($path[0])) {
                $this->version["path"] = $path[0];
            }
        }
    }

    public function probe(string $inputFile)
    {
        if (! file_exists($inputFile)) {
            throw new Exception("File [$inputFile] not found");
        }
        $commandParts = [];
        $commandParts[] = escapeshellarg($this->binary);
        $commandParts[] = "-v quiet"; // don't show banner or raw info
        $commandParts[] = "-print_format json"; // show all info as JSON
        $commandParts[] = "-show_format"; // include container info
        $commandParts[] = "-show_streams"; // include container info
        $commandParts[] = "-i " . escapeshellarg($inputFile);
        $commandParts[] = "2>&1";
        $command = implode(" ", $commandParts);

        $data = [];
        $data["command"]["binary"] = $this->version;
        $data["command"]["full"] = $command;
        $data["command"]["input"] = [
            "filename" => $inputFile,
            "filesize" => filesize($inputFile), // TODO: what to do for > 4GB files
            "modified" => date("c", filemtime($inputFile)),
            "changed" => date("c", filectime($inputFile)),
        ];

        $data["command"]["started_at"] = date("c");
        $t0 = microtime(true);
        exec($command, $output, $return);
        $t1 = microtime(true);
        $data["command"]["finished_at"] = date("c");
        $duration = round($t1 - $t0, 3);
        $data["command"]["duration"] = $duration;
        $data["command"]["return"] = $return;
        $data["result"] = json_decode(implode("\n", $output), true);
        ksort($data["result"]);
        foreach($data["result"]["streams"] as $i => $stream){
            $data["result"]["streams"][$i]["type"]=$stream["codec_type"];
            if(
                $data["result"]["format"]["nb_streams"] ==  1
                && $stream["codec_type"] == "video"
                && $stream["avg_frame_rate"] == "0/0"
                && in_array($stream["codec_name"],$this->image_formats)){
                $data["result"]["streams"][$i]["type"]="image";
            }
            ksort($data["result"]["streams"][$i]);
        }
        if (isset($data["result"]["format"]) && isset($data["result"]["streams"][0])) {
            ksort($data["result"]["format"]);
        }
        return $data;
    }

    // private methods

    private function find(string $haystack, string $pattern): string
    {
        $nb = preg_match($pattern, $haystack, $matches);
        if ($nb) {
            return $matches[1];
        }

        return "";
    }

    private function parse_ffprobe_version(string $text): array
    {
        /*
         * ffprobe version 4.3.1-0york0~16.04 Copyright (c) 2007-2020 the FFmpeg developers
         * built with gcc 5.4.0 (Ubuntu 5.4.0-6ubuntu1~16.04.12) 20160609
            configuration: --prefix=/usr (...) --enable-shared
            libavutil      56. 51.100 / 56. 51.100
            (...)
            libpostproc    55.  7.100 / 55.  7.100
         */
        $data = [];
        $data["_raw"] = $this->find($text, "|(.*)|");
        $data["version"] = $this->find($text, "|version ([\d\.]+)|");
        $data["year"] = $this->find($text, "|20\d\d-(\d\d\d\d)|");
        $data["gcc_version"] = $this->find($text, "|built with gcc ([\d\.]+)|");
        ksort($data);

        /*
         * [_raw] => ffprobe version 4.3.1-0york0~16.04 Copyright (c) 2007-2020 the FFmpeg developers
                    [gcc_version] => 5.4.0
                    [version] => 4.3.1
                    [year] => 2020
                    [file] => ffprobe
                    [path] => /usr/bin/ffprobe
                    [command] => 'ffprobe' -version

         */
        return $data;
    }
}
