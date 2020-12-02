<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use \Exception;
use Brightfish\SpxMediaAnalyzer\Ffmpeg;
use PHPUnit\Framework\TestCase;

class FfmpegTest extends TestCase
{
    // class to run ffmpeg - doesn not interpret the stderr output
    private string $tempFolder;
    /**
     * @var Ffmpeg
     */
    private Ffmpeg $ffmpeg;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->ffmpeg = new ffmpeg();
        $this->tempFolder = __DIR__ . "/" . "temp";
        if (! is_dir($this->tempFolder)) {
            mkdir($this->tempFolder);
        }
    }

    public function testUse_ffmpeg()
    {
        $this->expectException(Exception::class);
        $this->ffmpeg->useBinary("/does/not/exist");
        $this->ffmpeg->useBinary("/usr/bin/ffmpeg");
    }

    public function testRun()
    {
        $exampleFolder = __DIR__;
        $output = $this->ffmpeg->run("$exampleFolder/sources/example.mp4", "-", [], true);
        $this->assertGreaterThan(0, count($output), "lines");
    }

    public function __destruct()
    {
        if (is_dir($this->tempFolder)) {
            exec("rm -fr \"$this->tempFolder\" ");
        }
    }
}
