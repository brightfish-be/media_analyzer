<?php

namespace Brightfish\SpxMediaAnalyzer\Tests;

use Brightfish\SpxMediaAnalyzer\Cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    /**
     * @var Cache
     */
    private Cache $cache;
    private string $cacheFolder;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->cacheFolder = __DIR__ . DIRECTORY_SEPARATOR . ".tmp";
        $this->cache = new Cache($this->cacheFolder);
    }

    public function testSet()
    {
        $random = rand(1, 9999);
        $this->cache->set($random, "data");
        $this->assertTrue($this->cache->exists($random), "exists");
        $this->assertTrue($this->cache->get($random) === "data", "text get");
        $this->cache->set($random, ["key" => "val"]);
        $this->assertTrue($this->cache->exists($random), "exists");
        $this->assertTrue(is_array($this->cache->get($random)), "array get");
    }

    public function __destruct()
    {
        exec("cd \"$this->cacheFolder\" && rm -f *");
        rmdir($this->cacheFolder);
    }
}
