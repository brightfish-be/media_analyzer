<?php
// Author: Peter Forret (cinemapub, p.forret@brightfish.be)
namespace Brightfish\SpxMediaAnalyzer;

use Exception;

class Analyzer
{
    protected $pathToImage = '';

    public function analyze(string $path)
    {
        if (! file_exists($path)) {
            throw new Exception("`{$path}` does not exist");
        }
        $ffmpeg = new Ffmpeg();
        $data = $ffmpeg->run_ffmpeg($path);
        $lines = $data["output"];
        /*
        "ffmpeg version 4.1.3-0york1~16.04 Copyright (c) 2000-2019 the FFmpeg developers",
        "  built with gcc 5.4.0 (Ubuntu 5.4.0-6ubuntu1~16.04.11) 20160609",
        "  configuration: --prefix=\/usr --extra-version='0york1~16.04' --toolchain=hardened --libdir=\/usr\/lib\/x86_64-linux-gnu --incdir=\/usr\/include\/x86_64-linux-gnu --arch=amd64 --enable-gpl --disable-stripping --enable-avresample --disable-filter=resample --enable-avisynth --enable-gnutls --enable-ladspa --enable-libaom --enable-libass --enable-libbluray --enable-libbs2b --enable-libcaca --enable-libcdio --enable-libcodec2 --enable-libflite --enable-libfontconfig --enable-libfreetype --enable-libfribidi --enable-libgme --enable-libgsm --enable-libjack --enable-libmp3lame --enable-libmysofa --enable-libopenjpeg --enable-libopenmpt --enable-libopus --enable-libpulse --enable-librsvg --enable-librubberband --enable-libshine --enable-libsnappy --enable-libsoxr --enable-libspeex --enable-libssh --enable-libtheora --enable-libtwolame --enable-libvidstab --enable-libvorbis --enable-libvpx --enable-libwavpack --enable-libwebp --enable-libx265 --enable-libxml2 --enable-libxvid --enable-libzmq --enable-libzvbi --enable-lv2 --enable-omx --enable-openal --enable-opengl --enable-sdl2 --enable-nonfree --enable-libfdk-aac --enable-libdc1394 --enable-libdrm --enable-libiec61883 --enable-chromaprint --enable-frei0r --enable-libx264 --enable-shared",
        "  libavutil      56. 22.100 \/ 56. 22.100",
        "  libavcodec     58. 35.100 \/ 58. 35.100",
        "  libavformat    58. 20.100 \/ 58. 20.100",
        "  libavdevice    58.  5.100 \/ 58.  5.100",
        "  libavfilter     7. 40.101 \/  7. 40.101",
        "  libavresample   4.  0.  0 \/  4.  0.  0",
        "  libswscale      5.  3.100 \/  5.  3.100",
        "  libswresample   3.  3.100 \/  3.  3.100",
        "  libpostproc    55.  3.100 \/ 55.  3.100",
        "Input #0, mov,mp4,m4a,3gp,3g2,mj2, from '\/mnt\/c\/Users\/forretp\/Code\/github\/spx_media_analyzer\/tests\/sources\/example.mp4':",
        "  Metadata:",
        "    major_brand     : mp42",
        "    minor_version   : 0",
        "    compatible_brands: mp42isomavc1",
        "    creation_time   : 2010-09-23T00:37:25.000000Z",
        "    encoder         : HandBrake 0.9.4 2009112300",
        "  Duration: 00:00:06.31, start: 0.000000, bitrate: 252 kb\/s",
        "    Stream #0:0(und): Video: h264 (Main) (avc1 \/ 0x31637661), yuv420p(tv, smpte170m\/smpte170m\/bt709), 200x110 [SAR 1:1 DAR 20:11], 74 kb\/s, 23.72 fps, 24 tbr, 90k tbn, 48 tbc (default)",
        "    Metadata:",
        "      creation_time   : 2010-09-23T00:37:25.000000Z",
        "      encoder         : JVT\/AVC Coding",
        "    Stream #0:1(und): Audio: aac (LC) (mp4a \/ 0x6134706D), 48000 Hz, mono, fltp, 171 kb\/s (default)",
        "    Metadata:",
        "      creation_time   : 2010-09-23T00:37:25.000000Z",
        "At least one output file must be specified"

         */
        $result = [];
        $result["ffmpeg"]["version"] = str_replace([" the FFmpeg developers","Copyright "], "", $lines[0]);
        $result["file"]["name"] = basename($path);
        $result["file"]["extension"] = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $result["file"]["folder"] = pathinfo($path, PATHINFO_DIRNAME);
        $result["file"]["size"] = filesize($path);
        $result["file"]["mtime"] = filemtime($path);
        $result["file"]["modification"] = date("c", filemtime($path));
        $result["file"]["days"] = round((time() - filemtime($path)) / (3600 * 24), 2);
        $result["stream"]["count"] = 0;
        $audiostreams = [];
        foreach ($lines as $line) {
            if (strstr($line, "Duration:") && ! strstr($line, "N/A")) {
                $data = $this->find($line, "|Duration:(.*)|");
                $result["duration"]["raw"] = trim($data);
                $result["duration"]["length"] = $this->find($data, "|(\d\d:\d\d:\d\d\.\d\d)|");
                list($hr, $mn, $sc) = explode(":", $result["duration"]["length"], 3);
                $secs = ($hr * 3600) + ($mn * 60) + (double)$sc;
                $result["duration"]["seconds"] = round($secs, 2);
                $result["file"]["bps"] = round(8 * $result["file"]["size"] / $secs);
            }
            // Stream #0:0(und): Audio: aac (LC) (mp4a / 0x6134706D), 48000 Hz, mono, fltp, 171 kb/s (default)
            // Stream #0:0(und): Video: h264 (Main) (avc1 / 0x31637661), yuv420p(tv, smpte170m/smpte170m/bt709), 200x110 [SAR 1:1 DAR 20:11], 74 kb/s, 23.72 fps, 24 tbr, 90k tbn, 48 tbc (default)
            // Stream #0:0: Video: gif, bgra, 32x32, 14.25 fps, 14.29 tbr, 100 tbn, 100 tbc
            // Stream #0:0: Video: png, pal8(pc), 27x27, 25 tbr, 25 tbn, 25 tbc
            if (strstr($line, "Stream #")) {
                $result["stream"]["count"]++;
                if (strstr($line, "Video:")) {
                    $data = $this->find($line, "|Video:(.*)|");
                    $result["video"]["raw"] = trim($data);
                    $data = strtolower($data);
                    $result["video"]["size"] = $this->find($data, "|(\d\d+x\d\d+)|");
                    if ($result["video"]["size"]) {
                        list($w, $h) = explode("x", $result["video"]["size"]);
                        $result["video"]["width"] = (int)$w;
                        $result["video"]["height"] = (int)$h;
                        $result["video"]["pixels"] = $w * $h;
                        $result["video"]["aspect"] = round($w / $h, 2);
                        switch ($result["video"]["aspect"]) {
                            case 1.78:  $result["video"]["aspect_type"] = "HD";

break;
                            case 1.90:  $result["video"]["aspect_type"] = "DCP";

break;
                            case 1.85:  $result["video"]["aspect_type"] = "FLAT";

break;
                            case 2.35:  $result["video"]["aspect_type"] = "SCOPE";

break;
                            case 2.39:  $result["video"]["aspect_type"] = "SCOPE";

break;
                            case 1.33:  $result["video"]["aspect_type"] = "TV";

break;
                        }
                    }
                    $result["video"]["fps"] = (double)$this->find($data, "|([\d\.]+) fps|");
                    if (! $result["video"]["fps"]) {
                        $result["video"]["fps"] = $this->find($data, "|([\d\.]+) tbr|");
                    }
                    if ($result["video"]["fps"] && isset($result["duration"]) && $result["duration"]["seconds"]) {
                        $result["video"]["frames"] = round($result["duration"]["seconds"] * $result["video"]["fps"]);
                    }
                    $result["video"]["kbps"] = (double)$this->find($data, "|(\d+) kb/s|");
                    if (isset($result["file"]["bps"]) && ! $result["video"]["kbps"]) {
                        $result["video"]["kbps"] = round($result["file"]["bps"] / 1000);
                    }
                    if (isset($result["video"]["kbps"])) {
                        $result["video"]["bps"] = $result["video"]["kbps"] * 1000;
                    }
                    list($codec, $rest) = explode(",", $data);
                    $result["video"]["codec"] = trim(preg_replace("#(\(.*\))#", "", $codec));
                    if (isset($result["video"]["fps"]) && isset($result["video"]["pixels"])) {
                        $uncompressed = $result["video"]["pixels"] * 24 * $result["video"]["fps"];
                        $result["video"]["compression"] = round($result["video"]["bps"] / $uncompressed, 3);
                        $result["video"]["compression_percent"] = round(100 * $result["video"]["bps"] / $uncompressed, 1) . "%";
                    }
                }
                if (strstr($line, "Audio:")) {
                    // pcm_s24le ([1][0][0][0] / 0x0001), 48000 hz, 5.1, s32 (24 bit), 6912 kb/s
                    $data = $this->find($line, "|Audio:(.*)|");
                    $result["audio"]["raw"] = trim($data);
                    $data = strtolower($data);
                    if (! isset($result["audio"]["channels"])) {
                        $result["audio"]["channels"] = 0;
                    }
                    $achannels = $this->find($data, "|(\d) channels|");
                    if (! $achannels and strstr($data, "stereo")) {
                        $achannels = 2;
                    }
                    if (! $achannels and strstr($data, "5.1")) {
                        $achannels = 6;
                    }
                    if (! $achannels) {
                        $achannels = 1;
                    }
                    if (! isset($audiostreams[$achannels])) {
                        $audiostreams[$achannels] = 1;
                    } else {
                        $audiostreams[$achannels]++;
                    }
                    $result["audio"]["channels"] += $achannels;
                    $result["audio"]["kbps"] = $this->find($data, "|(\d+) kb/s|");
                    $result["audio"]["bps"] = 1000 * $result["audio"]["kbps"];
                    if ($achannels and $result["audio"]["kbps"]) {
                        $cbps = $result["audio"]["kbps"] / $achannels;
                        $result["audio"]["kbps_channel"] = round($cbps, 1);
                    };
                    $result["audio"]["bits"] = $this->find($data, "|\((\d+) bit\)|");
                    if (! $result["audio"]["bits"]) {
                        $result["audio"]["bits"] = 16;
                    }
                    $result["audio"]["hertz"] = $this->find($data, "|(\d\d\d\d\d) hz|");
                    if (! $result["audio"]["hertz"]) {
                        $result["audio"]["hertz"] = 48000;
                    }
                    $uncomp = $result["audio"]["hertz"] * $result["audio"]["bits"] * $result["audio"]["channels"];
                    $result["audio"]["compression"] = round($result["audio"]["bps"] / $uncomp, 3);
                    $result["audio"]["compression_percent"] = round(100 * $result["audio"]["bps"] / $uncomp) . "%";
                    switch (true) {
                        case($result["audio"]["hertz"] < 44000): $result["audio"]["quality"] = "low";

break;
                        case($result["audio"]["bits"] < 16): $result["audio"]["quality"] = "low";

break;
                        case($result["audio"]["compression"] < 0.1): $result["audio"]["quality"] = "low";

break;
                        default:          $result["audio"]["quality"] = "high";
                    }
                    list($codec, $rest) = explode(",", $data);
                    $result["audio"]["codec"] = trim(preg_replace("#(\(.*\))#", "", $codec));
                }
            }
        }
        print_r($result);

        return $result;
    }


    public function find($haystack, $pattern)
    {
        $nb = preg_match($pattern, $haystack, $matches);
        if ($nb) {
            return $matches[1];
        }

        return "";
    }
}
