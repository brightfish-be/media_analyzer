<?php


namespace Brightfish\SpxMediaAnalyzer\Objects;

class VideoStream extends AbstractStream
{
    protected array $metadata;
    protected float $fps;
    protected float $duration;
    protected float $aspect_ratio_number;
    protected int $coded_height;
    protected int $coded_width;
    protected int $height;
    protected int $width;
    protected float $max_bit_rate;
    protected int $nb_frames;
    private $aspect_ratio;
    /**
     * @var mixed|null
     */
    private $aspect_type;
    /**
     * @var mixed|null
     */
    private $avg_frame_rate;
    /**
     * @var mixed|null
     */
    private $chroma_location;
    /**
     * @var mixed|null
     */
    private string $codec_long_name;
    private string $codec_name;
    private $codec_tag;
    /**
     * @var mixed|null
     */
    private $codec_tag_string;
    /**
     * @var mixed|null
     */
    private $codec_time_base;
    /**
     * @var mixed|null
     */
    private $codec_type;
    /**
     * @var mixed|null
     */
    private $orientation;
    /**
     * @var mixed|null
     */
    private string $pix_fmt;
    private $display_aspect_ratio;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
        if (isset($this->metadata["width"]) && $this->metadata["height"]) {
            $this->metadata["aspect_ratio_number"] = round($this->metadata["width"] / $this->metadata["height"], 2);
            $this->metadata["orientation"] = "landscape";
            if ($this->metadata["width"] == $this->metadata["height"]) {
                $this->metadata["orientation"] = "square";
            }
            if ($this->metadata["width"] < $this->metadata["height"]) {
                $this->metadata["orientation"] = "portrait";
            }

            switch ($this->metadata["aspect_ratio_number"]) {
                case 1.0:
                    $this->metadata["aspect_ratio"] = "1:1";
                    $this->metadata["aspect_type"] = "square";

                    break;
                case 1.78:
                    $this->metadata["aspect_ratio"] = "16:9";
                    $this->metadata["aspect_type"] = "hd";

                    break;
                case 0.56:
                    $this->metadata["aspect_ratio"] = "9:16";
                    $this->metadata["aspect_type"] = "vhd";

                    break;
                case 2.33:
                    $this->metadata["aspect_ratio"] = "21:9";
                    $this->metadata["aspect_type"] = "scope";

                    break;
                case 0.43:
                    $this->metadata["aspect_ratio"] = "9:21";
                    $this->metadata["aspect_type"] = "vscope";

                    break;
                case 1.90:
                    $this->metadata["aspect_ratio"] = "256:135";
                    $this->metadata["aspect_type"] = "dcp";

                    break;
                case 1.85:
                    $this->metadata["aspect_ratio"] = "37:20";
                    $this->metadata["aspect_type"] = "flat";

                    break;
                case 1.67:
                    $this->metadata["aspect_ratio"] = "4:3";
                    $this->metadata["aspect_type"] = "tv";

                    break;
                case 0.75:
                    $this->metadata["aspect_ratio"] = "3:4";
                    $this->metadata["aspect_type"] = "vtv";

                    break;

                default:
                    $this->metadata["aspect_ratio"] = $this->metadata["width"] . ":" . $this->metadata["height"];
                    $this->metadata["aspect_type"] = "?";
            }
        }
        $fps = "0";
        if (isset($this->metadata["avg_frame_rate"]) && (double)$this->metadata["avg_frame_rate"] > 0) {
            $fps = $this->metadata["avg_frame_rate"];
        }
        if (isset($this->metadata["r_frame_rate"]) && (double)$this->metadata["r_frame_rate"] > 0) {
            $fps = $this->metadata["r_frame_rate"];
        }
        if (strstr($fps, "/")) {
            list($top, $bottom) = explode("/", $fps, 2);
            $fps = (double)$top / (double)$bottom;
        } else {
            $fps = (double)$fps;
        }
        $this->metadata["fps"] = round($fps, 3);
        if (! isset($this->metadata["nb_frames"])) {
            // not set for e.g. MXF video files
            $this->metadata["nb_frames"] = floor((double)$this->metadata["duration"] * $fps);
        }
        ksort($this->metadata);
    }
}
