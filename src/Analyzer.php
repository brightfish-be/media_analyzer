<?php
// Author: Peter Forret (cinemapub, p.forret@brightfish.be)
namespace Brightfish\SpxMediaAnalyzer;

use Exception;

class Analyzer
{
    /**
     * @var Ffmpeg
     */
    private Ffmpeg $ffmpeg;

    public function __construct(string $path = "")
    {
        $this->ffmpeg = new Ffmpeg();
        if ($path) {
            $this->ffmpeg->use_ffmpeg($path);
        }
    }

    public function meta(string $path): array
    {
        if (! file_exists($path)) {
            throw new Exception("`$path` does not exist");
        }
        $this->ffmpeg = new Ffmpeg();
        $data = $this->ffmpeg->run_ffmpeg($path);
        $lines = $data["output"];
        $meta = [];

        $meta["file"] = $this->get_file_meta($path);

        $output = implode("\n", $lines);
        $inputs = $this->split_on($output, "|Input (#\d+)|");
        if (count($inputs) < 2) {
            $meta["error"] = "no input found in file";

            return $meta;
        }
        $input_id = 0;
        foreach ($inputs as $input) {
            if ($input_id === 0) {
                // ffmpeg version and libraries info
                /*
                 * ffmpeg version 4.1.3-0york1~16.04 Copyright (c) 2000-2019 the FFmpeg developers
                    built with gcc 5.4.0 (Ubuntu 5.4.0-6ubuntu1~16.04.11) 20160609
                    configuration: --prefix=/usr (...)) --enable-shared
                    libavutil      56. 22.100 / 56. 22.100
                    (...)
                    libpostproc    55.  3.100 / 55.  3.100
                 */
                $meta["ffmpeg"] = $this->parse_ffmpeg_version($input);
                $meta["ffmpeg"]["path"] = $data["program"];
            } else {
                // there will only be 1 input
                $streams = $this->split_on($input, "|Stream (#\d:\d)|");
                $stream_id = 0;
                foreach ($streams as $stream) {
                    if ($stream_id === 0) {
                        // valid for the whole input
                        /*
                            , mov,mp4,m4a,3gp,3g2,mj2, from '(...)/spx_media_analyzer/tests/sources/video.mov':
                            Metadata:
                            major_brand     : qt
                            minor_version   : 512
                            compatible_brands: qt
                            encoder         : Lavf58.45.100
                            Duration: 00:00:01.00, start: 0.000000, bitrate: 99985 kb/s
                        */
                        $meta["duration"] = $this->parse_duration($stream);
                        $meta["metadata"] = $this->parse_metadata($stream);
                    } else {
                        // an actual stream
                        /*
                        (eng): Video: prores (apcn / 0x6E637061), yuv422p10le(tv, bt709, progressive), 1920x1080, 98431 kb/s, SAR 1:1 DAR 16:9, 25 fps, 25 tbr, 12800 tbn, 12800 tbc (default)
                        Metadata:
                        handler_name    : Apple Video Media Handler
                        encoder         : Apple ProRes¬¨‚Ä†422
                        timecode        : 00:00:00:00
                        */
                        $stream_data = $this->parse_stream_data($stream);
                        $type = $stream_data["type"];
                        $meta["streams"][$stream_id] = $stream_data;
                        $meta[$type] = $stream_data["details"];
                    }
                    $stream_id++;
                }
            }
            $input_id++;
        }

        return $meta;
    }


    private function parse_ffmpeg_version(string $text): array
    {
        $data = [];
        $data["_raw"] = $this->find($text, "|(.*)|");
        $data["version"] = $this->find($text, "|version ([\d\.]+)|");
        $data["year"] = $this->find($text, "|2000-(\d\d\d\d)|");
        $data["gcc_version"] = $this->find($text, "|built with gcc ([\d\.]+)|");
        ksort($data);

        return $data;
    }

    private function parse_duration(string $text): array
    {
        $data = [];
        $data["_raw"] = trim($this->find($text, "|Duration: ([^\s]*)|"));
        if ($data["_raw"]) {
            $data["length"] = $this->find($data["_raw"], "|(\d\d:\d\d:\d\d\.\d\d)|");
            if ($data["length"]) {
                list($hour, $min, $sec) = explode(":", $data["length"], 3);
                $secs = (int) $hour * 3600 + (int) $min * 60 + (double)$sec;
                $data["seconds"] = round($secs, 2);
            }
        }
        ksort($data);

        return $data;
    }

    private function parse_metadata(string $text): array
    {
        $data = [];
        if (strstr($text, "Metadata:")) {
            $this->find_label($text, "encoder", $data);
            $this->find_label($text, "handler_name", $data);
            $this->find_label($text, "compatible_brands", $data);
            $this->find_label($text, "timecode", $data);
        }
        ksort($data);

        return $data;
    }

    private function parse_stream_data(string $text): array
    {
        $data = [];
        $data["_raw"] = substr($text, 0, strpos($text, "\n")); // take first line
        $data["metadata"] = $this->parse_metadata($text);
        $type = "";
        if (strstr($data["_raw"], "Data:")) {
            $type = "data";
        }
        if (strstr($data["_raw"], "Audio:")) {
            $type = "audio";
        }
        if (strstr($data["_raw"], "Video:")) {
            $type = "video";
        }
        $data["type"] = $type;
        if ($type === "audio") {
            $data["details"] = $this->parse_audio_line($data["_raw"]);
        }
        if ($type === "video") {
            $data["details"] = $this->parse_video_line($data["_raw"]);
        }
        if ($type === "data") {
            $data["details"] = $this->parse_data_line($data["_raw"]);
        }
        ksort($data);

        return $data;
    }

    private function parse_audio_line(string $line): array
    {
        // pcm_s24le ([1][0][0][0] / 0x0001), 48000 hz, 5.1, s32 (24 bit), 6912 kb/s
        $data = [];
        $data["_raw"] = trim($this->find($line, "|Audio:\s+(.*)|"));
        $audio_channels = $this->find($line, "|(\d) channels|");
        if (! $audio_channels and strstr($line, "stereo")) {
            $audio_channels = 2;
        }
        if (! $audio_channels and strstr($line, "5.1")) {
            $audio_channels = 6;
        }
        if (! $audio_channels) {
            $audio_channels = 1;
        }
        $data["channels"] = $audio_channels;
        $data["kbps"] = $this->find($line, "|(\d+) kb/s|");
        $data["bps"] = 1000 * (double)$data["kbps"];
        if ($audio_channels and $data["kbps"]) {
            $channel_kbps = (double)$data["kbps"] / (int)$audio_channels;
            $data["kbps_channel"] = round($channel_kbps, 1);
        }
        $data["bits"] = $this->find($line, "|\((\d+) bit\)|");
        if (! $data["bits"]) {
            $data["bits"] = 16;
        }
        $data["hertz"] = $this->find($line, "|(\d\d\d\d\d) hz|");
        if (! $data["hertz"]) {
            $data["hertz"] = 48000;
        }
        $uncomp = (int)$data["hertz"] * (int)$data["bits"] * (int)$data["channels"];
        $data["compression"] = round($data["bps"] / $uncomp, 3);
        $data["compression_percent"] = round(100 * $data["bps"] / $uncomp) . "%";
        $data["quality"] = "high";
        if ($data["hertz"] < 44000) {
            $data["quality"] = "low";
        }
        if ($data["bits"] < 16) {
            $data["quality"] = "low";
        }
        if ($data["compression"] < 0.1) {
            $data["quality"] = "low";
        }
        $codec = substr($data["_raw"], 0, strpos($data["_raw"], ","));
        $data["codec"] = trim(preg_replace("#(\(.*\))#", "", $codec));
        ksort($data);

        return $data;
    }

    private function parse_video_line(string $line): array
    {
        $data = [];
        $data["_raw"] = trim($this->find($line, "|Video:\s+(.*)|"));
        $line = strtolower($line);
        $data["size"] = $this->find($line, "|(\d\d+x\d\d+)|");
        if ($data["size"]) {
            list($w, $h) = explode("x", $data["size"]);
            $data["width"] = (int)$w;
            $data["height"] = (int)$h;
            $data["pixels"] = (int)$w * (int)$h;
            $data["dar"] = $this->find($line, "|dar ([\d\.:]+)|");
            $data["aspect_ratio"] = round((double)$w / (double)$h, 2);
            switch ($data["aspect_ratio"]) {
                case 1.78:
                    $data["aspect_type"] = "hd";

break;
                case 1.90:
                    $data["aspect_type"] = "dcp";

break;
                case 1.85:
                    $data["aspect_type"] = "flat";

break;
                case 2.35:
                case 2.39:
                    $data["aspect_type"] = "scope";

break;
                case 1.33:
                    $data["aspect_type"] = "tv";

break;
                case 1:
                    $data["aspect_type"] = "square";

break;
            }
        }
        $data["chroma"] = $this->find($line, "|(yuv[\w]+)|");
        if (! $data["chroma"]) {
            $data["chroma"] = $this->find($line, "|(rgb[\w]+)|");
        }
        if (! $data["chroma"]) {
            $data["chroma"] = $this->find($line, "|(xyz[\w]+)|");
        }
        $data["fps"] = (double)$this->find($line, "|([\d\.]+) tbr|");
        if (! $data["fps"]) {
            $data["fps"] = (double)$this->find($line, "|([\d\.]+) fps|");
        }
        $data["kbps"] = (double)$this->find($line, "|(\d+) kb/s|");
        if (isset($data["kbps"])) {
            $data["bps"] = $data["kbps"] * 1000;
        }
        $codec = substr($data["_raw"], 0, strpos($data["_raw"], ","));
        $data["codec"] = trim(preg_replace("#(\(.*\))#", "", $codec));
        if (isset($data["fps"]) && isset($data["pixels"])) {
            $uncompressed = $data["pixels"] * 24 * $data["fps"];
            $data["compression"] = round($data["bps"] / $uncompressed, 3);
            $data["compression_percent"] = round(100 * $data["bps"] / $uncompressed, 1) . "%";
        }
        ksort($data);

        return $data;
    }

    private function parse_data_line($line): array
    {
        $data = [];
        $data["_raw"] = trim($this->find($line, "|Data:\s+(.*)|"));

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------

    public function get_file_meta(string $path): array
    {
        $data = [];
        $data["name"] = basename($path);
        $data["extension"] = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $data["folder"] = pathinfo($path, PATHINFO_DIRNAME);
        $data["size"] = filesize($path);
        $data["mtime"] = filemtime($path);
        $data["modification"] = date("c", filemtime($path));
        $data["days"] = round((time() - filemtime($path)) / (3600 * 24), 2);

        return $data;
    }

    public function split_on(string $text, string $pattern): array
    {
        return preg_split($pattern, $text);
    }

    private function find(string $haystack, string $pattern): string
    {
        $nb = preg_match($pattern, $haystack, $matches);
        if ($nb) {
            return $matches[1];
        }

        return "";
    }

    private function find_label(string $haystack, string $label, array &$array = []): string
    {
        $nb = preg_match("|$label\s*:\s+(.*)|", $haystack, $matches);
        if ($nb) {
            $value = $matches[1];
            if (isset($array)) {
                $array[$label] = $value;
            }

            return $value;
        }

        return "";
    }
}
