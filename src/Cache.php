<?php


namespace Brightfish\SpxMediaAnalyzer;


class Cache
{


    /**
     * @var string
     */
    private $prefix;
    /**
     * @var int
     */
    private $expiration;
    /**
     * @var string
     */
    private $folder;

    public function __construct(string $folder, int $expiration=3600, string $prefix='cache')
    {
        if(!is_dir($folder)){
            mkdir($folder);
        }
        $this->folder=$folder;
        $this->prefix=$prefix;
        $this->expiration=$expiration;
    }

    public function get($key)
    {
        $cached=$this->cacheFile($key);
        if(!file_exists($cached))   return false;
        if(time()-filemtime($cached) > $this->expiration) return false;
        return json_decode(file_get_contents($cached),true);
    }

    public function exists($key)
    {
        $cached=$this->cacheFile($key);
        if(!file_exists($cached))   return false;
        if(time()-filemtime($cached) > $this->expiration) return false;
        return true;
    }

    public function set($key,$data){
        $cached=$this->cacheFile($key);
        file_put_contents($cached,json_encode($data,JSON_PRETTY_PRINT));
    }

    private function cacheFile($key){
        return $this->folder . DIRECTORY_SEPARATOR . $this->prefix . "." . substr(sha1($key),0,20) . ".json";
    }
}