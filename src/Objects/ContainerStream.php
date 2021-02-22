<?php


namespace Brightfish\MediaAnalyzer\Objects;

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
    /**
     * @var mixed|null
     */
    private $bit_rate;
    /**
     * @var mixed|null
     */
    private $filename;
    /**
     * @var mixed|null
     */
    private $format_long_name;
    /**
     * @var mixed|null
     */
    private $format_name;
    /**
     * @var mixed|null
     */
    private $nb_programs;
    /**
     * @var mixed|null
     */
    private $size;
    /**
     * @var mixed|null
     */
    private $start_time;
    /**
     * @var mixed|null
     */
    private $tags;
}
