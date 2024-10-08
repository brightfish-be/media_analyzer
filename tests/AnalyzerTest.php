<?php

namespace Brightfish\MediaAnalyzer\Tests;

use Brightfish\MediaAnalyzer\Analyzer;
use Brightfish\MediaAnalyzer\Helpers\InMemoryCache;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class AnalyzerTest extends TestCase
{
    private string $exampleFolder;

    private InMemoryCache $cache;

    private NullLogger $logger;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->exampleFolder = __DIR__;
        $this->cache = new InMemoryCache();
        $this->logger = new NullLogger();
    }

    public function testMp4()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);

        $analyzer->meta("$this->exampleFolder/sources/big_buck_bunny5.mp4");

        $this->assertEquals(24, $analyzer->video->fps, "mp4 file: fps");
        $this->assertEquals(5, $analyzer->video->duration, "mp4 file: duration");

        $this->assertEquals("aac", $analyzer->audio->codec_name, "mp4 file: audio codec");
        $this->assertEquals(48000, $analyzer->audio->sample_rate, "mp4 file: audio sample rate");

        $this->assertEquals(2, $analyzer->container->nb_streams, "mp4 file: nb streams");

        // todo this seems to be different according to OS's
        // sometimes 5.022, sometimes 5.000000
        // implement desired rounding configuration variable?
        $this->assertEquals(5.0, round($analyzer->container->duration), "mp4 file: container duration");
    }

    public function testMp3()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);

        $analyzer->meta("$this->exampleFolder/sources/big_buck_bunny.mp3");

        $this->assertEquals("mp3", $analyzer->audio->codec_name, "mp3 file: audio codec");
        $this->assertEquals(44100, $analyzer->audio->sample_rate, "mp3 file: audio sample rate");

        $this->assertEquals(1, $analyzer->container->nb_streams, "mp3 file: nb streams");
        $this->assertEquals(29.805714, $analyzer->container->duration, "mp3 file: container duration");
    }

    public function testDcpVideo()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);
        $analyzer->meta("$this->exampleFolder/sources/dcp_video.mxf");

        $this->assertEquals(24, $analyzer->video->fps, "mxf file: fps");
        $this->assertEquals(2, $analyzer->video->duration, "mxf file: duration");

        $this->assertEquals(1, $analyzer->container->nb_streams, "mxf file: nb streams");
        $this->assertEquals(2, $analyzer->container->duration, "mxf file: container duration");
    }

    public function testDcpWav()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);
        $analyzer->meta("$this->exampleFolder/sources/dcp_audio.wav");

        $this->assertEquals("pcm_s24le", $analyzer->audio->codec_name, "wav file: audio codec");
        $this->assertEquals(48000, $analyzer->audio->sample_rate, "wav file: audio sample rate");

        $this->assertEquals(1, $analyzer->container->nb_streams, "wav file: nb streams");
        $this->assertEquals(2, $analyzer->container->duration, "wav file: container duration");
    }

    public function testWav()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);
        $analyzer->meta("$this->exampleFolder/sources/big_buck_bunny5.wav");

        $this->assertEquals("pcm_s16le", $analyzer->audio->codec_name, "wav file: audio codec");
        $this->assertEquals(44100, $analyzer->audio->sample_rate, "wav file: audio sample rate");

        $this->assertEquals(1, $analyzer->container->nb_streams, "wav file: nb streams");
        $this->assertEquals(5, $analyzer->container->duration, "wav file: container duration");
    }

    public function testContainerProperties()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);
        $analyzer->meta("$this->exampleFolder/sources/big_buck_bunny5.mp4");
        $this->assertIsNumeric($analyzer->container->bit_rate);
        $this->assertNotEmpty($analyzer->container->duration);
        $this->assertNotEmpty($analyzer->container->filename);
        $this->assertNotEmpty($analyzer->container->format_long_name);
        $this->assertNotEmpty($analyzer->container->format_name);
        $this->assertIsNumeric($analyzer->container->nb_programs);
        $this->assertIsNumeric($analyzer->container->nb_streams);
        $this->assertIsNumeric($analyzer->container->size);
        $this->assertIsNumeric($analyzer->container->start_time);
        $this->assertNotEmpty($analyzer->container->tags);
    }

    public function testVideoProperties()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);
        $analyzer->meta("$this->exampleFolder/sources/big_buck_bunny5.mp4");
        //print_r($analyzer->video);

        $this->assertIsNumeric($analyzer->video->aspect_ratio_number);
        $this->assertIsNumeric($analyzer->video->coded_height);
        $this->assertIsNumeric($analyzer->video->coded_width);
        $this->assertIsNumeric($analyzer->video->fps);
        $this->assertIsNumeric($analyzer->video->height);
        $this->assertIsNumeric($analyzer->video->max_bit_rate);
        $this->assertIsNumeric($analyzer->video->nb_frames);
        $this->assertIsNumeric($analyzer->video->width);
        $this->assertIsNumeric($analyzer->video->duration);

        $this->assertNotEmpty($analyzer->video->aspect_ratio);
        $this->assertNotEmpty($analyzer->video->aspect_type);
        $this->assertNotEmpty($analyzer->video->avg_frame_rate);
        $this->assertNotEmpty($analyzer->video->chroma_location);
        $this->assertNotEmpty($analyzer->video->codec_long_name);
        $this->assertNotEmpty($analyzer->video->codec_name);
        $this->assertNotEmpty($analyzer->video->codec_tag);
        $this->assertNotEmpty($analyzer->video->codec_tag_string);
        // todo $this->assertNotEmpty($analyzer->video->codec_time_base);
        $this->assertNotEmpty($analyzer->video->codec_type);
        $this->assertNotEmpty($analyzer->video->orientation);
        $this->assertNotEmpty($analyzer->video->pix_fmt);
        $this->assertNotEmpty($analyzer->video->display_aspect_ratio);
    }

    public function testAudioProperties()
    {
        $analyzer = new Analyzer("", $this->logger, $this->cache);
        $analyzer->meta("$this->exampleFolder/sources/big_buck_bunny5.mp4");

        $this->assertIsNumeric($analyzer->audio->bit_rate);
        $this->assertIsNumeric($analyzer->audio->bits_per_sample);
        $this->assertIsNumeric($analyzer->audio->channels);
        $this->assertIsNumeric($analyzer->audio->duration);
        $this->assertIsNumeric($analyzer->audio->duration_ts);
        $this->assertIsNumeric($analyzer->audio->max_bit_rate);
        $this->assertIsNumeric($analyzer->audio->nb_frames);
        $this->assertIsNumeric($analyzer->audio->sample_rate);
        $this->assertNotEmpty($analyzer->audio->avg_frame_rate);
        $this->assertNotEmpty($analyzer->audio->channel_layout);
        $this->assertNotEmpty($analyzer->audio->codec_long_name);
        $this->assertNotEmpty($analyzer->audio->codec_name);
        $this->assertNotEmpty($analyzer->audio->codec_tag);
        $this->assertNotEmpty($analyzer->audio->codec_tag_string);
        $this->assertNotEmpty($analyzer->audio->sample_fmt);
        $this->assertNotEmpty($analyzer->audio->time_base);
    }
}
