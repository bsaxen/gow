//=============================================
// File.......: lib.php
// Date.......: 2019-01-22
// Author.....: Benny Saxen
// Description: GOW PHP library
//=============================================
//=============================================
function addDomain ($domain)
//=============================================
{
  echo("[$domain]");
  $domain = $domain.'.domain';
  $fh = fopen($domain, 'w') or die("Can't add domain $domain");
}
//=============================================
function restApi($api,$url,$topic)
//=============================================
{
  echo("RestApi [$api] url=$url topic=$topic<br>");
  $call = 'http://'.$url.'/gowServer.php?do='.$api.'&topic='.$topic;
  $res = file_get_contents($call);
}
//=============================================
function getStatus($doc)
//=============================================
{
  global $g_message;
  $url = str_replace(".html", ".json", $doc);
  $json = file_get_contents($url);
  $json = utf8_encode($json);
  $res = json_decode($json, TRUE);
  $period      = $res['gow']['period'];
  $g_message   = $res['gow']['message'];
  $timestamp   = $res['gow']['gs_ts'];
  $now = date_create('now')->format('Y-m-d H:i:s');
  $diff = strtotime($now) - strtotime($timestamp);
  if ($diff > $period) 
  {
    $res = $diff;
  }
  else {
    $res = 0;
  }
  return ($res);
}
//=============================================
function sendMessage($url,$topic,$msg,$tag)
//=============================================
{
  echo "Send message $msg tag=$tag to $url/$topic<br>";
  $call = 'http://'.$url.'/gowServer.php?do=action&topic='.$topic.'&order='.$msg.'&tag='.$tag;
  $res = file_get_contents($call);
  //gowServer.php?do=action&topic=<topic>&order=<order>&tag=<tag>
}
//=============================================
// End of file
//=============================================
