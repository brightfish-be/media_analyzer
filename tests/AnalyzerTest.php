<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use Brightfish\SpxMediaAnalyzer\Analyzer;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Sabre\Cache\Memory;

class AnalyzerTest extends TestCase
{
    private string $exampleFolder;
    /**
     * @var Memory
     */
    private Memory $cache;
    /**
     * @var NullLogger
     */
    private NullLogger $logger;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->exampleFolder = __DIR__;
        $this->cache = new Memory();
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
        $this->assertEquals(5.022, $analyzer->container->duration, "mp4 file: container duration");
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
}
