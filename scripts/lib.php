<?php

# gpl2
# by crutchy
# 24-aug-2014

#####################################################################################################

ini_set("display_errors","on");

require_once("lib_buckets.php");

define("NICK_EXEC","exec");

define("VALID_UPPERCASE","ABCDEFGHIJKLMNOPQRSTUVWXYZ");
define("VALID_LOWERCASE","abcdefghijklmnopqrstuvwxyz");
define("VALID_NUMERIC","0123456789");

# VALID_UPPERCASE.VALID_LOWERCASE.VALID_NUMERIC

define("ICEWEASEL_UA","Mozilla/5.0 (X11; Linux x86_64; rv:24.0) Gecko/20140429 Firefox/24.0 Iceweasel/24.5.0");

define("BUCKET_CONNECTION_ESTABLISHED","<<IRC_CONNECTION_ESTABLISHED>>");
define("BUCKET_IGNORE_NEXT","<<BOT_IGNORE_NEXT>>");

$url_blacklist=array("kidd","porn","goat","xxx","sex","fuc");

#####################################################################################################

function bot_ignore_next()
{
  set_bucket(BUCKET_IGNORE_NEXT,"1");
}

#####################################################################################################

function convert_timestamp($time,$format)
{
  $arr=date_parse_from_format($format,$time);
  return mktime($arr["hour"],$arr["minute"],$arr["second"],$arr["month"],$arr["day"],$arr["year"]);
}

#####################################################################################################

function delete_empty_elements(&$array)
{
  for ($i=0;$i<count($array);$i++)
  {
    if ($array[$i]=="")
    {
      unset($array[$i]);
    }
  }
  $array=array_values($array);
}

#####################################################################################################

function exec_is_integer($value)
{
  return ctype_digit(strval($value));
}

#####################################################################################################

function random_string($length)
{
  $legal=VALID_UPPERCASE.VALID_LOWERCASE.VALID_NUMERIC;
  $result="";
  for ($i=0;$i<$length;$i++)
  {
    $result=$result.$legal[mt_rand(0,strlen($legal)-1)];
  }
  return $result;
}

#####################################################################################################

function term_echo($msg)
{
  echo "\033[34m$msg\033[0m\n";
}

#####################################################################################################

function privmsg($msg)
{
  echo "/PRIVMSG $msg\n";
}

#####################################################################################################

function action($msg)
{
  rawmsg(chr(1)."ACTION smiles at $msg");
}

#####################################################################################################

function rawmsg($msg)
{
  echo "/IRC $msg\n";
}

#####################################################################################################

function pm($nick,$msg)
{
  echo "/IRC :".NICK_EXEC." PRIVMSG $nick :$msg\n";
}

#####################################################################################################

function notice($nick,$msg)
{
  echo "/IRC :".NICK_EXEC." NOTICE $nick :$msg\n";
}

#####################################################################################################

function err($msg)
{
  term_echo($msg);
  die();
}

#####################################################################################################

function check_url($url)
{
  global $url_blacklist;
  $lower_url=strtolower($url);
  for ($i=0;$i<count($url_blacklist);$i++)
  {
    if (strpos($lower_url,$url_blacklist[$i])!==False)
    {
      term_echo("*** blacklisted URL detected ***");
      return False;
    }
  }
  return True;
}

#####################################################################################################

function wtouch($host,$uri,$port,$timeout=5)
{
  if (check_url($host.$uri)==False) # check url against blacklist
  {
    return False;
  }
  $errno=0;
  $errstr="";
  if ($port==80)
  {
    $fp=fsockopen($host,80,$errno,$errstr,$timeout);
  }
  elseif ($port==443)
  {
    $fp=fsockopen("ssl://$host",443,$errno,$errstr,$timeout);
  }
  else
  {
    $fp=fsockopen($host,$port,$errno,$errstr,$timeout);
  }
  if ($fp===False)
  {
    return False;
  }
  fwrite($fp,"GET $uri HTTP/1.0\r\nHost: $host\r\nConnection: Close\r\n\r\n");
  $response=fgets($fp,256);
  fclose($fp);
  return trim($response);
}

#####################################################################################################

function strip_ctrl_chars($url)
{
  return replace_ctrl_chars($url,"");
}

#####################################################################################################

function replace_ctrl_chars($url,$replace)
{
  $url=str_replace("\t",$replace,$url);
  $url=str_replace("\n",$replace,$url);
  $url=str_replace("\r",$replace,$url);
  $url=str_replace("\0",$replace,$url);
  return str_replace("\x0B",$replace,$url);
}

#####################################################################################################

function get_host_and_uri($url,&$host,&$uri,&$port)
{
  $url=trim($url);
  $comp=parse_url($url);
  $host="";
  if (isset($comp["host"])==True)
  {
    if ($comp["host"]<>"")
    {
      $host=$comp["host"];
    }
  }
  $port=80;
  if (isset($comp["scheme"])==True)
  {
    if ($comp["scheme"]=="https")
    {
      $port=443;
    }
  }
  $uri=$comp["path"];
  if (isset($comp["query"])==True)
  {
    if ($comp["query"]<>"")
    {
      $uri=$uri."?".$comp["query"];
    }
  }
  if (isset($comp["fragment"])==True)
  {
    if ($comp["fragment"]<>"")
    {
      $uri=$uri."#".$comp["fragment"];
    }
  }
  if (($host=="") or ($uri==""))
  {
    return False;
  }
  else
  {
    return True;
  }
}

#####################################################################################################

function get_redirected_url($from_url,$url_list="")
{
  $url=trim($from_url);
  if ($url=="")
  {
    return False;
  }
  $comp=parse_url($url);
  $host="";
  if (isset($comp["host"])==False)
  {
    if (is_array($url_list)==True)
    {
      if (count($url_list)>0)
      {
        $host=parse_url($url_list[count($url_list)-1],PHP_URL_HOST);
        $scheme=parse_url($url_list[count($url_list)-1],PHP_URL_SCHEME);
        $url=$scheme."://".$host.$url;
      }
    }
  }
  else
  {
    $host=$comp["host"];
  }
  if ($host=="")
  {
    term_echo("redirect without host: ".$url);
    return False;
  }
  $uri=$comp["path"];
  if (isset($comp["query"])==True)
  {
    if ($comp["query"]<>"")
    {
      $uri=$uri."?".$comp["query"];
    }
  }
  if (isset($comp["fragment"])==True)
  {
    if ($comp["fragment"]<>"")
    {
      $uri=$uri."#".$comp["fragment"];
    }
  }
  $port=80;
  if (isset($comp["scheme"])==True)
  {
    if ($comp["scheme"]=="https")
    {
      $port=443;
    }
  }
  if (($host=="") or ($uri==""))
  {
    return False;
  }
  $headers=whead($host,$uri,$port,ICEWEASEL_UA,"",10);
  $location=trim(exec_get_header($headers,"location",False));
  if ($location=="")
  {
    return $url;
  }
  else
  {
    if (is_array($url_list)==True)
    {
      if (in_array($location,$url_list)==True)
      {
        return False;
      }
      else
      {
        $list=$url_list;
        $list[]=$url;
        return get_redirected_url($location,$list);
      }
    }
    else
    {
      $list=array($url);
      return get_redirected_url($location,$list);
    }
  }
}

#####################################################################################################

function whead($host,$uri,$port=80,$agent=ICEWEASEL_UA,$extra_headers="",$timeout=20)
{
  if (check_url($host.$uri)==False) # check url against blacklist
  {
    return "";
  }
  $errno=0;
  $errstr="";
  if ($port==443)
  {
    $fp=fsockopen("ssl://$host",443,$errno,$errstr,$timeout);
  }
  else
  {
    $fp=fsockopen($host,$port,$errno,$errstr,$timeout);
  }
  if ($fp===False)
  {
    $msg="Error connecting to \"$host\".";
    term_echo($msg);
    return $msg;
  }
  $headers="HEAD $uri HTTP/1.0\r\n";
  $headers=$headers."Host: $host\r\n";
  if ($agent<>"")
  {
    $headers=$headers."User-Agent: $agent\r\n";
  }
  if ($extra_headers<>"")
  {
    foreach ($extra_headers as $key => $value)
    {
      $headers=$headers.$key.": ".$value."\r\n";
    }
  }
  $headers=$headers."Connection: Close\r\n\r\n";
  fwrite($fp,$headers);
  $response="";
  while (!feof($fp))
  {
    $response=$response.fgets($fp,1024);
  }
  fclose($fp);
  return $response;
}

#####################################################################################################

function wget_ssl($host,$uri,$agent=ICEWEASEL_UA,$extra_headers="")
{
  return wget($host,$uri,443,$agent,$extra_headers);
}

#####################################################################################################

function wget($host,$uri,$port=80,$agent=ICEWEASEL_UA,$extra_headers="",$timeout=20)
{
  if (check_url($host.$uri)==False) # check url against blacklist
  {
    return "";
  }
  $errno=0;
  $errstr="";
  if ($port==443)
  {
    $fp=fsockopen("ssl://$host",443,$errno,$errstr,$timeout);
  }
  else
  {
    $fp=fsockopen($host,$port,$errno,$errstr,$timeout);
  }
  if ($fp===False)
  {
    $msg="Error connecting to \"$host\".";
    term_echo($msg);
    return $msg;
  }
  $headers="GET $uri HTTP/1.0\r\n";
  $headers=$headers."Host: $host\r\n";
  if ($agent<>"")
  {
    $headers=$headers."User-Agent: $agent\r\n";
  }
  if ($extra_headers<>"")
  {
    foreach ($extra_headers as $key => $value)
    {
      $headers=$headers.$key.": ".$value."\r\n";
    }
  }
  $headers=$headers."Connection: Close\r\n\r\n";
  fwrite($fp,$headers);
  $response="";
  while (!feof($fp))
  {
    $response=$response.fgets($fp,1024);
  }
  fclose($fp);
  return $response;
}

#####################################################################################################

function wpost($host,$uri,$port,$agent=ICEWEASEL_UA,$params,$extra_headers="",$timeout=20)
{
  if (check_url($host.$uri)==False) # check url against blacklist
  {
    return "";
  }
  $errno=0;
  $errstr="";
  if ($port==443)
  {
    $fp=fsockopen("ssl://$host",443,$errno,$errstr,$timeout);
  }
  else
  {
    $fp=fsockopen($host,$port,$errno,$errstr,$timeout);
  }
  if ($fp===False)
  {
    term_echo("Error connecting to \"$host\".");
    return;
  }
  $content="";
  foreach ($params as $key => $value)
  {
    if ($content<>"")
    {
      $content=$content."&";
    }
    $content=$content.$key."=".rawurlencode($value);
  }
  $headers="POST $uri HTTP/1.0\r\n";
  $headers=$headers."Host: $host\r\n";
  $headers=$headers."User-Agent: $agent\r\n";
  $headers=$headers."Content-Type: application/x-www-form-urlencoded\r\n";
  $headers=$headers."Content-Length: ".strlen($content)."\r\n";
  if ($extra_headers<>"")
  {
    foreach ($extra_headers as $key => $value)
    {
      $headers=$headers.$key.": ".$value."\r\n";
    }
  }
  $headers=$headers."Connection: Close\r\n\r\n";
  $request=$headers.$content;
  fwrite($fp,$request);
  $response="";
  while (!feof($fp))
  {
    $response=$response.fgets($fp,1024);
  }
  fclose($fp);
  return $response;
}

#####################################################################################################

function strip_headers($response)
{
  $delim="\r\n\r\n";
  $i=strpos($response,$delim);
  if ($i===False)
  {
    return False;
  }
  return substr($response,$i+strlen($delim));
}

#####################################################################################################

function extract_raw_tag($html,$tag)
{
  $delim1="<$tag";
  $delim2=">";
  $delim3="</$tag>";
  $i=strpos(strtolower($html),strtolower($delim1));
  if ($i===False)
  {
    return False;
  }
  $html=substr($html,$i+strlen($delim1));
  $i=strpos($html,$delim2);
  if ($i===False)
  {
    return False;
  }
  $html=substr($html,$i+strlen($delim2));
  $i=strpos(strtolower($html),strtolower($delim3));
  if ($i===False)
  {
    return False;
  }
  return substr($html,0,$i);
}

#####################################################################################################

function extract_void_tag($html,$tag)
{
  $delim1="<$tag";
  $delim2=">";
  $html=extract_text($html,$delim1,$delim2);
  if ($html===False)
  {
    return False;
  }
  if (substr($html,strlen($html)-1,1)=="/")
  {
    $html=substr($html,0,strlen($html)-1);
  }
  return trim($html);
}

#####################################################################################################

function extract_text($text,$delim1,$delim2,$delim2opt=False)
{
  $i=strpos(strtolower($text),strtolower($delim1));
  if ($i===False)
  {
    return False;
  }
  $text=substr($text,$i+strlen($delim1));
  $i=strpos($text,$delim2);
  if ($i===False)
  {
    if ($delim2opt==True)
    {
      return trim($text);
    }
    else
    {
      return False;
    }
  }
  $text=substr($text,0,$i);
  return trim($text);
}

#####################################################################################################

function replace_first($search,$replace,$subject)
{
  $lsubject=strtolower($subject);
  $lsearch=strtolower($search);
  $n=count($search);
  $i=strpos($lsubject,$lsearch);
  if ($i===False)
  {
    return False;
  }
  $s1=substr($subject,0,$i);
  $s2=substr($subject,$i+strlen($search));
  return $s1.$replace.$s2;
}

#####################################################################################################

function strip_first_tag(&$html,$tag)
{
  $lhtml=strtolower($html);
  $i=strpos($lhtml,"<$tag");
  $end="</$tag>";
  $j=strpos($lhtml,$end);
  if (($i===False) or ($j===False))
  {
    return False;
  }
  $html=substr($html,0,$i).substr($html,$j+strlen($end));
  return True;
}

#####################################################################################################

function strip_comments(&$html)
{
  $i=strpos($html,"<!--");
  $end="-->";
  $j=strpos($html,$end);
  if (($i===False) or ($j===False))
  {
    return False;
  }
  $html=substr($html,0,$i).substr($html,$j+strlen($end));
  strip_comments($html);
  return True;
}

#####################################################################################################

function strip_all_tag(&$html,$tag)
{
  while (strip_first_tag($html,$tag)==True)
  {
  }
}

#####################################################################################################

function is_valid_chars($value,$valid_chars)
{
  for ($i=0;$i<strlen($value);$i++)
  {
    if (strpos($valid_chars,$value[$i])===False)
    {
      return False;
    }
  }
  return True;
}

#####################################################################################################

function filter($value,$valid_chars)
{
  $result="";
  for ($i=0;$i<strlen($value);$i++)
  {
    if (strpos($valid_chars,$value[$i])!==False)
    {
      $result=$result.$value[$i];
    }
  }
  return $result;
}

#####################################################################################################

function filter_non_alpha_num($value)
{
  return filter($value,VALID_UPPERCASE.VALID_LOWERCASE.VALID_NUMERIC);
}

#####################################################################################################

function exec_get_headers($response)
{
  $delim="\r\n\r\n";
  $i=strpos($response,$delim);
  if ($i===False)
  {
    return False;
  }
  return substr($response,0,$i);
}

#####################################################################################################

function exec_get_header($response,$header,$extract_headers=True)
{
  if ($extract_headers==True)
  {
    $headers=exec_get_headers($response);
  }
  else
  {
    $headers=$response;
  }
  $lines=explode("\n",$headers);
  for ($i=0;$i<count($lines);$i++)
  {
    $line=trim($lines[$i]);
    $parts=explode(":",$line);
    if (count($parts)>=2)
    {
      $key=trim($parts[0]);
      array_shift($parts);
      $value=trim(implode(":",$parts));
      if (strtolower($key)==strtolower($header))
      {
        return $value;
      }
    }
  }
  return "";
}

#####################################################################################################

function exec_get_cookies($response)
{
  $header="Set-Cookie";
  $values=array();
  $lines=explode("\n",exec_get_headers($response));
  for ($i=0;$i<count($lines);$i++)
  {
    $line=trim($lines[$i]);
    $parts=explode(":",$line);
    if (count($parts)>=2)
    {
      $key=trim($parts[0]);
      array_shift($parts);
      $value=trim(implode(":",$parts));
      if (strtolower($key)==strtolower($header))
      {
        $values[]=$value;
      }
    }
  }
  return $values;
}

#####################################################################################################

function extract_get($url,$name)
{
  $params=array();
  parse_str(parse_url($url,PHP_URL_QUERY),$params);
  if (isset($params[$name])==True)
  {
    return $params[$name];
  }
  else
  {
    return False;
  }
}

#####################################################################################################

?>
