<?php


namespace Brightfish\SpxMediaAnalyzer\Objects;

class ImageStream extends AbstractStream
{
    protected array $metadata;

    public function __construct(array $metadata)
    {
        parent::__construct($metadata);
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

    }
}
