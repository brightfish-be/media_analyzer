<?php


namespace Brightfish\MediaAnalyzer\Objects;

use Illuminate\Support\Str;
use JsonSerializable;

abstract class AbstractStream implements JsonSerializable
{
    protected array $metadata;

    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Return attribute's value through its getter.
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        // first try if a specific method was declared $this->getThisKey()
        if (method_exists($this, $method = 'get' . Str::studly($key))) {
            return $this->$method();
        }

        // then try the generic $this->metadata["this_key"]
        if (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }

        // give up and return null
        return null;
    }

    public function jsonSerialize()
    {
        return json_encode($this->metadata);
    }
}
