<?php
// Author: Peter Forret ( p.forret@brightfish.be)
namespace Brightfish\MediaAnalyzer;

use Brightfish\MediaAnalyzer\Objects\AudioStream;
use Brightfish\MediaAnalyzer\Objects\ContainerStream;
use Brightfish\MediaAnalyzer\Objects\DataStream;
use Brightfish\MediaAnalyzer\Objects\ImageStream;
use Brightfish\MediaAnalyzer\Objects\VideoStream;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Analyzer
{
    private Ffprobe $probe;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    public ContainerStream $container;
    public AudioStream $audio;
    public VideoStream $video;
    public DataStream $data;
    public ImageStream $image;
    private bool $useLogger = false;
    private bool $useCache = false;

    public function __construct(string $binary = "", LoggerInterface $logger = null, CacheInterface $cache = null)
    {
        $this->probe = new Ffprobe($binary);

        if ($logger) {
            $this->logger = $logger;
            $this->useLogger = true;
        }
        if ($cache) {
            $this->cache = $cache;
            $this->useCache = true;
        }
    }

    public function meta(string $path, $cacheTime = 3600): array
    {
        if (! file_exists($path)) {
            throw new Exception("Media file [$path] does not exist");
        }
        $key = "probe-" . $path;

        if ($this->useCache && $this->cache->has($key)) {
            if ($data = $this->cache->get($key)) {
                return $this->parseRawData($data);
            }
        }

        $data = $this->probe->probe($path);

        if (! $data || ! isset($data["result"])) {
            return [];
        }

        $meta = $this->parseRawData($data);

        if ($this->useCache) {
            # Cache the raw data.
            $this->cache($key, $data, $cacheTime);
        }

        return $meta;
    }

    /**
     * @param string $key
     * @param array $raw
     * @param int $ttl
     */
    protected function cache(string $key, array $raw, int $ttl): void
    {
        $this->cache->set($key, $raw, $ttl);
        $this->logger->info("Cached saved for $key");
    }

    /**
     * @param array $raw
     * @return array[]
     */
    protected function parseRawData(array $raw): array
    {
        $meta = [
            'streams' => []
        ];

        $this->container = new ContainerStream($raw["result"]["format"]);

        foreach ($raw["result"]["streams"] as $stream) {
            switch ($stream["type"]) {
                case "audio":
                    $this->audio = $meta["streams"][] = new AudioStream($stream);

                    break;

                case "video":
                    $this->video = $meta["streams"][] = new VideoStream($stream);

                    break;

                case "image":
                    $this->image = $meta["streams"][] = new ImageStream($stream);

                    break;

                case "data":
                    $this->data = $meta["streams"][] = new DataStream($stream);

                    break;

            }
        }

        return $meta;
    }

    /*
    [command] => Array
        (
            [binary] => Array
                (
                    [_raw] => ffprobe version 4.3.1-0york0~16.04 Copyright (c) 2007-2020 the FFmpeg developers
                    [gcc_version] => 5.4.0
                    [version] => 4.3.1
                    [year] => 2020
                    [file] => ffprobe
                    [path] => /usr/bin/ffprobe
                    [command] => 'ffprobe' -version
                )

            [full] => 'ffprobe' -v quiet -print_format json -show_format -show_streams -i '/mnt/c/Users/forretp/Code/github/spx_media_analyzer/tests/sources/example.mp4' 2>&1
            [input] => Array
                (
                    [filename] => /mnt/c/Users/forretp/Code/github/spx_media_analyzer/tests/sources/example.mp4
                    [filesize] => 199160
                    [modified] => 2020-11-16T08:35:25+00:00
                    [changed] => 2020-11-16T08:47:25+00:00
                )

            [started_at] => 2020-12-08T14:32:30+00:00
            [finished_at] => 2020-12-08T14:32:31+00:00
            [duration] => 0.463
            [return] => 0
        )

    [result] => Array
        (
            [format] => Array
                (
                    [bit_rate] => 252314
                    [duration] => 6.314667
                    [filename] => /mnt/c/Users/forretp/Code/github/spx_media_analyzer/tests/sources/example.mp4
                    [format_long_name] => QuickTime / MOV
                    [format_name] => mov,mp4,m4a,3gp,3g2,mj2
                    [nb_programs] => 0
                    [nb_streams] => 2
                    [probe_score] => 100
                    [size] => 199160
                    [start_time] => 0.000000
                    [tags] => Array
                        (
                            [major_brand] => mp42
                            [minor_version] => 0
                            [compatible_brands] => mp42isomavc1
                            [creation_time] => 2010-09-23T00:37:25.000000Z
                            [encoder] => HandBrake 0.9.4 2009112300
                        )

                )

            [streams] => Array
                (
                    [0] => Array
                        (
                            [index] => 0
                            [codec_name] => h264
                            [codec_long_name] => H.264 / AVC / MPEG-4 AVC / MPEG-4 part 10
                            [profile] => Main
                            [codec_type] => video
                            [codec_time_base] => 3141/149000
                            [codec_tag_string] => avc1
                            [codec_tag] => 0x31637661
                            [width] => 200
                            [height] => 110
                            [coded_width] => 208
                            [coded_height] => 112
                            [closed_captions] => 0
                            [has_b_frames] => 1
                            [sample_aspect_ratio] => 1:1
                            [display_aspect_ratio] => 20:11
                            [pix_fmt] => yuv420p
                            [level] => 11
                            [color_range] => tv
                            [color_space] => smpte170m
                            [color_transfer] => bt709
                            [color_primaries] => smpte170m
                            [chroma_location] => left
                            [refs] => 1
                            [is_avc] => true
                            [nal_length_size] => 4
                            [r_frame_rate] => 24/1
                            [avg_frame_rate] => 74500/3141
                            [time_base] => 1/90000
                            [start_pts] => 0
                            [start_time] => 0.000000
                            [duration_ts] => 565380
                            [duration] => 6.282000
                            [bit_rate] => 74475
                            [bits_per_raw_sample] => 8
                            [nb_frames] => 149
                            [disposition] => Array
                                (
                                    [default] => 1
                                    [dub] => 0
                                    [original] => 0
                                    [comment] => 0
                                    [lyrics] => 0
                                    [karaoke] => 0
                                    [forced] => 0
                                    [hearing_impaired] => 0
                                    [visual_impaired] => 0
                                    [clean_effects] => 0
                                    [attached_pic] => 0
                                    [timed_thumbnails] => 0
                                )

                            [tags] => Array
                                (
                                    [creation_time] => 2010-09-23T00:37:25.000000Z
                                    [language] => und
                                    [encoder] => JVT/AVC Coding
                                )

                        )

                    [1] => Array
                        (
                            [index] => 1
                            [codec_name] => aac
                            [codec_long_name] => AAC (Advanced Audio Coding)
                            [profile] => LC
                            [codec_type] => audio
                            [codec_time_base] => 1/48000
                            [codec_tag_string] => mp4a
                            [codec_tag] => 0x6134706d
                            [sample_fmt] => fltp
                            [sample_rate] => 48000
                            [channels] => 1
                            [channel_layout] => mono
                            [bits_per_sample] => 0
                            [r_frame_rate] => 0/0
                            [avg_frame_rate] => 0/0
                            [time_base] => 1/48000
                            [start_pts] => 0
                            [start_time] => 0.000000
                            [duration_ts] => 303104
                            [duration] => 6.314667
                            [bit_rate] => 171029
                            [max_bit_rate] => 201736
                            [nb_frames] => 296
                            [disposition] => Array
                                (
                                    [default] => 1
                                    [dub] => 0
                                    (...)
                                    [timed_thumbnails] => 0
                                )

                            [tags] => Array
                                (
                                    [creation_time] => 2010-09-23T00:37:25.000000Z
                                    [language] => und
                                )
                        )
                )
        )
)
*/
}
