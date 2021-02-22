<?php


namespace Brightfish\SpxMediaAnalyzer;


use Illuminate\Support\Carbon;
use Exception;

class DcpProbe
{
    var string $version="0.0.1";

    public function probe(string $input):array
    {
        $cpl_xml=$this->get_cpl_text($input);
        if(!$cpl_xml){
            return [];
        }
        $cpl_dom=simplexml_load_string($cpl_xml);
        $cpl_body=$cpl_dom->children();
        $data=Array();

        $data["command"]["binary"] = $this->version;
        $data["command"]["input"] = [
            "filename" => $input,
            "filesize" => $this->get_dcp_size($input),
            "modified" => date("c", filemtime($input)),
            "changed" => date("c", filectime($input)),
        ];
        $data["command"]["started_at"] = date("c");
        $t0 = microtime(true);

        $data["uuid"]=str_replace("urn:uuid:","",(string)$cpl_body->Id);
        $data["content_title"]=(string)$cpl_body->ContentTitleText;
        $data["issue_date"]=Carbon::parse($cpl_body->IssueDate);
        $data["annotation_text"]=(string)($cpl_body->AnnotationText);
        $data["issuer"]=(string)($cpl_body->Issuer);
        $data["creator"]=(string)($cpl_body->Creator);
        $data["content_kind"]=(string)($cpl_body->ContentKind);
        $data["rating_list"]=(string)($cpl_body->RatingList);
        $data["total_reels"]=count($cpl_body->ReelList);
        $data["total_frames"]=0;
        foreach($cpl_body->ReelList->Reel as $reel){
            $data["reel_uuid"]=(string)($reel->Id);
            if(!isset($reel->AssetList))    continue;
            foreach($reel->AssetList as $asset){
                if(isset($asset->MainPicture)){
                    // video asset
                    $data["aspect_ratio"]=(string)$asset->MainPicture->ScreenAspectRatio;
                    $data["frame_rate"]=(string)$asset->MainPicture->FrameRate;
                    $fps_parts=explode(' ',$data["frame_rate"]);
                    if(count($fps_parts) == 2){
                        $data["fps"]=round($fps_parts[0]/$fps_parts[1],3);
                    } else {
                        $data["fps"]=round($data["frame_rate"],3);
                    }
                    $data["frames"]=(int)$asset->MainPicture->Duration;
                    $data["total_frames"]+=$data["frames"];
                    $data["seconds"]=round($data["frames"]/$data["fps"],4);
                }
            }
        }
        $t1 = microtime(true);
        $data["command"]["finished_at"] = date("c");
        $duration = round($t1 - $t0, 3);
        $data["command"]["duration"] = $duration;
        // ----


        $data["result"] = json_decode(implode("\n", $output), true);
        ksort($data["result"]);
        foreach ($data["result"]["streams"] as $i => $stream) {
            $data["result"]["streams"][$i]["type"] = $stream["codec_type"];
            if (
                $data["result"]["format"]["nb_streams"] == 1
                && $stream["codec_type"] == "video"
                && $stream["avg_frame_rate"] == "0/0"
                && in_array($stream["codec_name"], $this->image_formats)) {
                $data["result"]["streams"][$i]["type"] = "image";
            }
            ksort($data["result"]["streams"][$i]);
        }
        if (isset($data["result"]["format"]) && isset($data["result"]["streams"][0])) {
            ksort($data["result"]["format"]);
        }

        return $data;
    }

    private function get_cpl_text(string $input,$min_size=10,$max_size=5000): string
    {
        $cpl_text="";
        if(is_dir($input)){
            $files=glob("$input/*");
            foreach($files as $file){
                $extension=pathinfo($file,PATHINFO_EXTENSION);
                if(!in_array($extension,["xml","cpl"])) continue;
                if(filesize($file) < $min_size) continue;
                if(filesize($file) > $max_size) continue;
                $file_content=file_get_contents($file);
                if(stristr($file_content,"ReelList")){
                    $cpl_text=$file_content;
                }
            }
        } elseif(is_file($input)){
            $file_content=file_get_contents($input);
            if(stristr($file_content,"ReelList")){
                $cpl_text=$file_content;
            }
        }
        return $cpl_text;
    }

    private function get_dcp_size(string $input): int
    {
        $folder="";
        if(is_file($input)){
            $folder=dirname($input);
        } elseif(is_dir($input)){
            $folder=$input;
        }
        if(!$folder){
            return 0;
        }
        $total_size=0;
        $files=glob("$input/*");
        foreach($files as $file){
            $total_size+=filesize($file);
        }
        return $total_size;
    }

}