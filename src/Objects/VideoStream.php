<?php


namespace Brightfish\SpxMediaAnalyzer\Objects;

class VideoStream extends AbstractStream
{
    protected array $metadata;
    /**
     * @var mixed|null
     */
    private $fps;
    /**
     * @var mixed|null
     */
    private $duration;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
        /*
            [0] => Brightfish\SpxMediaAnalyzer\Objects\VideoStream Object
                (
                    [metadata:protected] => Array
                        (
                            [avg_frame_rate] => 74500/3141
                            [bit_rate] => 74475
                            [bits_per_raw_sample] => 8
                            [chroma_location] => left
                            [closed_captions] => 0
                            [codec_long_name] => H.264 / AVC / MPEG-4 AVC / MPEG-4 part 10
                            [codec_name] => h264
                            [codec_tag] => 0x31637661
                            [codec_tag_string] => avc1
                            [codec_time_base] => 3141/149000
                            [codec_type] => video
                            [coded_height] => 112
                            [coded_width] => 208
                            [color_primaries] => smpte170m
                            [color_range] => tv
                            [color_space] => smpte170m
                            [color_transfer] => bt709
                            [display_aspect_ratio] => 20:11
                            [disposition] => Array
                                (
                                    [default] => 1
                                    [dub] => 0
                                    [original] => 0
                                    [comment] => 0
                                    [lyrics] => 0
                                    [karaoke] => 0
                                    [forced] => 0
                                    [hearing_impaired] => 0
                                    [visual_impaired] => 0
                                    [clean_effects] => 0
                                    [attached_pic] => 0
                                    [timed_thumbnails] => 0
                                )
                            [duration] => 6.282000
                            [duration_ts] => 565380
                            [has_b_frames] => 1
                            [height] => 110
                            [index] => 0
                            [is_avc] => true
                            [level] => 11
                            [nal_length_size] => 4
                            [nb_frames] => 149
                            [pix_fmt] => yuv420p
                            [profile] => Main
                            [r_frame_rate] => 24/1
                            [refs] => 1
                            [sample_aspect_ratio] => 1:1
                            [start_pts] => 0
                            [start_time] => 0.000000
                            [tags] => Array
                                (
                                    [creation_time] => 2010-09-23T00:37:25.000000Z
                                    [language] => und
                                    [encoder] => JVT/AVC Coding
                                )
                            [time_base] => 1/90000
                            [type] => video
                            [width] => 200
                        )
                )
        */
        if(isset($this->metadata["width"]) && $this->metadata["height"]){
            $this->metadata["aspect_ratio_number"]=round($this->metadata["width"]/$this->metadata["height"],2);
            $this->metadata["orientation"]="landscape";
            if($this->metadata["width"] == $this->metadata["height"])    $this->metadata["orientation"]="square";
            if($this->metadata["width"] < $this->metadata["height"])    $this->metadata["orientation"]="portrait";

            switch($this->metadata["aspect_ratio_number"]){
                case 1.0:
                    $this->metadata["aspect_ratio"]="1:1";
                    $this->metadata["aspect_type"]="square";
                    break;
                case 1.78:
                    $this->metadata["aspect_ratio"]="16:9";
                    $this->metadata["aspect_type"]="hd";
                    break;
                case 0.56:
                    $this->metadata["aspect_ratio"]="9:16";
                    $this->metadata["aspect_type"]="vhd";
                    break;
                case 2.33:
                    $this->metadata["aspect_ratio"]="21:9";
                    $this->metadata["aspect_type"]="scope";
                    break;
                case 0.43:
                    $this->metadata["aspect_ratio"]="9:21";
                    $this->metadata["aspect_type"]="vscope";
                    break;
                case 1.90:
                    $this->metadata["aspect_ratio"]="256:135";
                    $this->metadata["aspect_type"]="dcp";
                    break;
                case 1.85:
                    $this->metadata["aspect_ratio"]="37:20";
                    $this->metadata["aspect_type"]="flat";
                    break;
                case 1.67:
                    $this->metadata["aspect_ratio"]="4:3";
                    $this->metadata["aspect_type"]="tv";
                    break;
                case 0.75:
                    $this->metadata["aspect_ratio"]="3:4";
                    $this->metadata["aspect_type"]="vtv";
                    break;

                default:
                    $this->metadata["aspect_ratio"]=$this->metadata["width"] . ":" . $this->metadata["height"];
                    $this->metadata["aspect_type"]="?";
            }
        }
        $fps="0";
        if(isset($this->metadata["avg_frame_rate"]) && (double)$this->metadata["avg_frame_rate"] > 0)    $fps=$this->metadata["avg_frame_rate"];
        if(isset($this->metadata["r_frame_rate"]) && (double)$this->metadata["r_frame_rate"] > 0)    $fps=$this->metadata["r_frame_rate"];
        if(strstr($fps,"/")){
            list($top,$bottom)=explode("/",$fps,2);
            $fps=(double)$top/(double)$bottom;
        }
        $this->metadata["fps"]=round($fps,3);
        if(!isset($this->metadata["nb_frames"])){
            // not set for e.g. MXF video files
            $this->metadata["nb_frames"]=floor((double)$this->metadata["duration"]*$fps);
        }
        ksort($this->metadata);

    }
}
