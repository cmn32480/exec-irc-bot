<?php

# gpl2
# by crutchy
# 5-july-2014

# definitions.php

# http://api.urbandictionary.com/v0/define?term=shitton
# thanks weirdpercent

# https://encyclopediadramatica.es

#####################################################################################################

require_once("lib.php");
$trailing=$argv[1];
$alias=$argv[2];
define("DEFINITIONS_FILE","../data/definitions");
define("MAX_DEF_LENGTH",200);

$sources=array(
  "www.wolframalpha.com"=>array(
    "port"=>80,
    "uri"=>"/input/?i=define%3A%%term%%",
    "template"=>"%%term%%",
    "get_param"=>"",
    "delim_start"=>"context.jsonArray.popups.pod_0200.push( {\"stringified\": \"",
    "delim_end"=>"\",\"mInput\": \"\",\"mOutput\": \"\", \"popLinks\": {} });"),
  "www.urbandictionary.com"=>array(
    "port"=>80,
    "uri"=>"/define.php?term=%%term%%",
    "template"=>"%%term%%",
    "get_param"=>"term",
    "delim_start"=>"<div class='meaning'>",
    "delim_end"=>"</div>"),
  "www.stoacademy.com"=>array(
    "port"=>80,
    "uri"=>"/datacore/dictionary.php?searchTerm=%%term%%",
    "template"=>"%%term%%",
    "get_param"=>"",
    "delim_start"=>"<b><u>",
    "delim_end"=>"<p>"));

$terms=unserialize(file_get_contents(DEFINITIONS_FILE));
if ($alias=="~define-count")
{
  privmsg("custom definition count: ".count($terms));
  return;
}
if ($alias=="~define-sources")
{
  privmsg("definition sources in order of preference: www.urbandictionary.com > www.wolframalpha.com > www.stoacademy.com");
  return;
}
if ($alias=="~define-source")
{
  # add source using syntax: ~define-source $host|$port|$uri|$delim_start|$delim_end|$template|$get_param
  return;
}
if ($alias=="~define-add")
{
  $parts=explode(",",$trailing);
  if (count($parts)>1)
  {
    $term=trim($parts[0]);
    array_shift($parts);
    $def=trim(implode(",",$parts));
    $terms[$term]=$def;
    if (file_put_contents(DEFINITIONS_FILE,serialize($terms))===False)
    {
      privmsg("error writing definitions file");
    }
    else
    {
      privmsg("definition for term \"$term\" set to \"$def\"");
    }
  }
  else
  {
    privmsg("syntax: ~define-add <term> <definition>");
  }
  return;
}
foreach ($terms as $term => $def)
{
  $lterms[strtolower($term)]=$term;
}
if (isset($lterms[strtolower($trailing)])==True)
{
  $def=$terms[$lterms[strtolower($trailing)]];
  privmsg("[soylent] $trailing: $def");
}
else
{
  foreach ($sources as $host => $params)
  {
    ,,,$params["get_param"])==True)
    if (source_define($host,$trailing,$params)==True)
    {
      return;
    }
  }
  privmsg("$trailing: unable to find definition");
}

#####################################################################################################

function source_define($host,$term,$params)
{
  $uri=str_replace($params["template"],urlencode($term),$params["uri"]);
  $response=wget($host,$uri,$params["port"]);
  $html=strip_headers($response);
  $i=strpos($html,$params["delim_start"]);
  $def="";
  if ($i!==False)
  {
    $html=substr($html,$i+strlen($params["delim_start"]));
    $i=strpos($html,$params["delim_end"]);
    if ($i!==False)
    {
      $def=trim(strip_tags(substr($html,0,$i)));
      $def=str_replace(array("\n","\r")," ",$def);
      $def=str_replace("  "," ",$def);
      if (strlen($def)>MAX_DEF_LENGTH)
      {
        $def=substr($def,0,MAX_DEF_LENGTH)."...";
      }
    }
  }
  if ($def=="")
  {
    $location=exec_get_header($response,"location");
    if ($location=="")
    {
      return False;
    }
    else
    {
      $new_term=extract_get($location,$get_param);
      if ($new_term<>$term)
      {
        return source_define($host,$new_term,$params);
      }
      else
      {
        return False;
      }
    }
  }
  else
  {
    privmsg("[$host] ".chr(3)."3$term".chr(3).": ".html_entity_decode($def,ENT_QUOTES,"UTF-8"));
    return True;
  }
}

#####################################################################################################

?>
