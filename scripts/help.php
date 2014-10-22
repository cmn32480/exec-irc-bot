<?php

# gpl2
# by crutchy
# 6-sep-2014

#####################################################################################################

require_once("lib.php");
require_once("wiki_lib.php");

$trailing=trim($argv[1]);
$dest=trim($argv[2]);
$nick=trim($argv[3]);
$alias=strtolower(trim($argv[4]));

if ($trailing=="")
{
  privmsg("http://sylnt.us/exec#Quick_start");
  return;
}

$parts=explode(" ",$trailing);
delete_empty_elements($parts);
$cmd=strtolower($parts[0]);
array_shift($parts);
$trailing=trim(implode(" ",$parts));
unset($parts);

$result=get_help($cmd);
if (($result=="") and ($result!==False) and ($result!==True))
{
  if ($cmd[0]<>"~")
  {
    $result=get_help("~".$cmd);
  }
}
if ($result===True)
{
  return;
}
privmsg("help for \"$cmd\" alias not found");

#####################################################################################################

function get_help($cmd)
{
  $title="IRC:exec aliases";
  $section=$cmd;
  if (login(True)==False)
  {
    return False;
  }
  $result="";
  $text=get_text($title,$section,True,True);
  if ($text!==False)
  {
    for ($i=0;$i<min(count($text),3);$i++)
    {
      bot_ignore_next();
      privmsg(trim($text[$i]));
    }
    if ($cmd[0]=="~")
    {
      $cmd=".7E".substr($cmd,1);
    }
    privmsg("http://wiki.soylentnews.org/wiki/IRC:exec_aliases#$cmd");
    $result=True;
  }
  logout(True);
  return $result;
}

#####################################################################################################

/*

==unlock==

Syntax:
* ~weather location

Examples: (section optional in wiki)
* x

Related commands: (section optional in wiki)
* x

Developers: (not shown in irc)
* [[User:Crutchy|crutchy]]

Sources: (not shown in irc)
* x

*/

#####################################################################################################

?>
