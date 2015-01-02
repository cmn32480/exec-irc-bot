<?php

#####################################################################################################

/*
exec:~welcome|10|0|0|0|||||php scripts/welcome.php %%nick%% %%dest%% %%alias%% %%trailing%%
exec:~welcome-internal|30|0|0|1||INTERNAL|||php scripts/welcome.php %%nick%% %%dest%% %%alias%% %%trailing%%
*/

#####################################################################################################

require_once("lib.php");
require_once("weather_lib.php");
require_once("time_lib.php");
require_once("switches.php");

$nick=$argv[1];
$dest=$argv[2];
$alias=$argv[3];
$trailing=$argv[4];
$msg="";
$flag=handle_switch($alias,$dest,$nick,$trailing,"<<EXEC_WELCOME_CHANNELS>>","~welcome","~welcome-internal",$msg);
switch ($flag)
{
  case 1:
    privmsg("welcome enabled for ".chr(3)."10$dest");
    return;
  case 2:
    privmsg("welcome already enabled for ".chr(3)."10$dest");
    return;
  case 3:
    privmsg("welcome disabled for ".chr(3)."10$dest");
    return;
  case 4:
    privmsg("welcome already disabled for ".chr(3)."10$dest");
    return;
  case 9:
    show_welcome($nick);
    return;
}

#####################################################################################################

function show_welcome($nick)
{
  $location=get_location($nick);
  if ($location===False)
  {
    return;
  }
  $time=get_time($location);
  if ($time=="")
  {
    return;
  }
  $arr=convert_google_location_time($time);
  $data=process_weather($location);
  if (is_array($data)==False)
  {
    return;
  }
  if (($data["temp_C"]===False) or ($data["temp_F"]===False))
  {
    return;
  }
  privmsg("$nick: ".$arr["location"].", ".$data["temp_C"]."°C (".$data["temp_F"]."°F), ".date("g:i a",$arr["timestamp"])." ".$arr["timezone"].", ".date("l, j F Y",$arr["timestamp"]));
}

#####################################################################################################

?>
