<?php


namespace Brightfish\MediaAnalyzer\Objects;

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
    /**
     * @var mixed|null
     */
    private $bit_rate;
    /**
     * @var mixed|null
     */
    private $bits_per_sample;
    /**
     * @var mixed|null
     */
    private $channels;
    /**
     * @var mixed|null
     */
    private $duration;
    /**
     * @var mixed|null
     */
    private $duration_ts;
    /**
     * @var mixed|null
     */
    private $max_bit_rate;
    /**
     * @var mixed|null
     */
    private $nb_frames;
    /**
     * @var mixed|null
     */
    private $avg_frame_rate;
    /**
     * @var mixed|null
     */
    private $channel_layout;
    /**
     * @var mixed|null
     */
    private $codec_long_name;
    /**
     * @var mixed|null
     */
    private $codec_tag;
    /**
     * @var mixed|null
     */
    private $codec_tag_string;
    /**
     * @var mixed|null
     */
    private $sample_fmt;
    /**
     * @var mixed|null
     */
    private $time_base;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
    }
}
