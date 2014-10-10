<?php

# gpl2
# by crutchy

#####################################################################################################

ini_set("display_errors","on");
date_default_timezone_set("UTC");
require_once("lib.php");

$trailing=$argv[1];
$dest=$argv[2];
$nick=$argv[3];
$alias=$argv[4];

$list=array(
  "crutchy-/exec-irc-bot",
  "TheMightyBuzzard/slashcode",
  "chromatos/pas",
  "Subsentient/aqu4bot",
  "SoylentNews/slashcode",
  "paulej72/slashcode",
  "NCommander/slashcode");

for ($i=0;$i<count($list);$i++)
{
  check($list[$i]);
}

#####################################################################################################

function check($repo)
{
  $host="api.github.com";
  $port=443;
  $uri="/repos/$repo/events";
  $response=wget($host,$uri,$port,ICEWEASEL_UA,"",60);
  $content=strip_headers($response);
  $data=json_decode($content,True);
  $n=count($data);
  for ($i=0;$i<$n;$i++)
  {
    $timestamp=$data[$i]["created_at"];
    $t=convert_timestamp($timestamp,"Y-m-d H:i:s ");
    $dt=microtime(True)-$t;
    if ($dt<=900) # 15 minutes
    {
      if ($data[$i]["type"]=="PushEvent")
      {
        $msg=chr(3)."02"."$repo: ".chr(3)."08".$data[$i]["actor"]["login"].chr(3)." pushed to  - https://github.com/$repo";
        pm("#github",$msg);
      }
    }
  }
}

#####################################################################################################

?>