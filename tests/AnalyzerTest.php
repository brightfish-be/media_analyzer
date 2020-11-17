<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use Brightfish\SpxMediaAnalyzer\analyzer;
use PHPUnit\Framework\TestCase;

class analyzerTest extends TestCase
{

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function testAnalysis(){
        $exampleFolder=__DIR__;
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/example.gif");
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/example.mp4");
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/example.png");
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/big_buck_bunny.m4a");
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/big_buck_bunny.mp3");
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/big_buck_bunny5.mp4");
        $analysis=(new Analyzer)->analyze("$exampleFolder/sources/big_buck_bunny5.wav");
        $this->assertGreaterThan(0,$analysis,"fake");
    }
}
