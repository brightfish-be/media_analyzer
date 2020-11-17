<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use \Exception;
use Brightfish\SpxMediaAnalyzer\Ffmpeg;
use PHPUnit\Framework\TestCase;

class FfmpegTest extends TestCase
{
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
        $this->ffmpeg->use_ffmpeg("/does/not/exist");
        $this->ffmpeg->use_ffmpeg("/usr/bin/ffmpeg");
    }

    public function testLog_to_folder()
    {
        $this->expectException(Exception::class);
        $this->ffmpeg->log_to_folder("/does/not/exist");
        $this->ffmpeg->log_to_folder(".");
    }

    public function testRun()
    {
        $exampleFolder = __DIR__;
        $this->ffmpeg->cache_to_folder($this->tempFolder);
        $this->ffmpeg->log_to_folder($this->tempFolder);
        $output = $this->ffmpeg->run_ffmpeg("$exampleFolder/sources/example.mp4", "-", [], true);
        $this->assertGreaterThan(0, count($output), "lines");
    }

    public function __destruct()
    {
        if (is_dir($this->tempFolder)) {
            exec("rm -fr \"$this->tempFolder\" ");
        }
    }
}
