<?php

/*
exec:~jisho|20|0|0|1|||||php scripts/jisho2.php %%trailing%%
*/

require_once("lib.php");

$trailing=trim($argv[1]);

if ($trailing=="")
{
  privmsg("syntax: ~jisho <word>");
  privmsg("looks up jisho.org");
  return;
}

$response=wget("jisho.org","/api/v1/search/words?keyword=".urlencode($trailing));
$content=strip_headers($response);
if ($content===False)
{
  privmsg("error downloading");
  return;
}

$results=json_decode($content,True);

#file_put_contents("../data/jisho",json_encode($results,JSON_PRETTY_PRINT));

if (isset($results["data"])==False)
{
  privmsg("invalid result");
  return;
}

for ($i=0;$i<min(3,count($results["data"]));$i++)
{
  if ($results["data"][$i]["is_common"]==False)
  {
    continue;
  }
  $out="";
  for ($j=0;$j<count($results["data"][$i]["japanese"]);$j++)
  {
    if ($out<>"")
    {
      $out=$out.", ";
    }
    if (count($results["data"][$i]["japanese"])>1)
    {
      $out=$out."(".($j+1).") ";
    }
    $out=$out."word: ".$results["data"][$i]["japanese"][$j]["word"].", reading: ".$results["data"][$i]["japanese"][$j]["reading"].", ";
  }
  if (count($results["data"][$i]["senses"])>0)
  {
    $out=$out."meanings: ";
  }
  $senses=$results["data"][$i]["senses"];
  for ($j=0;$j<count($senses);$j++)
  {
    $meanings="";
    if (count($senses)>1)
    {
      if (count($senses[$j]["english_definitions"])>0)
      {
        if ($j>0)
        {
          $meanings=$meanings.", ";
        }
        $meanings=$meanings."sense ".($j+1).": ";
      }
    }
    for ($k=0;$k<count($senses[$j]["english_definitions"]);$k++)
    {
      if ($k>0)
      {
        $meanings=$meanings.", ";
      }
      $meanings=$meanings.$senses[$j]["english_definitions"][$k];
    }
    $out=$out.$meanings;
  }
  if ($out<>"")
  {
    privmsg($out);
  }
}

if (count($results["data"])==0)
{
  privmsg("no results");
}
else
{
  privmsg(count($results["data"])." results");
}

?>