<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use Brightfish\SpxMediaAnalyzer\Analyzer;
use PHPUnit\Framework\TestCase;

class AnalyzerTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function testMetaArray()
    {
        $exampleFolder = __DIR__;
        $analyzer = new Analyzer();

        $analysis = $analyzer->meta("$exampleFolder/sources/example.gif");
        $this->assertTrue($analysis["file"]["size"] === 8476, "gif file: file size");
        $this->assertTrue($analysis["video"]["size"] === "32x32", "gif file: video size");

        $analysis = $analyzer->meta("$exampleFolder/sources/example.mp4");
        $this->assertEquals("h264", $analysis["video"]["codec"], "mp4 file: video codec");
        $this->assertEquals(24, $analysis["video"]["fps"], "mp4 file: video fps");

        $analysis = $analyzer->meta("$exampleFolder/sources/example.png");
        $this->assertEquals(1, $analysis["image"]["aspect_ratio"], "png file: aspect ratio");
        $this->assertEquals(729, $analysis["image"]["pixels"], "png file: video pixels");

        $analysis = $analyzer->meta("$exampleFolder/sources/big_buck_bunny.m4a");
        $this->assertEquals("aac", $analysis["audio"]["codec"], "m4a file: codec");
        $this->assertEquals(2, $analysis["audio"]["channels"], "m4a file: audio channels");

        $analysis = $analyzer->meta("$exampleFolder/sources/big_buck_bunny.mp3");
        $this->assertEquals("mp3", $analysis["audio"]["codec"], "mp3 file: codec");
        $this->assertEquals(2, $analysis["audio"]["channels"], "mp3 file: audio channels");

        $analysis = $analyzer->meta("$exampleFolder/sources/big_buck_bunny5.mp4");
        $this->assertEquals("aac", $analysis["audio"]["codec"], "mp4 file: codec");
        $this->assertEquals("854x480", $analysis["video"]["size"], "mp4 file: resolution");

        $analysis = $analyzer->meta("$exampleFolder/sources/big_buck_bunny5.wav");
        $this->assertEquals("pcm_s16le", $analysis["audio"]["codec"], "wav file: codec");
        $this->assertEquals(1411, $analysis["audio"]["kbps"], "wav file: audio kbps");

        $analysis = $analyzer->meta("$exampleFolder/sources/video.mov");
        $this->assertEquals("pcm_s16le", $analysis["audio"]["codec"], "mov file: codec");
        $this->assertEquals("yuv422p10le", $analysis["video"]["chroma"], "mov file: video chroma");

        $analysis = $analyzer->meta("$exampleFolder/sources/dcp_video.mxf");
        $this->assertEquals("jpeg2000", $analysis["video"]["codec"], "mxf file: codec");
        $this->assertEquals("xyz12le", $analysis["video"]["chroma"], "mxf file: video chroma");
        $this->assertEquals(24, $analysis["video"]["fps"], "mxf file: fps");
    }

    public function testMetaObjects()
    {
        $exampleFolder = __DIR__;
        $analyzer = new Analyzer();

        $analyzer->meta("$exampleFolder/sources/big_buck_bunny5.mp4");
        $this->assertEquals(24, $analyzer->video->details["fps"], "mp4 file: video fps");
        $this->assertEquals(130000, $analyzer->audio->details["bps"], "mp4 file: audio bps");

        $analyzer->meta("$exampleFolder/sources/example.png");
        $this->assertEquals(27, $analyzer->image->details["width"], "png file: image width");
        $this->assertEquals(27, $analyzer->image->details["height"], "png file: image height");

    }
}
