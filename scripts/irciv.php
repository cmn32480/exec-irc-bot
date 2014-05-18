<?php

# gpl2
# by crutchy
# 17-may-2014

# irciv.php

#####################################################################################################

ini_set("display_errors","on");
date_default_timezone_set("UTC");
require_once("irciv_lib.php");

if ($argv[1]=="<<SAVE>>")
{
  irciv_save_data();
  return;
}

define("TIMEOUT_RANDOM_COORD",10); # sec

define("ACTION_LOGIN","login");
define("ACTION_LOGOUT","logout");
define("ACTION_RENAME","rename");
define("ACTION_INIT","init");
define("ACTION_STATUS","status");
define("ACTION_SET","set");
define("ACTION_UNSET","unset");
define("ACTION_FLAG","flag");
define("ACTION_UNFLAG","unflag");

define("MIN_CITY_SPACING",3);

$map_coords="";
$map_data=array();
$players=array();

$players_bucket=irciv_get_bucket("players");
if ($players_bucket=="")
{
  irciv_term_echo("player bucket contains no data");
}
else
{
  $players=unserialize($players_bucket);
  if ($players===False)
  {
    irciv_err("error unserializing player bucket data");
  }
}
$coords_bucket=irciv_get_bucket("map_coords");
$data_bucket=irciv_get_bucket("map_data");
if (($coords_bucket<>"") and ($data_bucket<>""))
{
  $map_coords=map_unzip($coords_bucket);
  $map_data=unserialize($data_bucket);
}

$nick=$argv[1];
$trailing=$argv[2];
$dest=$argv[3];

if (($trailing=="") or (($dest<>GAME_CHAN) and ($nick<>NICK_EXEC) and ($dest<>NICK_EXEC)))
{
  irciv_privmsg("https://github.com/crutchy-/test");
  return;
}

$parts=explode(" ",$trailing);

$action=strtolower($parts[0]);

switch ($action)
{
  case "help":
  case "?":
    if (count($parts)==1)
    {
      output_help();
    }
    break;
  case ACTION_LOGIN:
    if ((count($parts)==3) and ($nick==NICK_EXEC))
    {
      $player=$parts[1];
      $account=$parts[2];
      if (isset($players[$player])==False)
      {
        $player_id=1;
        if (isset($players[NICK_EXEC]["player_count"])==True)
        {
          $player_id=$players[NICK_EXEC]["player_count"]+1;
        }
        $players[NICK_EXEC]["player_count"]=$player_id;
        $players[$player]["account"]=$account;
        $players[$player]["id"]=$player_id;
        irciv_privmsg("login: player \"$player\" is now logged in");
      }
      else
      {
        irciv_privmsg("login: player \"$player\" already logged in");
      }
    }
    break;
  case ACTION_RENAME:
    if ((count($parts)==3) and ($nick==NICK_EXEC))
    {
      $old=$parts[1];
      $new=$parts[2];
      if ((isset($players[$old])==True) and (isset($players[$new])==False))
      {
        $player_data=$players[$old];
        $players[$new]=$player_data;
        unset($players[$old]);
        irciv_privmsg("player \"$old\" renamed to \"$new\"");
      }
      else
      {
        if (isset($players[$old])==True)
        {
          irciv_privmsg("error renaming player \"$old\" to \"$new\"");
        }
        else
        {
          #irciv_err("ACTION_RENAME: old nick not found");
        }
      }
    }
    else
    {
      irciv_err("ACTION_RENAME: incorrect part count or wrong nick");
    }
    break;
  case ACTION_LOGOUT:
    if (count($parts)==2)
    {
      $player=$parts[1];
      if (isset($players[$player])==True)
      {
        unset($players[$player]);
        irciv_privmsg("logout: player \"$player\" logged out");
      }
      else
      {
        irciv_privmsg("logout: there is no player logged in as \"$player\"");
      }
    }
    break;
  case ACTION_INIT:
    if (count($parts)==1)
    {
      player_init($nick);
      irciv_privmsg("data player \"$nick\" has been initialized");
    }
    else
    {
      irciv_privmsg("syntax: [civ] init");
    }
    break;
  case "u":
  case "up":
    if (count($parts)==1)
    {
      move_active_unit($nick,0);
    }
    else
    {
      irciv_privmsg("syntax: [civ] (up|u)");
    }
    break;
  case "r":
  case "right":
    if (count($parts)==1)
    {
      move_active_unit($nick,1);
    }
    else
    {
      irciv_privmsg("syntax: [civ] (right|r)");
    }
    break;
  case "d":
  case "down":
    if (count($parts)==1)
    {
      move_active_unit($nick,2);
    }
    else
    {
      irciv_privmsg("syntax: [civ] (down|d)");
    }
    break;
  case "l":
  case "left":
    if (count($parts)==1)
    {
      move_active_unit($nick,3);
    }
    else
    {
      irciv_privmsg("syntax: [civ] (left|l)");
    }
    break;
  case "b":
  case "build":
    if (count($parts)>1)
    {
      unset($parts[0]);
      $city_name=implode(" ",$parts);
      build_city($nick,$city_name);
    }
    else
    {
      irciv_privmsg("syntax: [civ] (build|b) City Name");
    }
    break;
  case ACTION_STATUS:
    status($nick);
    break;
  case ACTION_SET:
    if (count($parts)==2)
    {
      $pair=explode("=",$parts[1]);
      if (count($pair)==2)
      {
        $key=$pair[0];
        $value=$pair[1];
        $players[$nick]["settings"][$key]=$value;
        irciv_privmsg("key \"$key\" set to value \"$value\" for player \"$nick\"");
      }
      else
      {
        irciv_privmsg("syntax: [civ] set key=value");
      }
    }
    else
    {
      irciv_privmsg("syntax: [civ] set key=value");
    }
    break;
  case ACTION_UNSET:
    if (count($parts)==2)
    {
      $key=$parts[1];
      if (isset($players[$nick]["settings"][$key])==True)
      {
        unset($players[$nick]["settings"][$key]);
        irciv_privmsg("key \"$key\" unset for player \"$nick\"");
      }
      else
      {
        irciv_privmsg("setting \"$key\" not found for player \"$nick\"");
      }
    }
    else
    {
      irciv_privmsg("syntax: [civ] unset key");
    }
    break;
  case ACTION_FLAG:
    if (count($parts)==2)
    {
      $flag=$parts[1];
      $players[$nick]["flags"][$flag]="";
      irciv_privmsg("flag \"$flag\" set for player \"$nick\"");
    }
    else
    {
      irciv_privmsg("syntax: [civ] flag name");
    }
    break;
  case ACTION_UNFLAG:
    if (count($parts)==2)
    {
      $flag=$parts[1];
      if (isset($players[$nick]["flags"][$flag])==True)
      {
        unset($players[$nick]["flags"][$flag]);
        irciv_privmsg("flag \"$flag\" unset for player \"$nick\"");
      }
      else
      {
        irciv_privmsg("flag \"$flag\" not set for player \"$nick\"");
      }
    }
    else
    {
      irciv_privmsg("syntax: [civ] unflag name");
    }
    break;
}

$players_bucket=serialize($players);
if ($players_bucket===False)
{
  irciv_term_echo("error serializing player bucket data");
}
else
{
  irciv_set_bucket("players",$players_bucket);
}

#####################################################################################################

function output_help()
{
  irciv_privmsg("QUICK START GUIDE");
  irciv_privmsg("unit movement: (left|l),(right|r),(up|u),(down|d)");
  irciv_privmsg("settler actions: (build|b)");
  irciv_privmsg("player functions: (help|?),status,init,flag/unflag,set/unset");
  irciv_privmsg("flags: public_status,grid");
}

#####################################################################################################

function player_ready($nick)
{
  global $players;
  global $map_data;
  if (isset($map_data["cols"])==False)
  {
    irciv_privmsg("error: map not ready");
    return False;
  }
  if (isset($players[$nick])==False)
  {
    irciv_privmsg("player \"$nick\" not found");
    return False;
  }
  return True;
}

#####################################################################################################

function player_init($nick)
{
  global $players;
  global $map_coords;
  global $map_data;
  if (player_ready($nick)==False)
  {
    return;
  }
  $players[$nick]["init_time"]=time();
  $players[$nick]["units"]=array();
  $players[$nick]["cities"]=array();
  $players[$nick]["fog"]=str_repeat("0",strlen($map_coords));
  $start_x=-1;
  $start_y=-1;
  if (random_coord(TERRAIN_LAND,$start_x,$start_y)==False)
  {
    return;
  }
  add_unit($nick,"settler",$start_x,$start_y);
  add_unit($nick,"warrior",$start_x,$start_y);
  $players[$nick]["active"]=-1;
  cycle_active($nick);
  $players[$nick]["start_x"]=$start_x;
  $players[$nick]["start_y"]=$start_y;
  status($nick);
}

#####################################################################################################

function random_coord($terrain,&$x,&$y)
{
  global $map_coords;
  global $map_data;
  $start=microtime(True);
  do
  {
    $x=mt_rand(0,$map_data["cols"]-1);
    $y=mt_rand(0,$map_data["rows"]-1);
    $coord=map_coord($map_data["cols"],$x,$y);
    $dt=microtime(True)-$start;
    if ($dt>TIMEOUT_RANDOM_COORD)
    {
      irciv_privmsg("error: random_coord timeout");
      return False;
    }
  }
  while ($map_coords[$coord]<>$terrain);
  return True;
}

#####################################################################################################

function add_unit($nick,$type,$x,$y)
{
  global $players;
  if (player_ready($nick)==False)
  {
    return False;
  }
  $units=&$players[$nick]["units"];
  $data["type"]=$type;
  $data["health"]=100;
  $data["sight_range"]=4;
  $data["x"]=$x;
  $data["y"]=$y;
  $units[]=$data;
  $i=count($units)-1;
  $units[$i]["index"]=$i;
  unfog($nick,$x,$y,$data["sight_range"]);
  return True;
}

#####################################################################################################

function add_city($nick,$x,$y,$city_name)
{
  global $players;
  if (player_ready($nick)==False)
  {
    return;
  }
  $cities=&$players[$nick]["cities"];
  $data["name"]=$city_name;
  $data["population"]=1;
  $data["sight_range"]=7;
  $data["x"]=$x;
  $data["y"]=$y;
  $cities[]=$data;
  $i=count($cities)-1;
  $cities[$i]["index"]=$i;
  unfog($nick,$x,$y,$data["sight_range"]);
}

#####################################################################################################

function unfog($nick,$x,$y,$range)
{
  global $players;
  global $map_coords;
  global $map_data;
  if (player_ready($nick)==False)
  {
    return False;
  }
  $cols=$map_data["cols"];
  $rows=$map_data["rows"];
  $size=2*$range+1;
  $region=imagecreate($size,$size);
  $white=imagecolorallocate($region,255,255,255);
  $black=imagecolorallocate($region,0,0,0);
  imagefill($region,0,0,$white);
  imagefilledellipse($region,$range,$range,$size,$size,$black);
  for ($j=0;$j<$size;$j++)
  {
    for ($i=0;$i<$size;$i++)
    {
      $xx=$x-$range+$i;
      $yy=$y-$range+$j;
      if (imagecolorat($region,$i,$j)==$black)
      {
        if (($xx>=0) and ($yy>=0) and ($xx<$cols) and ($yy<$rows))
        {
          $coord=map_coord($cols,$xx,$yy);
          $players[$nick]["fog"][$coord]="1";
        }
      }
    }
  }
  imagedestroy($region);
}

#####################################################################################################

function status($nick)
{
  global $players;
  global $map_data;
  if (isset($players[$nick])==False)
  {
    return;
  }
  if (isset($players[$nick]["units"])==False)
  {
    player_init($nick);
    return;
  }
  $public=False;
  if (isset($players[$nick]["flags"]["public_status"])==True)
  {
    $public=True;
  }
  $i=$players[$nick]["active"];
  $unit=$players[$nick]["units"][$i];
  $index=$unit["index"];
  $type=$unit["type"];
  $health=$unit["health"];
  $x=$unit["x"];
  $y=$unit["y"];
  $n=count($players[$nick]["units"]);
  if (isset($players[$nick]["status_messages"])==True)
  {
    for ($i=0;$i<count($players[$nick]["status_messages"]);$i++)
    {
      status_msg($nick,GAME_CHAN."/$nick => ".$players[$nick]["status_messages"][$i],$public);
    }
    unset($players[$nick]["status_messages"]);
  }
  status_msg($nick,GAME_CHAN."/$nick => $index/$n, $type, +$health, ($x,$y)",$public);
}

#####################################################################################################

function status_msg($nick,$msg,$public)
{
  if ($public==False)
  {
    pm($nick,$msg);
  }
  else
  {
    irciv_privmsg($msg);
  }
}

#####################################################################################################

function move_active_unit($nick,$dir)
{
  global $players;
  global $map_data;
  global $map_coords;
  if (player_ready($nick)==False)
  {
    return False;
  }
  $dir_x=array(0,1,0,-1);
  $dir_y=array(-1,0,1,0);
  $captions=array("up","right","down","left");
  if (isset($players[$nick]["active"])==True)
  {
    $active=$players[$nick]["active"];
    $old_x=$players[$nick]["units"][$active]["x"];
    $old_y=$players[$nick]["units"][$active]["y"];
    $x=$old_x+$dir_x[$dir];
    $y=$old_y+$dir_y[$dir];
    $caption=$captions[$dir];
    if (($x<0) or ($x>=$map_data["cols"]) or ($y<0) or ($y>=$map_data["rows"]))
    {
      $players[$nick]["status_messages"][]="move $caption failed for active unit (already @ edge of map)";
    }
    elseif ($map_coords[map_coord($map_data["cols"],$x,$y)]<>TERRAIN_LAND)
    {
      $players[$nick]["status_messages"][]="move $caption failed for active unit (already @ edge of landmass)";
    }
    else
    {
      $players[$nick]["units"][$active]["x"]=$x;
      $players[$nick]["units"][$active]["y"]=$y;
      unfog($nick,$x,$y,$players[$nick]["units"][$active]["sight_range"]);
      $type=$players[$nick]["units"][$active]["type"];
      $players[$nick]["status_messages"][]="successfully moved $type $caption from ($old_x,$old_y) to ($x,$y)";
      cycle_active($nick);
    }
    status($nick);
  }
}

#####################################################################################################

function delete_unit($nick,$index)
{
  global $players;
  if (player_ready($nick)==False)
  {
    return False;
  }
  if (isset($players[$nick]["units"][$index])==False)
  {
    return False;
  }
  $count=count($players[$nick]["units"]);
  $next=$index+1;
  for ($i=$next;$i<$count;$i++)
  {
    $players[$nick]["units"][$i]["index"]=$i-1;
  }
  unset($players[$nick]["units"][$index]);
  $players[$nick]["units"]=array_values($players[$nick]["units"]);
  return True;
}

#####################################################################################################

function build_city($nick,$city_name)
{
  global $players;
  global $map_data;
  global $map_coords;
  if (player_ready($nick)==False)
  {
    return False;
  }
  if (isset($players[$nick]["active"])==False)
  {
    return False;
  }
  $unit=$players[$nick]["units"][$players[$nick]["active"]];
  if ($unit["type"]<>"settler")
  {
    $players[$nick]["status_messages"][]="only settlers can build cities";
  }
  else
  {
    $x=$unit["x"];
    $y=$unit["y"];
    $city_exists=False;
    $city_adjacent=False;
    $cities=&$players[$nick]["cities"];
    for ($i=0;$i<count($cities);$i++)
    {
      if ($cities[$i]["name"]==$city_name)
      {
        $city_exists=True;
        $players[$nick]["status_messages"][]="city named \"$city_name\" already exists";
        break;
      }
      $dx=abs($cities[$i]["x"]-$x);
      $dy=abs($cities[$i]["y"]-$y);
      if (($dx<MIN_CITY_SPACING) and ($dy<MIN_CITY_SPACING))
      {
        $city_adjacent=True;
        $players[$nick]["status_messages"][]="city \"".$cities[$i]["name"]."\" is too close";
        break;
      }
    }
    if (($city_exists==False) and ($city_adjacent==False))
    {
      add_city($nick,$x,$y,$city_name);
      #delete_unit($nick,$players[$nick]["active"]); # WORKS BUT LEAVE OUT FOR TESTING
      $players[$nick]["status_messages"][]="successfully established the new city of \"$city_name\" at coordinates ($x,$y)";
      cycle_active($nick);
    }
  }
  status($nick);
}

#####################################################################################################

function output_map($nick)
{
  global $players;
  global $map_coords;
  global $map_data;
  if (player_ready($nick)==False)
  {
    return False;
  }
  $game_id=sprintf("%02d",0);
  $player_id=sprintf("%02d",$players[$nick]["id"]);
  $timestamp=date("YmdHis",time());
  $key=random_string(16);
  $filename=$game_id.$player_id.$timestamp.$key;
  $response=upload_map_image($filename,$map_coords,$map_data,$players,$nick);
  $response_lines=explode("\n",$response);
  $msg=trim($response_lines[count($response_lines)-1]);
  if (trim($response_lines[0])=="HTTP/1.1 200 OK")
  { 
    if ($msg=="SUCCESS")
    {
      $players[$nick]["status_messages"][]="http://irciv.port119.net/?pid=".$players[$nick]["id"];
      #$players[$nick]["status_messages"][]="http://irciv.port119.net/?map=$filename";
    }
  }
  else
  {
    $players[$nick]["status_messages"][]=$msg;
  }
}

#####################################################################################################

function cycle_active($nick)
{
  global $players;
  if (player_ready($nick)==False)
  {
    return False;
  }
  output_map($nick);
  $n=count($players[$nick]["units"]);
  if (isset($players[$nick]["active"])==False)
  {
    $players[$nick]["active"]=0;
  }
  else
  {
    $players[$nick]["active"]=$players[$nick]["active"]+1;
    if ($players[$nick]["active"]>=$n)
    {
      $players[$nick]["active"]=0;
    }
  }
}

#####################################################################################################

?>
