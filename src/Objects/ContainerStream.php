<?php


namespace Brightfish\SpxMediaAnalyzer\Objects;

class ContainerStream extends AbstractStream
{
    protected array $metadata;
    /**
     * @var mixed|null
     */
    private $nb_streams;
    /**
     * @var mixed|null
     */
    private $duration;
}
