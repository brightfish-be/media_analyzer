<?php


namespace Brightfish\MediaAnalyzer\Objects;

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
        // then try the generic $this->metadata["this_key"]
        if (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }

        if (isset($this->$key)) {
            return $this->$key;
        }

        // give up and return null
        return null;
    }

    public function jsonSerialize(): mixed
    {
        return json_encode($this->metadata);
    }
}
