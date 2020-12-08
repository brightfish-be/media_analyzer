<?php


namespace Brightfish\SpxMediaAnalyzer\Objects;

class AudioStream extends AbstractStream
{
    protected array $metadata;
    /**
     * @var mixed|null
     */
    private $codec_name;
    /**
     * @var mixed|null
     */
    private $sample_rate;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
    }
}
