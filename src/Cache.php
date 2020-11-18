<?php


namespace Brightfish\SpxMediaAnalyzer;

class Cache
{
    private string $prefix;
    private int $expiration;
    private string $folder;

    public function __construct(string $folder, int $expiration = 3600, string $prefix = 'cache')
    {
        if (! is_dir($folder)) {
            mkdir($folder);
        }
        $this->folder = $folder;
        $this->prefix = $prefix;
        $this->expiration = $expiration;
    }

    /**
     * @param string $key
     * @return mixed|string|string[]
     */
    public function get(string $key)
    {
        $cached = $this->cacheFile($key);
        if (! file_exists($cached)) {
            return "";
        }
        if (time() - filemtime($cached) > $this->expiration) {
            return "";
        }

        return json_decode(file_get_contents($cached), true);
    }

    public function exists(string $key): bool
    {
        $cached = $this->cacheFile($key);
        if (! file_exists($cached)) {
            return false;
        }
        if (time() - filemtime($cached) > $this->expiration) {
            return false;
        }

        return true;
    }

    public function set(string $key, $data): void
    {
        $cached = $this->cacheFile($key);
        file_put_contents($cached, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function cacheFile(string $key): string
    {
        return $this->folder . DIRECTORY_SEPARATOR . $this->prefix . "." . substr(sha1($key), 0, 20) . ".json";
    }
}
