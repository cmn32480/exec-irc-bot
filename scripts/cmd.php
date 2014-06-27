<?php

# gpl2
# by crutchy
# 26-june-2014

#####################################################################################################

require_once("lib.php");

$cmd=$argv[1];
$trailing=$argv[2];
$data=$argv[3];
$dest=$argv[4];
$params=$argv[5];
$nick=$argv[6];

#####################################################################################################
#                                                                                                   #
#                                          ~~~ CAUTION ~~~                                          #
#                                                                                                   #
#   THIS SCRIPT IS CALLED ON EVERY IRC EVENT, INCLUDING THOSE ORIGINATING FROM THIS SCRIPT.         #
#                                                                                                   #
#   INFINITE LOOPS CAN RESULT FROM:

#     (1) ECHOING A COMAND IN IT'S OWN HANDLER

#     (2) ECHOING A COMMAND WHOSE HANDLER ECHOES THE ORIGINAL COMMAND
#                                                                                                   #
#####################################################################################################

switch (strtoupper($cmd))
{
  case "INTERNAL":

    break;
  case "BUCKET_GET":

    break;
  case "BUCKET_SET":

    break;
  case "BUCKET_UNSET":

    break;
  case "INVITE": # :crutchy!~crutchy@709-27-2-01.cust.aussiebb.net INVITE exec :#~

    break;
  case "JOIN": # :exec!~exec@709-27-2-01.cust.aussiebb.net JOIN #

    break;
  case "KICK":

    break;
  case "KILL":

    break;
  case "MODE":

    break;
  case "NICK":

    break;
  case "NOTICE":

    break;
  case "PART":

    break;
  case "PRIVMSG":
    #echo "/INTERNAL ~sed $trailing\n";
    break;
  case "QUIT":

    break;
  case "043": # nickname was forced to change due to a collision

    break;
  case "263": # server dropped command without processing it

    break;
  case "311": # :irc.sylnt.us 311 exec tme520 ~TME520 218-883-738-54.tpgi.com.au * :TME520

    break;
  case "319": # :irc.sylnt.us 319 exec crutchy :#wiki +#test #sublight #help @#exec #derp @#civ @#1 @#0 ## @#/ @#> @#~ @#

    break;
  case "330": # is logged in as

    break;
  case "353": # channel names list

    break;
  case "401": # :irc.sylnt.us 401 exec SedBot :No such nick/channel

    break;
  case "436": # server detected a nickname collision

    break;
  case "471": # attempting to join a channel which is set +l and is already full

    break;
}

#####################################################################################################

?>
