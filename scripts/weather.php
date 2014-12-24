<?php

# gpl2
# by crutchy

#####################################################################################################

/*
exec:~weather|60|0|0|1|||||php scripts/weather.php %%alias%% %%trailing%% %%nick%%
exec:~weather-old|10|0|0|1|||||php scripts/weather.php %%alias%% %%trailing%% %%nick%%
exec:~weather-add|10|0|0|1|||||php scripts/weather.php %%alias%% %%trailing%% %%nick%%
exec:~weather-del|10|0|0|1|||||php scripts/weather.php %%alias%% %%trailing%% %%nick%%
exec:~weather-prefs|10|0|0|1|||||php scripts/weather.php %%alias%% %%trailing%% %%nick%%
*/

#####################################################################################################

require_once("lib.php");
require_once("weather_lib.php");
require_once("time_lib.php");

$alias=$argv[1];
$trailing=trim($argv[2]);
$nick=trim($argv[3]);

switch ($alias)
{
  case "~weather-prefs":
    # TODO: registered nick personalised settings (units, default location, private msg, formatting, etc)
    privmsg("tama lama ding dong");
    break;
  case "~weather-del":
    if (del_location($trailing)==True)
    {
      privmsg("location \"$trailing\" deleted");
    }
    else
    {
      if (trim($trailing)<>"")
      {
        privmsg("location for \"$trailing\" not found");
      }
      else
      {
        privmsg("syntax: ~weather-del <name>");
      }
    }
    break;
  case "~weather-add":
    set_location_alias($alias,$trailing);
    break;
  case "~weather":
    $data=process_weather($trailing,$nick);
    if ($data!==False)
    {
      privmsg($data);
    }
    else
    {
      privmsg("syntax: ~weather <location>");
    }
    break;
  case "~weather-old":
    $data=process_weather_old($trailing,$nick);
    if (is_array($data)==False)
    {
      switch ($data)
      {
        case 1:
          privmsg("weather for \"$trailing\" not found. check spelling or try another nearby location.");
          break;
        case 2:
          privmsg("all stations matching \"$trailing\" are either inactive or have no data. check spelling or try another nearby location.");
          break;
        default:
          privmsg("syntax: ~weather location");
          privmsg("weather data courtesy of the APRS Citizen Weather Observer Program (CWOP) @ http://weather.gladstonefamily.net/");
      }
    }
    else
    {
      $time_str=$data["utc"]." (UTC)";
      $time=get_time($trailing);
      if ($time<>"")
      {
        $arr=convert_google_location_time($time);
        $t=$arr["timestamp"]-$data["age_num"]*60;
        $time_str=date("g:i a",$t)." (".$arr["timezone"].")";
      }
      $color="10";
      privmsg("weather for ".chr(2).chr(3).$color.$data["name"].chr(3).chr(2)." at $time_str".$data["age"]." temp: ".chr(2).chr(3).$color.$data["temp"].chr(3).chr(2).", dp: ".chr(2).chr(3).$color.$data["dewpoint"].chr(3).chr(2).", press: ".chr(2).chr(3).$color.$data["press"].chr(3).chr(2).", humid: ".chr(2).chr(3).$color.$data["humidity"].chr(3).chr(2).", wind: ".chr(2).chr(3).$color.$data["wind_speed"].chr(3).chr(2)." @ ".chr(2).chr(3).$color.$data["wind_direction"].chr(3).chr(2));
    }
    break;
}

#####################################################################################################

?>
