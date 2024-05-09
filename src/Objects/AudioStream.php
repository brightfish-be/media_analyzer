<?php


namespace Brightfish\MediaAnalyzer\Objects;

class AudioStream extends AbstractStream
{
    protected array $metadata;
    /**
     * @var mixed|null
     */
    protected $codec_name;
    /**
     * @var mixed|null
     */
    protected $sample_rate;
    /**
     * @var mixed|null
     */
    protected $bit_rate;
    /**
     * @var mixed|null
     */
    protected $bits_per_sample;
    /**
     * @var mixed|null
     */
    protected $channels;
    /**
     * @var mixed|null
     */
    protected $duration;
    /**
     * @var mixed|null
     */
    protected $duration_ts;
    /**
     * @var mixed|null
     */
    protected $max_bit_rate = 0;
    /**
     * @var mixed|null
     */
    protected $nb_frames;
    /**
     * @var mixed|null
     */
    protected $avg_frame_rate;
    /**
     * @var mixed|null
     */
    protected $channel_layout;
    /**
     * @var mixed|null
     */
    protected $codec_long_name;
    /**
     * @var mixed|null
     */
    protected $codec_tag;
    /**
     * @var mixed|null
     */
    protected $codec_tag_string;
    /**
     * @var mixed|null
     */
    protected $sample_fmt;
    /**
     * @var mixed|null
     */
    protected $time_base;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
    }
}
