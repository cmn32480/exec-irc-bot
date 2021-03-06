<?php

#####################################################################################################

/*
exec:~staff|20|0|0|1|||||php scripts/staff.php %%trailing%% %%alias%% %%nick%%
exec:~eds|10|0|0|1|||||php scripts/staff.php %%trailing%% %%alias%% %%nick%%
exec:~devs|10|0|0|1|||||php scripts/staff.php %%trailing%% %%alias%% %%nick%%
*/

#####################################################################################################

require_once("lib.php");

$trailing=strtolower(trim($argv[1]));
$alias=$argv[2];
$nick=trim($argv[3]);

switch ($alias)
{
  case "~eds":
    $reason="";
    if ($trailing<>"")
    {
      $reason=" (reason: ".$trailing.")";
    }
    privmsg("editor ping for $nick$reason: janrinok LaminatorX n1 nick martyb Bytram Azrael mrcoolbp cmn32480 coolhand takyon cmn32480|away bytram|away");
    return;
  case "~devs":
    $reason="";
    if ($trailing<>"")
    {
      $reason=" (reason: ".$trailing.")";
    }
    privmsg("dev ping for $nick$reason: TheMightyBuzzard paulej72");
    return;
}

switch ($trailing)
{
  case "meeting":
    $response=wget("soylentnews.org","/");
    $delim1="<!-- begin site_news block -->";
    $delim2="<!-- end site_news block -->";
    $max_len=300;
    $text=extract_text($response,$delim1,$delim2);
    $parts=explode("<hr>",$text);
    $result="";
    for ($i=0;$i<count($parts);$i++)
    {
      if (strpos(strtolower($parts[$i]),"meeting")!==False)
      {
        $result=$parts[$i];
      }
    }
    if ($result<>"")
    {
      term_echo($result);
      $result=strip_tags($result);
      $result=replace_ctrl_chars($result," ");
      $result=str_replace("  "," ",$result);
      if (strlen($result)>$max_len)
      {
        $result=trim(substr($result,0,$max_len))."...";
      }
    }
    else
    {
      require_once("wiki_lib.php");
      $title="Issues to Be Raised at the Next Board Meeting";
      $section="Next meeting";
      $result=get_text($title,$section,True);
      var_dump($result);
      if (is_array($result)==True)
      {
        $result=trim(implode(" ",$text));
      }
      if ($result=="")
      {
        return;
      }
    }
    if (strlen($result)>200)
    {
      $result=trim(substr($result,0,200))."...";
    }
    privmsg(chr(3)."03"."********** ".chr(3)."05".chr(2)."SOYLENTNEWS BOARD MEETING".chr(2).chr(3)."03"." **********");
    privmsg(chr(3)."03".trim($result));
    break;
}

#####################################################################################################

?>
