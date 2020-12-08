<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use \Exception;
use Brightfish\SpxMediaAnalyzer\Ffprobe;
use PHPUnit\Framework\TestCase;

class FfprobeTest extends TestCase
{
    // class to run ffmpeg - doesn not interpret the stderr output
    private string $tempFolder;
    /**
     * @var Ffmpeg
     */
    private Ffprobe $ffmpeg;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->ffmpeg = new Ffprobe();
    }

    public function testUse_ffmpeg()
    {
        $this->expectException(Exception::class);
        $this->ffmpeg = new Ffprobe("/does/not/exist");

        $this->ffmpeg = new Ffprobe("/usr/bin/ffprobe");
    }

    public function testRun()
    {
        $exampleFolder = __DIR__;
        $output = $this->ffmpeg->probe("$exampleFolder/sources/example.mp4");
        print_r($output);
        $this->assertGreaterThan(0, count($output["result"]["streams"]), "stream count");
    }

}
