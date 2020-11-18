<?php


namespace Brightfish\SpxMediaAnalyzer;

use Exception;

class Ffmpeg
{
    private string $program;
    private string $logFolder;
    private Cache $cache;

    public function __construct()
    {
        // this assumes that ffmpeg executable is in the path somewhere
        $this->program = "ffmpeg";
        $this->logFolder = "";
    }

    public function use_ffmpeg(string $path): self
    {
        // to set the ffmpeg path explicitly
        if (! file_exists($path)) {
            throw new Exception("Program [$path] not found");
        }
        $this->program = $path;

        return $this;
    }

    public function log_to_folder(string $path): self
    {
        if (! file_exists($path)) {
            throw new Exception("Log folder [$path] not found");
        }
        $this->logFolder = $path;

        return $this;
    }

    public function cache_to_folder(string $path): self
    {
        if (! is_dir($path)) {
            throw new Exception("Cache folder [$path] not found");
        }
        $this->cache = new Cache($path, 36000, "ffmpeg");

        return $this;
    }

    public function run_ffmpeg(string $inputFile, string $outputFile = "", array $parameters = [], bool $cache_results = false): array
    {
        $commandParts = [];
        if ($this->program === "ffmpeg") {
            $commandParts[] = $this->program;
        } else {
            $commandParts[] = $this->addQuotes($this->program, true);
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
        if ($cache_results && isset($this->cache) && $this->cache->exists($key)) {
            $data = $this->cache->get($key);
            $data["from_cache"] = date("c");

            return $data;
        }
        $data = [];
        $data["program"] = $this->program;
        $data["command"] = $command;
        if (file_exists($inputFile)) {
            $data["input"] = [
                "filename" => $inputFile,
                "filesize" => filesize($inputFile),
            ];
        }
        $data["started_at"] = date("c");
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
        if ($cache_results && isset($this->cache)) {
            $this->cache->set($key, $data);
        }

        /*
         * Array
        (
            [program] => ffmpeg
            [command] => ffmpeg -i "(...)/spx_media_analyzer/tests/sources/big_buck_bunny5.wav" 2>&1
            [input] => Array
                (
                    [filename] => (...)/spx_media_analyzer/tests/sources/big_buck_bunny5.wav
                    [filesize] => 882078
                )

            [started_at] => 2020-11-18T12:20:31+00:00
            [finished_at] => 2020-11-18T12:20:31+00:00
            [duration] => 0.141
            [return] => 1
            [output] => Array
                (
                    [0] => ffmpeg version 4.1.3-0york1~16.04 Copyright (c) 2000-2019 the FFmpeg developers
                    [1] =>   built with gcc 5.4.0 (Ubuntu 5.4.0-6ubuntu1~16.04.11) 20160609
                    [2] =>   configuration: --prefix=/usr --extra-version='0york1~16.04' --toolchain=hardened --libdir=/usr/lib/x86_64-linux-gnu --incdir=/usr/include/x86_64-linux-gnu --arch=amd64 --enable-gpl --disable-stripping --enable-avresample --disable-filter=resample --enable-avisynth --enable-gnutls --enable-ladspa --enable-libaom --enable-libass --enable-libbluray --enable-libbs2b --enable-libcaca --enable-libcdio --enable-libcodec2 --enable-libflite --enable-libfontconfig --enable-libfreetype --enable-libfribidi --enable-libgme --enable-libgsm --enable-libjack --enable-libmp3lame --enable-libmysofa --enable-libopenjpeg --enable-libopenmpt --enable-libopus --enable-libpulse --enable-librsvg --enable-librubberband --enable-libshine --enable-libsnappy --enable-libsoxr --enable-libspeex --enable-libssh --enable-libtheora --enable-libtwolame --enable-libvidstab --enable-libvorbis --enable-libvpx --enable-libwavpack --enable-libwebp --enable-libx265 --enable-libxml2 --enable-libxvid --enable-libzmq --enable-libzvbi --enable-lv2 --enable-omx --enable-openal --enable-opengl --enable-sdl2 --enable-nonfree --enable-libfdk-aac --enable-libdc1394 --enable-libdrm --enable-libiec61883 --enable-chromaprint --enable-frei0r --enable-libx264 --enable-shared
                    [3] =>   libavutil      56. 22.100 / 56. 22.100
                    [4] =>   libavcodec     58. 35.100 / 58. 35.100
                    [5] =>   libavformat    58. 20.100 / 58. 20.100
                    [6] =>   libavdevice    58.  5.100 / 58.  5.100
                    [7] =>   libavfilter     7. 40.101 /  7. 40.101
                    [8] =>   libavresample   4.  0.  0 /  4.  0.  0
                    [9] =>   libswscale      5.  3.100 /  5.  3.100
                    [10] =>   libswresample   3.  3.100 /  3.  3.100
                    [11] =>   libpostproc    55.  3.100 / 55.  3.100
                    [12] => Guessed Channel Layout for Input Stream #0.0 : stereo
                    [13] => Input #0, wav, from '(...)/spx_media_analyzer/tests/sources/big_buck_bunny5.wav':
                    [14] =>   Metadata:
                    [15] =>     encoder         : Lavf58.20.100
                    [16] =>   Duration: 00:00:05.00, bitrate: 1411 kb/s
                    [17] =>     Stream #0:0: Audio: pcm_s16le ([1][0][0][0] / 0x0001), 44100 Hz, stereo, s16, 1411 kb/s
                    [18] => At least one output file must be specified
                )

        )
         */
        return $data;
    }

    public function addQuotes(string $path, bool $dont_expand = false): string
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
