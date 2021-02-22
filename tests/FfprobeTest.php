<?php

namespace Brightfish\MediaAnalyzer\Tests;

use \Exception;
use Brightfish\MediaAnalyzer\Ffprobe;
use PHPUnit\Framework\TestCase;

class FfprobeTest extends TestCase
{
    // class to run ffmpeg - doesn not interpret the stderr output
    private string $tempFolder;
    /**
     * @var Ffmpeg
     */
    private Ffprobe $probe;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->probe = new Ffprobe();
    }

    public function testBinary()
    {
        $this->expectException(Exception::class);
        $this->probe = new Ffprobe("/does/not/exist");

        $this->probe = new Ffprobe("/usr/bin/ffprobe");
    }

    public function testProbe()
    {
        $exampleFolder = __DIR__;
        $output = $this->probe->probe("$exampleFolder/sources/example.jpg");
        $this->assertGreaterThan(0, count($output["result"]["streams"]), "stream count");
    }
}
