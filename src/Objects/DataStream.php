<?php


namespace Brightfish\MediaAnalyzer\Objects;

class DataStream extends AbstractStream
{
    protected array $metadata;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
    }
}
